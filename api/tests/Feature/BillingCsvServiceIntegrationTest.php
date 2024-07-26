<?php

namespace Tests\Feature;

use App\Services\BillingCsvService;
use App\Services\QueueService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class BillingCsvServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->billingCsvService = new BillingCsvService($this->queueServiceMock);

        Storage::fake('local');

        $uuidMock = Mockery::mock('alias:Ramsey\Uuid\Uuid');
        $uuidMock->shouldReceive('uuid4')
            ->andReturn((object)['toString' => 'test-uuid']);
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

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) {
                return $messageData['uuid'] === 'test-uuid' && !empty($messageData['path']);
            }), BillingCsvService::QUEUE_NAME);

        $this->billingCsvService->processCsvFile($file);

        $this->assertEquals('test-uuid', $this->billingCsvService->getUuidStorage());

        $zipFileName = 'test-uuid.zip';
        Storage::disk('local')->assertExists('uploads/' . $zipFileName);

        // Ensure the original CSV file is deleted
        Storage::disk('local')->assertMissing('uploads/test-uuid.csv');
    }

    /** @test */
    public function it_handles_file_storage_and_queue_publishing()
    {
        $file = UploadedFile::fake()->create('billings.csv', 1024);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) {
                return $messageData['uuid'] === 'test-uuid' && !empty($messageData['path']);
            }), BillingCsvService::QUEUE_NAME);

        $this->billingCsvService->processCsvFile($file);

        $this->assertEquals('test-uuid', $this->billingCsvService->getUuidStorage());

        $zipFileName = 'test-uuid.zip';
        Storage::disk('local')->assertExists('uploads/' . $zipFileName);
    }
}
