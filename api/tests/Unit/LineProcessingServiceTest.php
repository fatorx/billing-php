<?php

namespace Tests\Unit;

use App\Services\InvoiceProcessingService;
use Tests\TestCase;
use App\Services\LineProcessingService;
use App\Services\QueueService;
use App\Models\Billing;
use Mockery;
use Exception;

class LineProcessingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->billingMock = Mockery::mock(Billing::class);
        $this->lineProcessingService = new LineProcessingService($this->queueServiceMock, $this->billingMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_if_uuid_or_line_not_provided_in_message()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('UUID or line not provided in the message.');

        $message = json_encode(['uuid' => 'test-uuid']);

        $this->lineProcessingService->processMessage($message);
    }

    /** @test */
    public function it_throws_exception_if_line_format_is_invalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(LineProcessingService::INVALID_LINE_FORMAT);

        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'invalid,line,format']);

        $this->lineProcessingService->processMessage($message);
    }

    /** @test */
    public function it_throws_exception_if_debit_id_already_registered()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(LineProcessingService::MESSAGE_EXCEPTION_DEBT_CHECK);

        $billing = new \stdClass();
        $billing->id = 1;
        $billing->government_id = '12345678901';
        $billing->name = 'John Doe';
        $billing->email = 'johndoe@example.com';
        $billing->amount = 10.00;
        $billing->due_date = '2024-07-10';

        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'John Doe,12345678901,johndoe@example.com,10.00,2024-07-10,1']);

        $this->billingMock->shouldReceive('find')
            ->with($billing->id)
            ->andReturn($billing);

        $this->lineProcessingService->processMessage($message);
    }

    /** @test */
    public function it_throws_exception_if_amount_is_zero()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(LineProcessingService::MESSAGE_EXCEPTION_VALUE);

        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'John Doe,12345678901,johndoe@example.com,0.00,2024-07-10,1']);

        $this->billingMock->shouldReceive('find')
            ->with(1)
            ->andReturn(null);

        $this->lineProcessingService->processMessage($message);
    }

    /** @test
     * @throws Exception
     */
    public function it_processes_line_and_generates_queue_message()
    {
        $messageData = ['uuid' => 'test-uuid', 'line' => 'John Doe,12345678901,johndoe@example.com,10.00,2024-07-10,10'];
        $message = json_encode($messageData);

        $this->billingMock->shouldReceive('find')
            ->with(10)
            ->andReturn(null);

        $billingData = [
            'id' => 10,
            'government_id' => '12345678901',
            'email' => 'johndoe@example.com',
            'name' => 'John Doe',
            'amount' => '10',
            'due_date' => '2024-07-10',
            'status' => 'pending',
        ];

        $this->billingMock->shouldReceive('create')
            ->with($billingData);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) use ($billingData) {
                return $messageData['uuid'] === 'test-uuid' && $messageData['debit_id'] === $billingData['id'];
            }), LineProcessingService::QUEUE_NAME);
            
        $this->lineProcessingService->processMessage($message);
        $this->assertTrue(true);
    }
}
