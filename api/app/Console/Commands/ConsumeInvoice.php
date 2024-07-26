<?php

namespace App\Console\Commands;

use App\External\QueueConnection;
use App\Logs\Log;
use App\Services\InvoiceProcessingService;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;


class ConsumeInvoice extends Command
{
    use Log;

    protected $signature = 'rabbitmq:consume-invoice';
    protected string $queueName = 'process_invoice';
    protected $description = 'Consume Invoice';

    protected InvoiceProcessingService $invoiceProcessingService;

    public function __construct(InvoiceProcessingService $invoiceProcessingService)
    {
        parent::__construct();
        $this->invoiceProcessingService = $invoiceProcessingService;
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

        $callback = function (AMQPMessage $msg) {
            $this->info('Received: ' . $msg->body . "\n");

            try {
                $this->invoiceProcessingService->processMessage($msg->body);
                $msg->ack();
            } catch (Exception $e) {
                $this->error('Error processing message: ' . $e->getMessage());
                $msg->nack(false, true);
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
