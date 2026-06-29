<?php

declare(strict_types=1);

namespace App\Tests\Controller\Contracts;

use App\Controller\Contracts\Generate;
use App\Entity\Contract;
use App\Service\ContractTemplateHelper;
use App\Type\Gender;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class GenerateTest extends TestCase
{
    public function testInvokeReturnsPdfResponse(): void
    {
        $controller = $this->createController();
        $contract = $this->createContract();

        $response = $controller($contract);

        self::assertInstanceOf(PdfResponse::class, $response);
    }

    public function testInvokeResponseHasPdfContentType(): void
    {
        $controller = $this->createController();
        $contract = $this->createContract();

        $response = $controller($contract);

        self::assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function testInvokeResponseContentStartsWithPdfMagicBytes(): void
    {
        $pdf = $this->createStub(Pdf::class);
        $pdf->method('getOutputFromHtml')->willReturn('%PDF-1.4 fake content');

        $controller = $this->createController(pdf: $pdf);
        $contract = $this->createContract();

        $response = $controller($contract);

        self::assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }

    public function testInvokeFilenameContainsStoreNumberAndDate(): void
    {
        $clock = new MockClock('2024-03-15 10:00:00');
        $controller = $this->createController(clock: $clock);
        $contract = $this->createContract(storeNumber: 42);

        $response = $controller($contract);

        self::assertStringContainsString(
            'contrato-local-42-2024-03-15.pdf',
            $response->headers->get('content-disposition') ?? ''
        );
    }

    public function testInvokePassesRenderedHtmlToPdfWithEncoding(): void
    {
        $contractHtml = '<html><body>Contract text</body></html>';

        $templateHelper = $this->createStub(ContractTemplateHelper::class);
        $templateHelper->method('replaceContent')->willReturn($contractHtml);

        $pdf = $this->createMock(Pdf::class);
        $pdf->expects(self::once())
            ->method('getOutputFromHtml')
            ->with(
                self::stringContains($contractHtml),
                ['encoding' => 'utf-8']
            )
            ->willReturn('%PDF-fake');

        $controller = $this->createController(pdf: $pdf, templateHelper: $templateHelper);
        $contract = $this->createContract();

        $controller($contract);
    }

    public function testInvokePrependsTableWidthCss(): void
    {
        $pdf = $this->createMock(Pdf::class);
        $pdf->expects(self::once())
            ->method('getOutputFromHtml')
            ->with(
                self::stringContains('table { width: 100% !important; min-width: unset !important; }'),
                self::anything()
            )
            ->willReturn('%PDF-fake');

        $controller = $this->createController(pdf: $pdf);
        $controller($this->createContract());
    }

    public function testInvokePassesContractToTemplateHelper(): void
    {
        $contract = $this->createContract();

        $templateHelper = $this->createMock(ContractTemplateHelper::class);
        $templateHelper->expects(self::once())
            ->method('replaceContent')
            ->with($contract)
            ->willReturn('<html/>');

        $controller = $this->createController(templateHelper: $templateHelper);

        $controller($contract);
    }

    private function createController(
        ?Pdf $pdf = null,
        ?ContractTemplateHelper $templateHelper = null,
        ?MockClock $clock = null,
    ): Generate {
        return new Generate(
            $pdf ?? $this->createStub(Pdf::class),
            $templateHelper ?? $this->createStub(ContractTemplateHelper::class),
            $clock ?? new MockClock('2024-01-01'),
        );
    }

    private function createContract(int $storeNumber = 1): Contract
    {
        return (new Contract())
            ->setStoreNumber($storeNumber)
            ->setGender(Gender::other)
            ->setInqNombreapellido('Test User')
            ->setValAlq(100.0)
            ->setValGarantia(200.0)
            ->setText('<html><body>[local_no]</body></html>');
    }
}
