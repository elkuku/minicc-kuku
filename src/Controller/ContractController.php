<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Helper\IntlConverter;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use IntlNumbersToWords\Numbers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContractController
 */
class ContractController extends Controller
{
	/**
	 * @Route("contracts", name="contract-list")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function list(StoreRepository $storeRepository, UserRepository $userRepository,
	                     ContractRepository $contractRepository, Request $request): Response
	{
		$storeId = $request->request->getInt('store_id');
		$year    = $request->request->getInt('year');

		return $this->render(
			'contract/list.html.twig',
			[
				'stores'    => $storeRepository->getActive(),
				'users'     => $userRepository->findActiveUsers(),
				'contracts' => $contractRepository->findContracts($storeId, $year),
				'year'      => $year,
				'storeId'   => $storeId,
			]
		);
	}

	/**
	 * @Route("contracts-new", name="contracts-new")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function new(StoreRepository $storeRepository, UserRepository $userRepository,
	                    ContractRepository $contractRepository, Request $request): Response
	{
		$store     = $storeRepository->find($request->request->getInt('store'));
		$user      = $userRepository->find($request->request->getInt('user'));
		$plantilla = $contractRepository->findPlantilla();

		$contract = new Contract;

		$contract->setText($plantilla->getText());

		if ($store)
		{
			$contract->setValuesFromStore($store);
		}

		if ($user)
		{
			$contract
				->setInqNombreapellido($user->getName())
				->setInqCi($user->getInqCi());
		}

		$form = $this->createForm(ContractType::class, $contract);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$contract = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($contract);
			$em->flush();

			$this->addFlash('success', 'El contrato fue guardado.');

			return $this->redirectToRoute('contract-list');
		}

		return $this->render(
			'contract/form.html.twig',
			[
				'form'          => $form->createView(),
				'data'          => $contract,
				'ivaMultiplier' => getenv('value_iva'),
			]
		);
	}

	/**
	 * @Route("contract/{id}", name="contracts-edit")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function edit(ContractRepository $contractRepository, Request $request, int $id): Response
	{
		$data = $contractRepository->find($id);

		$form = $this->createForm(ContractType::class, $data);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$data = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($data);
			$em->flush();

			$this->addFlash('success', 'Contrato has been saved');

			return $this->redirectToRoute('contract-list');
		}

		return $this->render(
			'contract/form.html.twig',
			[
				'form'          => $form->createView(),
				'data'          => $data,
				'ivaMultiplier' => getenv('value_iva'),
			]
		);
	}

	/**
	 * @Route("contracts-template", name="contracts-template")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function template(ContractRepository $contractRepository, Request $request): Response
	{
		$data = $contractRepository->findPlantilla();

		$form = $this->createForm(ContractType::class, $data);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$data = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($data);
			$em->flush();

			$this->addFlash('success', 'Template has been saved');

			return $this->redirectToRoute('contract-list');
		}

		return $this->render(
			'contract/form.html.twig',
			[
				'form'          => $form->createView(),
				'data'          => $data,
				'ivaMultiplier' => getenv('value_iva'),
			]
		);
	}

	/**
	 * @Route("contract-generate/{id}", name="contract-generate")
	 *
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function generate(Contract $contract): Response
	{
		if (!$contract)
		{
			throw $this->createNotFoundException('No contract found');
		}

		$numberToWord = new Numbers;

		$searchReplace = [
			'[local_no]'     => $contract->getStoreNumber(),
			'[destination]'  => $contract->getDestination(),
			'[val_alq]'      => number_format($contract->getValAlq(), 2),
			'[txt_alq]'      => $numberToWord->toCurrency($contract->getValAlq(), 'es_EC', 'USD'),
			'[val_garantia]' => number_format($contract->getValGarantia(), 2),
			'[txt_garantia]' => $numberToWord->toCurrency($contract->getValGarantia(), 'es_EC', 'USD'),
			//      '[fecha_inicio]' => $contract->getDate(),
			'[fecha_long]'   => IntlConverter::formatDate($contract->getDate()),

			'[inq_nombreapellido]' => $contract->getInqNombreapellido(),
			'[inq_ci]'             => $contract->getInqCi(),

			'[el_la]'   => $contract->getGender()->getId() == 1 ? 'el' : 'la',
			'[del_la]'  => $contract->getGender()->getId() == 1 ? 'del' : 'de la',
			'[senor_a]' => $contract->getGender()->getId() == 1 ? 'señor' : 'señora',

			'[cnt_lanfort]'  => $contract->getCntLanfort(),
			'[cnt_neon]'     => $contract->getCntNeon(),
			'[cnt_switch]'   => $contract->getCntSwitch(),
			'[cnt_toma]'     => $contract->getCntToma(),
			'[cnt_ventana]'  => $contract->getCntVentana(),
			'[cnt_llaves]'   => $contract->getCntLlaves(),
			'[cnt_med_agua]' => $contract->getCntMedAgua(),
			'[cnt_med_elec]' => $contract->getCntMedElec(),

			'[med_electrico]' => $contract->getMedElectrico(),
			'[med_agua]'      => $contract->getMedAgua(),
		];

		$html = str_replace(array_keys($searchReplace), $searchReplace, $contract->getText());

		/** @var \Twig_Environment $twig */
		$twig = clone $this->get('twig');

		$template = $twig->createTemplate($html);

		$html = $template->render($searchReplace);

		$filename = sprintf('contrato-local-%d-%s.pdf', $contract->getStoreNumber(), date('Y-m-d'));

		return new Response(
			$this->get('knp_snappy.pdf')
				->getOutputFromHtml($html, ['encoding' => 'utf-8']),
			200,
			[
				'Content-Type'        => 'application/pdf',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}
}
