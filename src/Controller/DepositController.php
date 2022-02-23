<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Helper\CsvParser\CsvParser;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $deposits = $depositRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            ceil(
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
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        $csvFile = $request->files->get('csv_file');
        if (!$csvFile) {
            throw new RuntimeException('No CSV file recieved.');
        }
        $path = $csvFile->getRealPath();
        if (!$path) {
            throw new RuntimeException('Invalid CSV file.');
        }
        $csvData = (new CsvParser)->parseCSV(file($path));
        $entity = $paymentMethodRepository->find(2);
        if (!$entity) {
            throw new UnexpectedValueException('Invalid entity');
        }
        $em = $managerRegistry->getManager();
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
                $em->persist($deposit);
                $insertCount++;
            }
        }
        $em->flush();
        $this->addFlash(
            ($insertCount ? 'success' : 'warning'),
            'Depositos insertados: '
            .$insertCount
        );

        return $this->redirectToRoute('deposits');
    }

    #[Route(path: '/_search', name: '_deposito_search', methods: ['GET'])]
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

    #[Route(path: '/_lookup', name: '_deposito_lookup', methods: ['GET'])]
    public function lookup(
        DepositRepository $depositRepository,
        Request $request
    ): JsonResponse {
        $id = $request->get('id');

        $deposit = $depositRepository->find($id);

        return $this->json($deposit);
    }

    #[Route(path: '/lookup', name: 'lookup-depo', methods: ['POST'])]
    /**
     * @deprecated
     */
    public function lookupOLD(
        DepositRepository $depositRepository,
        Request $request,
    ): JsonResponse {
        $documentId = $request->get('document_id');
        $deposits = $depositRepository->lookup($documentId);
        $response = [
            'error' => '',
            'data'  => '',
        ];
        if (!$deposits) {
            $response['error'] = 'No se encontró ninún depósito con este número!';
        } elseif (count($deposits) > 1) {
            $ids = [];
            /** @type Deposit $d */
            foreach ($deposits as $deposit) {
                $d = $deposit[0];
                $ids[] = $d->getDocument();
            }

            $response['error'] = 'Ambiguous selection. Found: '
                .implode(' ', $ids);
        } elseif ($deposits[0]['tr_id']) {
            $response['error'] = 'Deposito ALREADY ASSIGNED!: '
                .$deposits[0]['tr_id'];
        } else {
            $response['data'] = $deposits[0];
        }

        return new JsonResponse($response);
    }

    #[Route(path: '/delete/{id}', name: 'deposits-delete', methods: ['GET'])]
    public function delete(
        Deposit $deposit,
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        $em = $managerRegistry->getManager();
        $em->remove($deposit);
        $em->flush();
        $this->addFlash('success', 'Deposit method has been deleted');

        return $this->redirectToRoute('deposits');
    }
}
