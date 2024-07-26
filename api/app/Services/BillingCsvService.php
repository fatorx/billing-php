<?php

namespace App\Services;

use App\Logs\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ZipArchive;

class BillingCsvService
{
    use Log;

    const MESSAGE_FAIL_GET_FILE_NAME = 'Não foi possível armazenar o arquivo nomeado.';
    const QUEUE_NAME = 'process_files';

    private UuidInterface $uuidClass;
    private ZipArchive $zip;
    private string $uuidStorage;
    private string $path;
    private QueueService $queueService;

    /**
     * @param QueueService $queueService
     * @param UuidInterface $uuidClass
     * @param ZipArchive $zip
     */
    public function __construct(QueueService $queueService, UuidInterface $uuidClass, ZipArchive $zip)
    {
        $this->queueService = $queueService;
        $this->uuidClass = $uuidClass;
        $this->zip = $zip;
    }

    /**
     * Process the uploaded CSV file and store the data.
     *
     * @param UploadedFile $file
     * @return string
     * @throws Exception
     */
    public function processCsvFile(UploadedFile $file): string
    {
        $this->uuidStorage = $this->uuidClass->toString();
        $entryName = "{$this->uuidStorage}.csv";

        $this->path = $file->storePubliclyAs('uploads', $entryName);
        $this->zipFile();

        return $this->getUuidStorage();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function zipFile(): void
    {
        $storagePath = Storage::path($this->path);
        $fileZipPath = str_replace('.csv', '.zip', $storagePath);

        $zip = $this->zip;
        if ($zip->open($fileZipPath, ZipArchive::CREATE) !== true) {
            $e = new Exception(self::MESSAGE_FAIL_GET_FILE_NAME);
            $this->addLog($e, 'zip_error_');
            throw $e;
        }

        $entryName = "{$this->uuidStorage}.csv";
        $zip->addFile($storagePath, $entryName);
        $zip->close();

        Storage::delete($this->path);

        $messageData = [
            'uuid' => $this->uuidStorage,
            'path' => $fileZipPath,
        ];

        $this->queueService->publishMessage($messageData, self::QUEUE_NAME);
    }

    public function getUuidStorage(): string
    {
        return $this->uuidStorage;
    }
}
