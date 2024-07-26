<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InvoiceProcessingService;
use App\Services\QueueService;
use App\Models\Billing;
use Mockery;
use Exception;

class InvoiceProcessingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->billingMock = Mockery::mock(Billing::class);

        $this->invoiceProcessingService = new InvoiceProcessingService($this->queueServiceMock, $this->billingMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_if_debit_id_not_provided_in_message()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(InvoiceProcessingService::DEBIT_ID_NOT_PROVIDED);

        $message = json_encode(['uuid' => 'test-uuid']);

        $this->invoiceProcessingService->processMessage($message);
    }

    /** @test */
    public function it_throws_exception_if_debit_not_found()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(InvoiceProcessingService::DEBIT_NOT_FOUND);

        $dataMessage = ['uuid' => 'test-uuid', 'debit_id' => 1];
        $message = json_encode(['uuid' => 'test-uuid', 'debit_id' => 1]);

        $this->queueServiceMock
            ->shouldReceive('publishMessage')
            ->with([$dataMessage, $this->invoiceProcessingService::QUEUE_NAME]);


        $this->billingMock->shouldReceive('find')
            ->with(1)
            ->andReturn(false);

        $this->invoiceProcessingService->processMessage($message);
        $this->assertTrue(true);
    }

    /** @test */
    public function it_processes_invoice_and_generates_queue_message()
    {
        $billing = new \stdClass();
        $billing->id = 123456;
        $billing->government_id = 321;
        $billing->email = 'fabio@gmail.com';
        $billing->amount = 10.50;
        $billing->due_date = '2024-07-10';

        $message = json_encode(['uuid' => 'test-uuid', 'debit_id' => $billing->id]);

        $this->billingMock->shouldReceive('find')
                    ->with($billing->id)
                    ->andReturn($billing);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) use ($billing) {
                return $messageData['uuid'] === 'test-uuid' && $messageData['debit_id'] === $billing->id;
            }), InvoiceProcessingService::QUEUE_NAME);

        $this->invoiceProcessingService->processMessage($message);
        $this->assertTrue(true);
    }
}
