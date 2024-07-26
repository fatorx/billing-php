<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeBase extends Command
{
    protected $signature = 'consumer';

    /**
     * @param Exception $e
     * @param AMQPMessage $msg
     * @param AMQPChannel|AbstractChannel $channel
     * @param string $queueName
     * @return void
     */
    function exceptionHandle(
        Exception $e, AMQPMessage $msg, AMQPChannel|AbstractChannel $channel, string $queueName): void
    {
        $this->error('Error processing message: ' . $e->getMessage());

        $body = json_decode($msg->body, true);
        $body['queue_name'] = $queueName;
        $bodyJson = json_encode($body);

        $error_message = new AMQPMessage($bodyJson, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $channel->basic_publish($error_message, '', 'error_queue');
        $msg->nack(false, false);
    }
}
