<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PaymentMethod;
use App\Repository\DepositRepository;
use App\Repository\PaymentMethodRepository;
use App\Service\DepositImporter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

final class DepositImporterTest extends TestCase
{
    private string $tmpFile = '';

    protected function setUp(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'deposit_csv_');
        self::assertIsString($path);
        $this->tmpFile = $path;
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testImportCountsInsertedDeposits(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha,Numero de documento,Credito',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/01/2024,123456,500.00',
            'TRANSFERENCIA DIRECTA DE Cliente B,15/01/2024,789012,200.00',
        ]);

        $importer = $this->makeImporter(has: false);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(2, $count);
    }

    public function testImportSkipsNonTransferenciaLines(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha,Numero de documento,Credito',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/01/2024,111111,100.00',
            'PAGO SERVICIO Cliente B,15/01/2024,222222,200.00',
            'NOTA DE CREDITO,20/01/2024,333333,300.00',
        ]);

        $importer = $this->makeImporter(has: false);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(1, $count);
    }

    public function testImportSkipsLinesWithMissingFields(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/01/2024',
        ]);

        $importer = $this->makeImporter(has: false);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(0, $count);
    }

    public function testImportSkipsAlreadyExistingDeposits(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha,Numero de documento,Credito',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/01/2024,123456,500.00',
            'TRANSFERENCIA DIRECTA DE Cliente B,15/01/2024,789012,200.00',
        ]);

        $importer = $this->makeImporter(has: true);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(0, $count);
    }

    public function testImportMixesNewAndExistingDeposits(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha,Numero de documento,Credito',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/01/2024,123456,500.00',
            'TRANSFERENCIA DIRECTA DE Cliente B,15/01/2024,789012,200.00',
        ]);

        $paymentMethodRepo = $this->createStub(PaymentMethodRepository::class);
        $paymentMethodRepo->method('find')->willReturn(new PaymentMethod());

        $depositRepo = $this->createStub(DepositRepository::class);
        $depositRepo->method('has')->willReturnOnConsecutiveCalls(false, true);

        $em = $this->createStub(EntityManagerInterface::class);

        $importer = new DepositImporter($paymentMethodRepo, $depositRepo, $em);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(1, $count);
    }

    public function testImportThrowsWhenNoFileProvided(): void
    {
        $importer = $this->makeImporter(has: false);
        $request = new Request();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No CSV file received.');

        $importer->importFromRequest($request);
    }

    public function testImportThrowsWhenPaymentMethodNotFound(): void
    {
        $this->writeCsv(['Descripcion,Fecha,Numero de documento,Credito']);

        $paymentMethodRepo = $this->createStub(PaymentMethodRepository::class);
        $paymentMethodRepo->method('find')->willReturn(null);

        $depositRepo = $this->createStub(DepositRepository::class);
        $em = $this->createStub(EntityManagerInterface::class);

        $importer = new DepositImporter($paymentMethodRepo, $depositRepo, $em);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid entity');

        $importer->importFromRequest($this->makeRequest());
    }

    public function testImportHandlesDateWithSlashes(): void
    {
        $this->writeCsv([
            'Descripcion,Fecha,Numero de documento,Credito',
            'TRANSFERENCIA DIRECTA DE Cliente A,10/06/2024,999999,750.00',
        ]);

        $importer = $this->makeImporter(has: false);

        $count = $importer->importFromRequest($this->makeRequest());

        self::assertSame(1, $count);
    }

    private function makeRequest(): Request
    {
        $uploadedFile = new UploadedFile($this->tmpFile, 'test.csv', 'text/csv', null, true);
        $request = new Request();
        $request->files->set('csv_file', $uploadedFile);

        return $request;
    }

    private function makeImporter(bool $has = false): DepositImporter
    {
        $paymentMethodRepo = $this->createStub(PaymentMethodRepository::class);
        $paymentMethodRepo->method('find')->willReturn(new PaymentMethod());

        $depositRepo = $this->createStub(DepositRepository::class);
        $depositRepo->method('has')->willReturn($has);

        $em = $this->createStub(EntityManagerInterface::class);

        return new DepositImporter($paymentMethodRepo, $depositRepo, $em);
    }

    /**
     * @param array<string> $lines
     */
    private function writeCsv(array $lines): void
    {
        file_put_contents($this->tmpFile, implode("\n", $lines));
    }
}
