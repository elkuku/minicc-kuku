<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Deposit;
use App\Helper\CsvParser\CsvObject;
use App\Helper\CsvParser\CsvParser;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use UnexpectedValueException;
use Symfony\Component\HttpFoundation\Request;

readonly class DepositImporter
{
    public function __construct(
        private PaymentMethodRepository $paymentMethodRepository,
        private DepositRepository $depositRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function importFromRequest(Request $request): int
    {
        $entity = $this->paymentMethodRepository->find(2);
        if (!$entity) {
            throw new UnexpectedValueException('Invalid entity');
        }

        $csvData = $this->getCsvDataFromRequest($request);
        $insertCount = 0;

        foreach ($csvData->lines as $line) {
            if (!isset(
                $line->descripcion, $line->fecha,
                $line->{'numero de documento'}, $line->credito
            )) {
                continue;
            }

            if (!str_starts_with(
                (string) $line->descripcion,
                'TRANSFERENCIA DIRECTA DE'
            )) {
                continue;
            }

            $deposit = new Deposit();
            $deposit->setEntity($entity)
                ->setDate(new DateTime(str_replace('/', '-', (string) $line->fecha)))
                ->setDocument($line->{'numero de documento'})
                ->setAmount($line->credito);

            if (false === $this->depositRepository->has($deposit)) {
                $this->entityManager->persist($deposit);
                ++$insertCount;
            }
        }

        $this->entityManager->flush();

        return $insertCount;
    }

    private function getCsvDataFromRequest(Request $request): CsvObject
    {
        $csvFile = $request->files->get('csv_file');
        if (!$csvFile) {
            throw new RuntimeException('No CSV file received.');
        }

        $path = $csvFile->getRealPath();
        if (!$path) {
            throw new RuntimeException('Invalid CSV file.');
        }

        $contents = file($path);
        if (!$contents) {
            throw new RuntimeException('Cannot read CSV file.');
        }

        return new CsvParser()->parseCSV($contents);
    }
}
