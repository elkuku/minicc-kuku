<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Store;
use App\Repository\TransactionRepository;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Snappy\Pdf;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class PdfHelperTest extends TestCase
{
    public function testGetRootReturnsInjectedRootDir(): void
    {
        $helper = $this->createPdfHelper();

        self::assertSame('/project/root', $helper->getRoot());
    }

    public function testGetOutputFromHtmlDelegatesToPdfEngine(): void
    {
        $pdfEngine = $this->createStub(Pdf::class);
        $pdfEngine->method('getOutputFromHtml')
            ->willReturn('pdf-binary-content');

        $helper = $this->createPdfHelper(pdfEngine: $pdfEngine);

        $result = $helper->getOutputFromHtml('<h1>Hello</h1>');

        self::assertSame('pdf-binary-content', $result);
    }

    public function testRenderTransactionHtmlCallsTwigWithCorrectTemplate(): void
    {
        $store = (new Store())->setId(1);
        $transactionRepo = $this->createStub(TransactionRepository::class);
        $transactionRepo->method('findByStoreAndYear')->willReturn([]);
        $transactionRepo->method('getSaldoAnterior')->willReturn(0);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '_pdf/transactions-pdf.html.twig',
                self::callback(static function (array $params) use ($store): bool {
                    return $params['store'] === $store
                        && $params['year'] === 2024
                        && $params['saldoAnterior'] === 0
                        && \is_array($params['transactions']);
                }),
            )
            ->willReturn('<html>transactions</html>');

        $helper = $this->createPdfHelper(twig: $twig);

        $result = $helper->renderTransactionHtml($transactionRepo, $store, 2024);

        self::assertSame('<html>transactions</html>', $result);
    }

    public function testRenderPayrollsHtmlCallsTwigWithCorrectTemplate(): void
    {
        $payrollHelper = $this->createStub(PayrollHelper::class);
        $payrollHelper->method('getData')->willReturn([
            'factDate' => '2024-3-1',
            'prevDate' => '2024-2-01',
            'stores' => [],
            'storeData' => [],
        ]);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '_pdf/payrolls-pdf.html.twig',
                self::callback(static fn(array $params): bool => $params['factDate'] === '2024-3-1'
                    && $params['prevDate'] === '2024-2-01'),
            )
            ->willReturn('<html>payrolls</html>');

        $helper = $this->createPdfHelper(twig: $twig);

        $result = $helper->renderPayrollsHtml(2024, 3, $payrollHelper);

        self::assertSame('<html>payrolls</html>', $result);
    }

    public function testGetHeaderHtmlCallsTwigWithCorrectTemplateAndRootPath(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                '_header-pdf.html.twig',
                ['rootPath' => '/project/root/public'],
            )
            ->willReturn('<header>header</header>');

        $helper = $this->createPdfHelper(twig: $twig);

        $result = $helper->getHeaderHtml();

        self::assertSame('<header>header</header>', $result);
    }

    public function testGetFooterHtmlCallsTwigWithCorrectTemplate(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with('_footer-pdf.html.twig')
            ->willReturn('<footer>footer</footer>');

        $helper = $this->createPdfHelper(twig: $twig);

        $result = $helper->getFooterHtml();

        self::assertSame('<footer>footer</footer>', $result);
    }

    private function createPdfHelper(
        ?Environment $twig = null,
        ?Pdf $pdfEngine = null,
    ): PdfHelper {
        return new PdfHelper(
            '/project/root',
            $twig ?? $this->createStub(Environment::class),
            $pdfEngine ?? $this->createStub(Pdf::class),
        );
    }
}
