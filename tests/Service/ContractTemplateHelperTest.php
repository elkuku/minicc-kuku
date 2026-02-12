<?php

declare(strict_types=1);

namespace App\Tests\Service;

use Iterator;
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

        $this->assertContains('local_no', $keys);
        $this->assertContains('destination', $keys);
        $this->assertContains('val_alq', $keys);
        $this->assertContains('txt_alq', $keys);
        $this->assertContains('val_garantia', $keys);
        $this->assertContains('txt_garantia', $keys);
        $this->assertContains('fecha_long', $keys);
        $this->assertContains('inq_nombreapellido', $keys);
        $this->assertContains('inq_ci', $keys);
        $this->assertContains('senor_a', $keys);
        $this->assertContains('el_la', $keys);
        $this->assertContains('del_la', $keys);
        $this->assertContains('cnt_lanfort', $keys);
        $this->assertContains('cnt_neon', $keys);
        $this->assertContains('cnt_switch', $keys);
        $this->assertContains('cnt_toma', $keys);
        $this->assertContains('cnt_ventana', $keys);
        $this->assertContains('cnt_llaves', $keys);
        $this->assertContains('cnt_med_agua', $keys);
        $this->assertContains('cnt_med_elec', $keys);
        $this->assertContains('med_electrico', $keys);
        $this->assertContains('med_agua', $keys);
    }

    public function testGetReplacementStringsCount(): void
    {
        $keys = $this->helper->getReplacementStrings();

        $this->assertCount(22, $keys);
    }

    public function testReplaceContentWithSimplePlaceholder(): void
    {
        $contract = $this->createContract();
        $contract->setText('Local número [local_no]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Local número 42', $result);
    }

    public function testReplaceContentWithMultiplePlaceholders(): void
    {
        $contract = $this->createContract();
        $contract->setText('Local [local_no] - Destino: [destination]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Local 42 - Destino: Centro Comercial', $result);
    }

    public function testReplaceContentFormatsValAlqWithTwoDecimals(): void
    {
        $contract = $this->createContract();
        $contract->setText('Alquiler: [val_alq]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Alquiler: 150.50', $result);
    }

    public function testReplaceContentFormatsValGarantiaWithTwoDecimals(): void
    {
        $contract = $this->createContract();
        $contract->setText('Garantía: [val_garantia]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Garantía: 300.00', $result);
    }

    public function testReplaceContentIncludesCurrencyWords(): void
    {
        $contract = $this->createContract();
        $contract->setText('[txt_alq]');

        $result = $this->helper->replaceContent($contract);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('dolar', strtolower($result));
    }

    public function testReplaceContentIncludesGarantiaCurrencyWords(): void
    {
        $contract = $this->createContract();
        $contract->setText('[txt_garantia]');

        $result = $this->helper->replaceContent($contract);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('dolar', strtolower($result));
    }

    public function testReplaceContentInqFields(): void
    {
        $contract = $this->createContract();
        $contract->setText('[inq_nombreapellido] CI: [inq_ci]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Juan Pérez CI: 1234567890-1', $result);
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

        $this->assertSame(sprintf('%s %s %s', $expectedSenor, $expectedElLa, $expectedDelLa), $result);
    }

    /**
     * @return Iterator<string, array{Gender, string, string, string}>
     */
    public static function genderProvider(): Iterator
    {
        yield 'male' => [Gender::male, 'señor', 'el', 'del'];
        yield 'female' => [Gender::female, 'señora', 'la', 'de la'];
        yield 'other' => [Gender::other, 'señor@', 'l@', 'de l@'];
    }

    public function testReplaceContentCounterFields(): void
    {
        $contract = $this->createContract();
        $contract->setText(
            '[cnt_lanfort] [cnt_neon] [cnt_switch] [cnt_toma] '
            . '[cnt_ventana] [cnt_llaves] [cnt_med_agua] [cnt_med_elec]'
        );

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('3 5 2 4 6 1 7 8', $result);
    }

    public function testReplaceContentMeterFields(): void
    {
        $contract = $this->createContract();
        $contract->setText('Eléctrico: [med_electrico] Agua: [med_agua]');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Eléctrico: MED-ELEC-001 Agua: MED-AGUA-001', $result);
    }

    public function testReplaceContentWithNoPlaceholders(): void
    {
        $contract = $this->createContract();
        $contract->setText('Texto sin marcadores');

        $result = $this->helper->replaceContent($contract);

        $this->assertSame('Texto sin marcadores', $result);
    }

    public function testReplaceContentWithDateField(): void
    {
        $contract = $this->createContract();
        $contract->setDate(new DateTime('2024-03-15'));
        $contract->setText('Fecha: [fecha_long]');

        $result = $this->helper->replaceContent($contract);

        $this->assertStringContainsString('15', $result);
        $this->assertStringContainsString('2024', $result);
    }

    public function testReplaceContentPreservesUnknownBrackets(): void
    {
        $contract = $this->createContract();
        $contract->setText('Known: [local_no] Unknown: [unknown_field]');

        $result = $this->helper->replaceContent($contract);

        $this->assertStringContainsString('42', $result);
        $this->assertStringContainsString('[unknown_field]', $result);
    }

    private function createContract(): Contract
    {
        return new Contract()
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