<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FileProcessingService;
use App\Services\QueueService;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ZipArchive;
use Exception;

class FileProcessingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->zipMock = Mockery::mock(ZipArchive::class);

        $this->fileProcessingService = new FileProcessingService($this->queueServiceMock, $this->zipMock);

        Storage::fake('local');
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_if_file_path_not_provided_in_message()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(FileProcessingService::FILE_PATH_NOT_PROVIDED);

        $message = json_encode(['uuid' => 'test-uuid']);

        $this->fileProcessingService->processMessage($message);
    }

    /** @test */
    public function it_throws_exception_if_failed_to_open_zip_file()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(FileProcessingService::FAILED_TO_OPEN);

        $message = json_encode(['uuid' => 'test-uuid', 'path' => '/invalid/path.zip']);

        $this->zipMock->shouldReceive('open')
            ->andReturn(false);

        $this->fileProcessingService->processMessage($message);
    }

    /** @test */
    public function it_processes_zip_file_and_generates_queue_messages()
    {
        $message = json_encode(['uuid' => 'test-uuid', 'path' => '/tmp/test-uuid.zip']);

        file_put_contents('/tmp/test-uuid.csv', "q,a,s\nq,a,s");

        $this->zipMock->shouldReceive('open')
            ->andReturn(true);
        $this->zipMock->shouldReceive('extractTo')
            ->andReturn(true);
        $this->zipMock->shouldReceive('close')
            ->andReturn(true);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->times(1);

        $this->fileProcessingService->processMessage($message);

        unlink('/tmp/test-uuid.csv');

        $this->assertTrue(true);
    }
}
