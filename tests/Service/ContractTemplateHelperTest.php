<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Contract;
use App\Service\ContractTemplateHelper;
use App\Type\Gender;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContractTemplateHelperTest extends TestCase
{
    private ContractTemplateHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new ContractTemplateHelper();
    }

    public function testGetReplacementStringsReturnsExpectedKeys(): void
    {
        $keys = $this->helper->getReplacementStrings();

        self::assertContains('local_no', $keys);
        self::assertContains('destination', $keys);
        self::assertContains('val_alq', $keys);
        self::assertContains('txt_alq', $keys);
        self::assertContains('val_garantia', $keys);
        self::assertContains('txt_garantia', $keys);
        self::assertContains('fecha_long', $keys);
        self::assertContains('inq_nombreapellido', $keys);
        self::assertContains('inq_ci', $keys);
        self::assertContains('senor_a', $keys);
        self::assertContains('el_la', $keys);
        self::assertContains('del_la', $keys);
        self::assertContains('cnt_lanfort', $keys);
        self::assertContains('cnt_neon', $keys);
        self::assertContains('cnt_switch', $keys);
        self::assertContains('cnt_toma', $keys);
        self::assertContains('cnt_ventana', $keys);
        self::assertContains('cnt_llaves', $keys);
        self::assertContains('cnt_med_agua', $keys);
        self::assertContains('cnt_med_elec', $keys);
        self::assertContains('med_electrico', $keys);
        self::assertContains('med_agua', $keys);
    }

    public function testGetReplacementStringsCount(): void
    {
        $keys = $this->helper->getReplacementStrings();

        self::assertCount(22, $keys);
    }

    public function testReplaceContentWithSimplePlaceholder(): void
    {
        $contract = $this->createContract();
        $contract->setText('Local número [local_no]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Local número 42', $result);
    }

    public function testReplaceContentWithMultiplePlaceholders(): void
    {
        $contract = $this->createContract();
        $contract->setText('Local [local_no] - Destino: [destination]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Local 42 - Destino: Centro Comercial', $result);
    }

    public function testReplaceContentFormatsValAlqWithTwoDecimals(): void
    {
        $contract = $this->createContract();
        $contract->setText('Alquiler: [val_alq]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Alquiler: 150.50', $result);
    }

    public function testReplaceContentFormatsValGarantiaWithTwoDecimals(): void
    {
        $contract = $this->createContract();
        $contract->setText('Garantía: [val_garantia]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Garantía: 300.00', $result);
    }

    public function testReplaceContentIncludesCurrencyWords(): void
    {
        $contract = $this->createContract();
        $contract->setText('[txt_alq]');

        $result = $this->helper->replaceContent($contract);

        self::assertNotEmpty($result);
        self::assertStringContainsString('dolar', strtolower($result));
    }

    public function testReplaceContentIncludesGarantiaCurrencyWords(): void
    {
        $contract = $this->createContract();
        $contract->setText('[txt_garantia]');

        $result = $this->helper->replaceContent($contract);

        self::assertNotEmpty($result);
        self::assertStringContainsString('dolar', strtolower($result));
    }

    public function testReplaceContentInqFields(): void
    {
        $contract = $this->createContract();
        $contract->setText('[inq_nombreapellido] CI: [inq_ci]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Juan Pérez CI: 1234567890-1', $result);
    }

    #[DataProvider('genderProvider')]
    public function testReplaceContentGenderFields(
        Gender $gender,
        string $expectedSenor,
        string $expectedElLa,
        string $expectedDelLa,
    ): void {
        $contract = $this->createContract();
        $contract->setGender($gender);
        $contract->setText('[senor_a] [el_la] [del_la]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame("$expectedSenor $expectedElLa $expectedDelLa", $result);
    }

    /**
     * @return array<string, array{Gender, string, string, string}>
     */
    public static function genderProvider(): array
    {
        return [
            'male' => [Gender::male, 'señor', 'el', 'del'],
            'female' => [Gender::female, 'señora', 'la', 'de la'],
            'other' => [Gender::other, 'señor@', 'l@', 'de l@'],
        ];
    }

    public function testReplaceContentCounterFields(): void
    {
        $contract = $this->createContract();
        $contract->setText(
            '[cnt_lanfort] [cnt_neon] [cnt_switch] [cnt_toma] '
            . '[cnt_ventana] [cnt_llaves] [cnt_med_agua] [cnt_med_elec]'
        );

        $result = $this->helper->replaceContent($contract);

        self::assertSame('3 5 2 4 6 1 7 8', $result);
    }

    public function testReplaceContentMeterFields(): void
    {
        $contract = $this->createContract();
        $contract->setText('Eléctrico: [med_electrico] Agua: [med_agua]');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Eléctrico: MED-ELEC-001 Agua: MED-AGUA-001', $result);
    }

    public function testReplaceContentWithNoPlaceholders(): void
    {
        $contract = $this->createContract();
        $contract->setText('Texto sin marcadores');

        $result = $this->helper->replaceContent($contract);

        self::assertSame('Texto sin marcadores', $result);
    }

    public function testReplaceContentWithDateField(): void
    {
        $contract = $this->createContract();
        $contract->setDate(new DateTime('2024-03-15'));
        $contract->setText('Fecha: [fecha_long]');

        $result = $this->helper->replaceContent($contract);

        self::assertStringContainsString('15', $result);
        self::assertStringContainsString('2024', $result);
    }

    public function testReplaceContentPreservesUnknownBrackets(): void
    {
        $contract = $this->createContract();
        $contract->setText('Known: [local_no] Unknown: [unknown_field]');

        $result = $this->helper->replaceContent($contract);

        self::assertStringContainsString('42', $result);
        self::assertStringContainsString('[unknown_field]', $result);
    }

    private function createContract(): Contract
    {
        return (new Contract())
            ->setStoreNumber(42)
            ->setDestination('Centro Comercial')
            ->setValAlq(150.50)
            ->setValGarantia(300.00)
            ->setGender(Gender::male)
            ->setInqNombreapellido('Juan Pérez')
            ->setInqCi('1234567890-1')
            ->setCntLanfort(3)
            ->setCntNeon(5)
            ->setCntSwitch(2)
            ->setCntToma(4)
            ->setCntVentana(6)
            ->setCntLlaves(1)
            ->setCntMedAgua(7)
            ->setCntMedElec(8)
            ->setMedElectrico('MED-ELEC-001')
            ->setMedAgua('MED-AGUA-001');
    }
}