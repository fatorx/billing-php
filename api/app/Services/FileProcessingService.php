<?php

namespace App\Services;

use App\Logs\Log;
use App\Models\File;
use Exception;
use ZipArchive;

class FileProcessingService
{
    use Log;

    const FILE_PATH_NOT_PROVIDED = 'File path not provided in the message.';
    const FAILED_TO_OPEN = 'Failed to open the ZIP file.';
    const QUEUE_NAME = 'process_line';

    private ZipArchive $zip;
    protected QueueService $queueService;

    public function __construct(QueueService $queueService, $zip)
    {
        $this->queueService = $queueService;
        $this->zip = $zip;
    }

    /**
     * @throws Exception
     */
    public function processMessage(string $message): void
    {
        $data = json_decode($message, true);

        if (isset($data['path'])) {
            $uuid = $data['uuid'];
            $filePath = $data['path'];
            $this->processFile($filePath, $uuid);
        } else {
            $e = new Exception(self::FILE_PATH_NOT_PROVIDED);
            $this->addLog($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function processFile(string $filePath, string $uuid): void
    {
        $zip = $this->zip;
        if ($zip->open($filePath)) {
            $zip->extractTo('/tmp');
            $zip->close();

            $pathTmp = "/tmp/{$uuid}.csv";
            chmod($pathTmp, 0777);

            $fileExtract = file($pathTmp);
            array_shift($fileExtract);

            $numLines = count($fileExtract);
            $this->registerFileInDatabase($uuid, $numLines);

            foreach ($fileExtract as $line) {
                $this->generateQueueMessage($uuid, trim($line));
            }

        } else {
            $e = new Exception(self::FAILED_TO_OPEN);
            $this->addLog($e);
            throw $e;
        }
    }

    protected function registerFileInDatabase(string $uuid, int $numLines): int
    {
        $file = File::create([
            'uuid' => $uuid,
            'process' => 1,
            'num_lines' => $numLines,
            'finish' => 0,
        ]);

        return (int)$file->id;
    }

    protected function generateQueueMessage(string $uuid, string $line): void
    {
        $message = [
            'uuid' => $uuid,
            'line' => $line,
        ];
        $this->queueService->publishMessage($message, self::QUEUE_NAME);
    }
}
