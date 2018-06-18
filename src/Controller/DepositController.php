<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Deposit;
use App\Helper\CsvParser\CsvParser;
use App\Helper\PaginatorTrait;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/deposits")
 */
class DepositController extends Controller
{
    use PaginatorTrait;

    /**
     * @Route("/", name="deposits")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function index(DepositRepository $depositRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);

        $deposits = $depositRepository->getPaginatedList($paginatorOptions);

        $paginatorOptions->setMaxPages(ceil(count($deposits) / $paginatorOptions->getLimit()));

        return $this->render(
            'deposit/list.html.twig',
            [
                'deposits'         => $deposits,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    /**
     * @Route("/upload", name="upload-csv")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function uploadCSV(PaymentMethodRepository $paymentMethodRepository, DepositRepository $depositRepository,
                              Request $request): RedirectResponse
    {
        $csvFile = $request->files->get('csv_file');

        $path = $csvFile->getRealPath();

        if (!$path) {
            throw new \RuntimeException('Invalid CSV file.');
        }

        $csvData = (new CsvParser)->parseCSV(file($path));

        $entity = $paymentMethodRepository->find(2);

        $em = $this->getDoctrine()->getManager();

        $insertCount = 0;

        foreach ($csvData->lines as $line) {
            if ('C' != $line->tipo) {
                continue;
            }

            if (false !== strpos($line->concepto, 'INTERES')) {
                continue;
            }

            $deposit = (new Deposit)
                ->setEntity($entity)
                ->setDate(new \DateTime(str_replace('/', '-', $line->fecha)))
                ->setDocument($line->documento)
                ->setAmount($line->monto);

            if (false == $depositRepository->has($deposit)) {
                $em->persist($deposit);
                $insertCount++;

                continue;
            }
        }

        $em->flush();

        $this->addFlash(($insertCount ? 'success' : 'warning'), 'Depositos insertados: ' . $insertCount);

        return $this->redirectToRoute('deposits');
    }

    /**
     * @Route("/lookup", name="lookup-depo")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function lookup(DepositRepository $depositRepository, Request $request): JsonResponse
    {
        $documentId = $request->get('document_id');

        $deposits = $depositRepository->lookup($documentId);

        $response = [
            'error' => '',
            'data'  => '',
        ];

        if (!$deposits) {
            $response['error'] = 'No se encontró ninún depósito con este número!';
        } else {
            if (count($deposits) > 1) {
                $ids = [];
                /** @type Deposit $d */
                foreach ($deposits as $deposit) {
                    $d     = $deposit[0];
                    $ids[] = $d->getDocument();
                }

                $response['error'] = 'Ambiguous selection. Found: ' . implode(' ', $ids);
            } else {
                if ($deposits[0]['tr_id']) {
                    $response['error'] = 'Deposito ALREADY ASSIGNED!: ' . $deposits[0]['tr_id'];
                } else {
                    $response['data'] = $deposits[0];
                }
            }
        }

        return new JsonResponse($response);
    }
}
