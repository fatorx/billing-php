<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\QueueService;
use App\External\QueueConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Mockery;

class QueueServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueConnectionMock = Mockery::mock(QueueConnection::class);
        $this->amqpStreamConnectionMock = Mockery::mock(AMQPStreamConnection::class);
        $this->amqpChannelMock = Mockery::mock(AMQPChannel::class);

        $this->queueConnectionMock->shouldReceive('get')
            ->andReturn($this->amqpStreamConnectionMock);

        $this->amqpStreamConnectionMock->shouldReceive('channel')
            ->andReturn($this->amqpChannelMock);

        $this->amqpChannelMock->shouldReceive('queue_declare')
            ->with(config('rabbitmq.queue'), false, true, false, false);

        $this->app->instance(QueueConnection::class, $this->queueConnectionMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_publish_a_message_to_the_queue()
    {
        $queueService = new QueueService();

        $messageData = ['key' => 'value'];
        $message = json_encode($messageData);
        $amqpMessage = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        $this->amqpChannelMock->shouldReceive('basic_publish')
            ->once()
            ->with($amqpMessage, '', config('rabbitmq.queue'));

        $queueService->publishMessage($messageData);
    }
}
