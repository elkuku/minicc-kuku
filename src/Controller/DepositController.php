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
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/deposits")
 */
class DepositController extends AbstractController
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

		$paginatorOptions->setMaxPages(ceil(\count($deposits) / $paginatorOptions->getLimit()));

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
	                          Request $request
	): RedirectResponse
	{
		$csvFile = $request->files->get('csv_file');

		if (!$csvFile)
		{
			throw new \RuntimeException('No CSV file recieved.');
		}

		$path = $csvFile->getRealPath();

		if (!$path)
		{
			throw new \RuntimeException('Invalid CSV file.');
		}

		$csvData = (new CsvParser)->parseCSV(file($path));

		$entity = $paymentMethodRepository->find(2);

		if (!$entity)
		{
			throw new \UnexpectedValueException('Invalid entity');
		}

		$em = $this->getDoctrine()->getManager();

		$insertCount = 0;

		foreach ($csvData->lines as $line)
		{
			if (!isset($line->descripcion))
			{
				continue;
			}

			if ('DEPOSITO' !== $line->descripcion)
			{
				continue;
			}

//			if (false !== strpos($line->concepto, 'INTERES'))
//			{
//				continue;
//			}

			$deposit = (new Deposit)
				->setEntity($entity)
				->setDate(new \DateTime(str_replace('/', '-', $line->fecha)))
				->setDocument($line->{'numero de documento'})
				->setAmount($line->credito);

			if (false === $depositRepository->has($deposit))
			{
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

		if (!$deposits)
		{
			$response['error'] = 'No se encontró ninún depósito con este número!';
		}
		elseif (\count($deposits) > 1)
		{
			$ids = [];
			/** @type Deposit $d */
			foreach ($deposits as $deposit)
			{
				$d     = $deposit[0];
				$ids[] = $d->getDocument();
			}

			$response['error'] = 'Ambiguous selection. Found: ' . implode(' ', $ids);
		}
		elseif ($deposits[0]['tr_id'])
		{
			$response['error'] = 'Deposito ALREADY ASSIGNED!: ' . $deposits[0]['tr_id'];
		}
		else
		{
			$response['data'] = $deposits[0];
		}

		return new JsonResponse($response);
	}
}
