<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PdfController
 */
class PdfController extends Controller
{
    /**
     * @Route("/planillas", name="planillas")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function planillasAction()
    {
        $year  = date('Y');
        $month = date('m');

        $stores = $this->getDoctrine()
            ->getRepository(Store::class)
            ->findAll();

        $factDate = $year.'-'.$month.'-1';

        if (1 == $month) {
            $prevYear  = $year - 1;
            $prevMonth = 12;
        } else {
            $prevYear  = $year;
            $prevMonth = $month - 1;
        }

        $prevDate = $prevYear.'-'.$prevMonth.'-01';

        $repo = $this->getDoctrine()
            ->getRepository(Transaction::class);

        $storeData = [];

        /* @type Store $store */
        foreach ($stores as $store) {
            $storeData[$store->getId()]['saldoIni']     = $repo->getSaldoALaFecha(
                $store,
                $prevYear.'-'.$prevMonth.'-01'
            );
            $storeData[$store->getId()]['transactions'] = $repo->findMonthPayments(
                $store,
                $prevMonth,
                $prevYear
            );
        }

        $html = $this->renderView(
            'admin/planillas-pdf.html.twig',
            [
                'factDate'  => $factDate,
                'prevDate'  => $prevDate,
                'stores'    => $stores,
                'storeData' => $storeData,
            ]
        );

        $filename = sprintf('planillas-%d-%d.pdf', $year, $month);

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
}
