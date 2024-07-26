<?php

namespace App\External;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class QueueConnection
{
    /**
     * @throws Exception
     */
    public function get(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );
    }
}
