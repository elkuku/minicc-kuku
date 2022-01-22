<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Helper\IntlConverter;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use IntlNumbersToWords\Numbers;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: 'contracts')]
class ContractController extends AbstractController
{
    #[Route(path: '/', name: 'contract-list', methods: ['GET', 'POST'])]
    public function list(
        StoreRepository $storeRepository,
        UserRepository $userRepository,
        ContractRepository $contractRepository,
        Request $request
    ): Response {
        $storeId = $request->request->getInt('store_id');
        $year = $request->request->getInt('year');

        return $this->render(
            'contract/list.html.twig',
            [
                'stores'    => $storeRepository->getActive(),
                'users'     => $userRepository->findActiveUsers(),
                'contracts' => $contractRepository->findContracts(
                    $storeId,
                    $year
                ),
                'year'      => $year,
                'storeId'   => $storeId,
            ]
        );
    }

    #[Route(path: '/new', name: 'contracts-new', methods: ['POST'])]
    public function new(
        StoreRepository $storeRepo,
        UserRepository $userRepo,
        ContractRepository $contractRepo,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $store = $storeRepo->find($request->request->getInt('store'));
        $user = $userRepo->find($request->request->getInt('user'));
        $plantilla = $contractRepo->findPlantilla();
        $contract = new Contract;
        $contract->setText($plantilla->getText());
        if ($store) {
            $contract->setValuesFromStore($store);
        }
        if ($user) {
            $contract
                ->setInqNombreapellido($user->getName())
                ->setInqCi($user->getInqCi());
        }
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contract = $form->getData();

            $em = $managerRegistry->getManager();
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
                'ivaMultiplier' => $_ENV['value_iva'],
                'title'         => 'Nuevo Contrato',
            ]
        );
    }

    #[Route(path: '/{id}', name: 'contracts-edit', requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'])]
    public function edit(
        Contract $contract,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contract = $form->getData();

            $em = $managerRegistry->getManager();
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
                'ivaMultiplier' => $_ENV['value_iva'],
                'title'         => 'Editar Contrato',
            ]
        );
    }

    #[Route(path: '/delete/{id}', name: 'contracts-delete', methods: ['GET'])]
    public function delete(
        Contract $contract,
        ManagerRegistry $managerRegistry,
    ): RedirectResponse {
        $em = $managerRegistry->getManager();
        $em->remove($contract);
        $em->flush();
        $this->addFlash('success', 'Contract has been deleted');

        return $this->redirectToRoute('contract-list');
    }

    #[Route(path: '/template', name: 'contracts-template', methods: ['GET', 'POST'])]
    public function template(
        ContractRepository $contractRepository,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $data = $contractRepository->findPlantilla();
        $form = $this->createForm(ContractType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $em = $managerRegistry->getManager();
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
                'ivaMultiplier' => $_ENV['value_iva'],
                'title'         => 'Plantilla',
            ]
        );
    }

    #[Route(path: '/generate/{id}', name: 'contract-generate', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function generate(
        Contract $contract,
        Pdf $pdf,
        Environment $environment
    ): PdfResponse {
        $numberToWord = new Numbers;
        $searchReplace = [
            '[local_no]'     => $contract->getStoreNumber(),
            '[destination]'  => $contract->getDestination(),
            '[val_alq]'      => number_format($contract->getValAlq(), 2),
            '[txt_alq]'      => $numberToWord->toCurrency(
                $contract->getValAlq(),
                'es_EC',
                'USD'
            ),
            '[val_garantia]' => number_format($contract->getValGarantia(), 2),
            '[txt_garantia]' => $numberToWord->toCurrency(
                $contract->getValGarantia(),
                'es_EC',
                'USD'
            ),
            '[fecha_long]'   => IntlConverter::formatDate($contract->getDate()),

            '[inq_nombreapellido]' => $contract->getInqNombreapellido(),
            '[inq_ci]'             => $contract->getInqCi(),

            '[el_la]'   => $contract->getGender()->getId() === 1 ? 'el' : 'la',
            '[del_la]'  => $contract->getGender()->getId() === 1 ? 'del'
                : 'de la',
            '[senor_a]' => $contract->getGender()->getId() === 1 ? 'señor'
                : 'señora',

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
        $html = str_replace(
            array_keys($searchReplace),
            $searchReplace,
            $contract->getText()
        );
        $twig = clone $environment;

        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $twig->createTemplate($html)
                    ->render([]),
                ['encoding' => 'utf-8']
            ),
            sprintf(
                'contrato-local-%d-%s.pdf',
                $contract->getStoreNumber(),
                date('Y-m-d')
            )
        );
    }
}
