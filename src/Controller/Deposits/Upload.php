<?php

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Entity\Deposit;
use App\Helper\CsvParser\CsvObject;
use App\Helper\CsvParser\CsvParser;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits/upload', name: 'deposits_upload', methods: ['GET', 'POST'])]
class Upload extends BaseController
{
    public function __invoke(
        PaymentMethodRepository $paymentMethodRepository,
        DepositRepository       $depositRepository,
        Request                 $request,
        EntityManagerInterface  $entityManager,
    ): RedirectResponse
    {
        $entity = $paymentMethodRepository->find(2);
        if (!$entity) {
            throw new \UnexpectedValueException('Invalid entity');
        }

        $csvData = $this->getCsvDataFromRequest($request);
        $insertCount = 0;
        foreach ($csvData->lines as $line) {
            if (!isset(
                $line->descripcion, $line->fecha,
                $line->{'numero de documento'}, $line->credito
            )
            ) {
                continue;
            }

            if (!str_starts_with(
                (string)$line->descripcion,
                'TRANSFERENCIA DIRECTA DE'
            )
            ) {
                // if ('DEPOSITO' !== $line->descripcion) {
                continue;
            }

            $deposit = (new Deposit())
                ->setEntity($entity)
                ->setDate(new \DateTime(str_replace('/', '-', (string)$line->fecha)))
                ->setDocument($line->{'numero de documento'})
                ->setAmount($line->credito);

            if (false === $depositRepository->has($deposit)) {
                $entityManager->persist($deposit);
                ++$insertCount;
            }
        }
        $entityManager->flush();
        $this->addFlash(
            $insertCount ? 'success' : 'warning',
            'Depositos insertados: '
            . $insertCount
        );

        return $this->redirectToRoute('deposits_index');
    }

    private function getCsvDataFromRequest(Request $request): CsvObject
    {
        $csvFile = $request->files->get('csv_file');
        if (!$csvFile) {
            throw new \RuntimeException('No CSV file received.');
        }

        $path = $csvFile->getRealPath();
        if (!$path) {
            throw new \RuntimeException('Invalid CSV file.');
        }

        $contents = file($path);
        if (!$contents) {
            throw new \RuntimeException('Cannot read CSV file.');
        }

        return (new CsvParser())->parseCSV($contents);
    }
}
