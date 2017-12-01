<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use App\Helper\CsvParser\CsvParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DepositController
 */
class DepositController extends AbstractController
{
    /**
     * @Route("/deposits", name="deposits")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $paginatorOptions = $this->getPaginatorOptions($request);

        $deposits = $this->getDoctrine()
            ->getRepository(Deposit::class)
            ->getPaginatedList($paginatorOptions);

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
     * @Route("/upload-csv", name="upload-csv")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function uploadCSVAction(Request $request)
    {
        $csvFile = $request->files->get('csv_file');

        $path = $csvFile->getRealPath();

        if (!$path) {
            throw new \RuntimeException('Invalid CSV file.');
        }

        $csvData = (new CsvParser())->parseCSV(file($path));

        $entity = $this->getDoctrine()
            ->getRepository(PaymentMethod::class)
            ->find(2);

        $depoRepo = $this->getDoctrine()
            ->getRepository(Deposit::class);

        $em = $this->getDoctrine()->getManager();

        $insertCount = 0;

        foreach ($csvData->lines as $line) {
            if ('C' != $line->tipo) {
                continue;
            }

            if (false !== strpos($line->concepto, 'INTERES')) {
                continue;
            }

            $deposit = (new Deposit())
                ->setEntity($entity)
                ->setDate(new \DateTime(str_replace('/', '-', $line->fecha)))
                ->setDocument($line->documento)
                ->setAmount($line->monto);

            if (false == $depoRepo->has($deposit)) {
                $em->persist($deposit);
                $insertCount++;

                continue;
            }
        }

        $em->flush();

        $this->addFlash(($insertCount ? 'success' : 'warning'), 'Depositos insertados: '.$insertCount);

        return $this->redirectToRoute('deposits');
    }

    /**
     * @Route("/lookup-depo", name="lookup-depo")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function lookupAction(Request $request)
    {
        $documentId = $request->get('document_id');

        $deposits = $this->getDoctrine()
            ->getRepository(Deposit::class)
            ->lookup($documentId);

        $response = [
            'error' => '',
            'data' => '',
        ];

        if (!$deposits) {
            $response['error'] = 'No se encontró ninún depósito con este número!';
        } else {
            if (count($deposits) > 1) {
                $ids = [];
                /* @type Deposit $d */
                foreach ($deposits as $deposit) {
                    $d = $deposit[0];
                    $ids[] = $d->getDocument();
                }
                $response['error'] = 'Ambiguous selection. Found: '.implode(' ', $ids);
            } else {
                if ($deposits[0]['tr_id']) {
                    $response['error'] = 'Deposito ALREADY ASSIGNED!: '.$deposits[0]['tr_id'];
                } else {
                    $response['data'] = $deposits[0];
                }
            }
        }

        return new JsonResponse($response);
    }
}
