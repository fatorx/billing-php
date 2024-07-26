<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BillingCsvService;
use App\Services\QueueService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
use Mockery;
use Ramsey\Uuid\Uuid;
use ZipArchive;

class BillingCsvServiceTest extends TestCase
{
    protected Uuid $uuidMock;
    protected ZipArchive $zipMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);

        $this->uuidMock = Mockery::mock('Ramsey\Uuid\Uuid');
        $this->zipMock = Mockery::mock('ZipArchive');
        $this->billingCsvService = new BillingCsvService($this->queueServiceMock, $this->uuidMock, $this->zipMock);

        Storage::fake('local');
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_process_and_zip_csv_file()
    {
        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $this->uuidMock->shouldReceive('toString')
            ->andReturn('1257e258-6b50-484e-bd9e-90e51cb30d0a');

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) {
                return $messageData['uuid'] === '1257e258-6b50-484e-bd9e-90e51cb30d0a' &&
                    str_contains($messageData['path'], '1257e258-6b50-484e-bd9e-90e51cb30d0a.zip');
            }), 'process_files');

        $this->expectationsForZipArchive();

        $this->billingCsvService->processCsvFile($file);

        $this->assertEquals('1257e258-6b50-484e-bd9e-90e51cb30d0a', $this->billingCsvService->getUuidStorage());
    }

    /** @test */
    public function it_throws_exception_if_zip_fails()
    {
        $this->expectException(Exception::class);

        // Create a mock for UploadedFile
        $uploadedFileMock = Mockery::mock(UploadedFile::class);
        $uploadedFileMock->shouldReceive('storePubliclyAs')
            ->once()
            ->andReturn('/invalid/path');

        Storage::shouldReceive('path')
            ->andReturn('/invalid/path');

        // Expectations for ZipArchive failure
        $this->zipMock->shouldReceive('open')
            ->once()
            ->with(Mockery::type('string'), ZipArchive::CREATE)
            ->andReturn(false);

        $this->uuidMock->shouldReceive('toString')
            ->andReturn('1257e258-6b50-484e-bd9e-90e51cb30d0a');

        $this->billingCsvService->processCsvFile($uploadedFileMock);
    }

    /**
     * @return void
     */
    private function expectationsForZipArchive(): void
    {
        $this->zipMock->shouldReceive('open')
            ->once()
            ->with(Mockery::type('string'), ZipArchive::CREATE)
            ->andReturn(true);

        $this->zipMock->shouldReceive('addFile')
            ->once()
            ->with(Mockery::type('string'), '1257e258-6b50-484e-bd9e-90e51cb30d0a.csv')
            ->andReturn(true);

        $this->zipMock->shouldReceive('close')
            ->once()
            ->andReturn(true);
    }
}
