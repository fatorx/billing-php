<?php

namespace App\Console\Commands;

use App\External\QueueConnection;
use App\Services\FileProcessingService;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeFile extends ConsumeBase
{
    protected $signature = 'rabbitmq:consume-files';
    protected string $queueName = 'process_files';
    protected $description = 'Consume Files';

    protected FileProcessingService $fileProcessingService;

    public function __construct(FileProcessingService $fileProcessingService)
    {
        parent::__construct();
        $this->fileProcessingService = $fileProcessingService;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $connection = (new QueueConnection)->get();
        $channel = $connection->channel();

        $channel->queue_declare(
            $this->queueName, false, true, false, false
        );

        $channel->queue_declare(
            'error_queue', false, true, false, false
        );

        $callback = function (AMQPMessage $msg) use ($channel) {
            $this->info('Received: ' . $msg->body);

            try {
                $this->fileProcessingService->processMessage($msg->body);
                $msg->ack();
            } catch (Exception $e) {
                $this->exceptionHandle($e, $msg, $channel, $this->queueName);
            }
        };

        $channel->basic_consume(
            $this->queueName, '', false,
            false, false, false, $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
