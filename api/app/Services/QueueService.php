<?php

namespace App\Services;

use App\External\QueueConnection;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueService
{
    protected AMQPStreamConnection $connection;
    protected AMQPChannel $channel;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = (new QueueConnection)->get();

        $this->channel = $this->connection->channel();
        $this->channel->queue_declare(
            config('rabbitmq.queue'), false, true, false, false
        );
    }

    public function publishMessage(array $messageData, string $queueName = ''): void
    {
        $message = json_encode($messageData);

        $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        if ($queueName == '') {
            $queueName = config('rabbitmq.queue');
        }

        $this->channel->basic_publish($msg, '', $queueName);
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
