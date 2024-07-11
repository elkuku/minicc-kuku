<?php

namespace App\Service;

use App\Entity\Contract;
use App\Helper\IntlConverter;
use App\Type\Gender;
use IntlNumbersToWords\Numbers;

class ContractTemplateHelper
{
    /**
     * @return array<string>
     */
    public function getReplacementStrings(): array
    {
        $replacements = $this->getReplacements((new Contract())->setGender(Gender::other));
        return array_keys($replacements);
    }

    public function replaceContent(Contract $contract): string
    {
        $replacements = $this->getReplacements($contract);
        $start = '[';
        $end = ']';
        $search = array_map(fn($item): string => $start . $item . $end, array_keys($replacements));
        return str_replace(
            $search,
            $replacements,
            $contract->getText()
        );
    }

    /**
     * @return array<string, string|int|null>
     */
    private function getReplacements(Contract $contract): array
    {
        $numberToWord = new Numbers();
        return [
            'local_no' => $contract->getStoreNumber(),
            'destination' => $contract->getDestination(),
            'val_alq' => number_format((float)$contract->getValAlq(), 2),
            'txt_alq' => $numberToWord->toCurrency(
                (float)$contract->getValAlq(),
                'es_EC',
                'USD'
            ),
            'val_garantia' => number_format(
                (float)$contract->getValGarantia(),
                2
            ),
            'txt_garantia' => $numberToWord->toCurrency(
                (float)$contract->getValGarantia(),
                'es_EC',
                'USD'
            ),
            'fecha_long' => IntlConverter::formatDate($contract->getDate()),

            'inq_nombreapellido' => $contract->getInqNombreapellido(),
            'inq_ci' => $contract->getInqCi(),

            'senor_a' => $contract->getGender()->titleLong(),
            'el_la' => $contract->getGender()->text_1(),
            'del_la' => $contract->getGender()->text_2(),

            'cnt_lanfort' => $contract->getCntLanfort(),
            'cnt_neon' => $contract->getCntNeon(),
            'cnt_switch' => $contract->getCntSwitch(),
            'cnt_toma' => $contract->getCntToma(),
            'cnt_ventana' => $contract->getCntVentana(),
            'cnt_llaves' => $contract->getCntLlaves(),
            'cnt_med_agua' => $contract->getCntMedAgua(),
            'cnt_med_elec' => $contract->getCntMedElec(),

            'med_electrico' => $contract->getMedElectrico(),
            'med_agua' => $contract->getMedAgua(),
        ];
    }
}
