<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Helper\CsvParser\CsvObject;
use App\Helper\CsvParser\CsvParser;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;
use function count;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits')]
class DepositController extends AbstractController
{
    use PaginatorTrait;

    #[Route(path: '/', name: 'deposits', methods: ['GET', 'POST'])]
    public function index(
        DepositRepository $depositRepository,
        Request $request,
        int $listLimit
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request, $listLimit);
        $deposits = $depositRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(
                count($deposits) / $paginatorOptions->getLimit()
            )
        );

        return $this->render(
            'deposit/list.html.twig',
            [
                'deposits' => $deposits,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    #[Route(path: '/upload', name: 'upload-csv', methods: ['GET', 'POST'])]
    public function uploadCSV(
        PaymentMethodRepository $paymentMethodRepository,
        DepositRepository $depositRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entity = $paymentMethodRepository->find(2);
        if (!$entity) {
            throw new UnexpectedValueException('Invalid entity');
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
                $line->descripcion,
                'TRANSFERENCIA DIRECTA DE'
            )
            ) {
                // if ('DEPOSITO' !== $line->descripcion) {
                continue;
            }

            $deposit = (new Deposit)
                ->setEntity($entity)
                ->setDate(new DateTime(str_replace('/', '-', $line->fecha)))
                ->setDocument($line->{'numero de documento'})
                ->setAmount($line->credito);

            if (false === $depositRepository->has($deposit)) {
                $entityManager->persist($deposit);
                $insertCount++;
            }
        }
        $entityManager->flush();
        $this->addFlash(
            ($insertCount ? 'success' : 'warning'),
            'Depositos insertados: '
            .$insertCount
        );

        return $this->redirectToRoute('deposits');
    }

    #[Route(path: '/search', name: 'deposito_search', methods: ['GET'])]
    public function _search(
        DepositRepository $depositRepository,
        Request $request
    ): Response {
        $documentId = (int)$request->get('q');
        $ids = $depositRepository->search($documentId);

        return $this->render(
            'deposit/_search_result.html.twig',
            [
                'ids' => $ids,
            ]
        );
    }

    #[Route(path: '/lookup', name: 'deposito_lookup', methods: ['GET'])]
    public function lookup(
        DepositRepository $depositRepository,
        Request $request
    ): JsonResponse {
        $id = $request->get('id');

        $deposit = $depositRepository->find($id);

        return $this->json($deposit);
    }

    #[Route(path: '/delete/{id}', name: 'deposits-delete', methods: ['GET'])]
    public function delete(
        Deposit $deposit,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $entityManager->remove($deposit);
        $entityManager->flush();
        $this->addFlash('success', 'Deposit method has been deleted');

        return $this->redirectToRoute('deposits');
    }

    private function getCsvDataFromRequest(Request $request): CsvObject
    {
        $csvFile = $request->files->get('csv_file');
        if (!$csvFile) {
            throw new RuntimeException('No CSV file recieved.');
        }

        $path = $csvFile->getRealPath();
        if (!$path) {
            throw new RuntimeException('Invalid CSV file.');
        }

        $contents = file($path);
        if (!$contents) {
            throw new RuntimeException('Cannot read CSV file.');
        }

        return (new CsvParser)->parseCSV($contents);
    }
}
