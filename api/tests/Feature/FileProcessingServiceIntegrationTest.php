<?php

namespace Tests\Feature;

use App\Models\File;
use App\Services\FileProcessingService;
use App\Services\QueueService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use ZipArchive;

class FileProcessingServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->fileProcessingService = new FileProcessingService($this->queueServiceMock);

        Storage::fake('local');
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_process_zip_file_and_generate_queue_messages()
    {
        $uuid = 'test-uuid';
        $message = json_encode(['uuid' => $uuid, 'path' => 'uploads/test-uuid.zip']);

        // Create a fake zip file
        Storage::put('uploads/test-uuid.zip', '');
        $zip = new ZipArchive();
        $zip->open(Storage::path('uploads/test-uuid.zip'), ZipArchive::CREATE);
        $zip->addFromString('test-uuid.csv', "header\nline1\nline2");
        $zip->close();

        File::shouldReceive('create')
            ->once()
            ->andReturn((object)['id' => 1]);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->twice();

        $this->fileProcessingService->processMessage($message);

        $this->assertDatabaseHas('files', [
            'uuid' => $uuid,
            'num_lines' => 2,
            'finish' => 0,
        ]);
    }
}
