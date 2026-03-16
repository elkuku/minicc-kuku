<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contract;
use App\Entity\Store;

class ContractFactory
{
    public function createFromStore(Store $store, ?Contract $template = null): Contract
    {
        $contract = new Contract();

        if ($template !== null) {
            $contract->setText($template->getText());
        }

        $contract
            ->setStoreNumber((int) $store->getId())
            ->setDestination($store->getDestination())
            ->setValAlq($store->getValAlq())
            ->setCntLanfort($store->getCntLanfort())
            ->setCntLlaves($store->getCntLlaves())
            ->setCntMedAgua($store->getCntMedAgua())
            ->setCntMedElec($store->getCntMedElec())
            ->setCntNeon($store->getCntNeon())
            ->setCntSwitch($store->getCntSwitch())
            ->setCntToma($store->getCntToma())
            ->setCntVentana($store->getCntVentana())
            ->setMedElectrico($store->getMedElectrico())
            ->setMedAgua($store->getMedAgua());

        return $contract;
    }
}
