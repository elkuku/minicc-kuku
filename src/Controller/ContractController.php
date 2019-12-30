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
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContractController
 *
 * @Route("contracts")
 */
class ContractController extends AbstractController
{
	/**
	 * @Route("/", name="contract-list")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function list(StoreRepository $storeRepository, UserRepository $userRepository, ContractRepository $contractRepository, Request $request
	): Response
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
	 * @Route("/new", name="contracts-new")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function new(StoreRepository $storeRepo, UserRepository $userRepo, ContractRepository $contractRepo, Request $request
	): Response
	{
		$store     = $storeRepo->find($request->request->getInt('store'));
		$user      = $userRepo->find($request->request->getInt('user'));
		$plantilla = $contractRepo->findPlantilla();

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
				'title'         => 'Nuevo Contrato',
			]
		);
	}

	/**
	 * @Route("/{id}", name="contracts-edit", requirements={"id"="\d+"})
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function edit(Contract $contract, Request $request): Response
	{
		$form = $this->createForm(ContractType::class, $contract);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$contract = $form->getData();

			$em = $this->getDoctrine()->getManager();
			$em->persist($contract);
			$em->flush();

			$this->addFlash('success', 'Contrato has been saved');

			return $this->redirectToRoute('contract-list');
		}

		return $this->render(
			'contract/form.html.twig',
			[
				'form'          => $form->createView(),
				'data'          => $contract,
				'ivaMultiplier' => getenv('value_iva'),
				'title'         => 'Editar Contrato'
			]
		);
	}

	/**
	 * @Route("/delete/{id}", name="contracts-delete")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function delete(Contract $contract): Response
	{
		$em = $this->getDoctrine()->getManager();
		$em->remove($contract);
		$em->flush();

		$this->addFlash('success', 'Contract has been deleted');

		return $this->redirectToRoute('contract-list');
	}

	/**
	 * @Route("/template", name="contracts-template")
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
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
				'title'         => 'Plantilla'
			]
		);
	}

	/**
	 * @Route("/generate/{id}", name="contract-generate", requirements={"id"="\d+"})
	 *
	 * @Security("is_granted('ROLE_ADMIN')")
	 */
	public function generate(Contract $contract, Pdf $pdf): PdfResponse
	{
		$numberToWord = new Numbers;

		$searchReplace = [
			'[local_no]'     => $contract->getStoreNumber(),
			'[destination]'  => $contract->getDestination(),
			'[val_alq]'      => number_format($contract->getValAlq(), 2),
			'[txt_alq]'      => $numberToWord->toCurrency($contract->getValAlq(), 'es_EC', 'USD'),
			'[val_garantia]' => number_format($contract->getValGarantia(), 2),
			'[txt_garantia]' => $numberToWord->toCurrency($contract->getValGarantia(), 'es_EC', 'USD'),
			'[fecha_long]'   => IntlConverter::formatDate($contract->getDate()),

			'[inq_nombreapellido]' => $contract->getInqNombreapellido(),
			'[inq_ci]'             => $contract->getInqCi(),

			'[el_la]'   => $contract->getGender()->getId() === 1 ? 'el' : 'la',
			'[del_la]'  => $contract->getGender()->getId() === 1 ? 'del' : 'de la',
			'[senor_a]' => $contract->getGender()->getId() === 1 ? 'señor' : 'señora',

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

		return new PdfResponse(
			$pdf->getOutputFromHtml($twig->createTemplate($html)->render([]), ['encoding' => 'utf-8']),
			sprintf('contrato-local-%d-%s.pdf', $contract->getStoreNumber(), date('Y-m-d'))
		);
	}
}
