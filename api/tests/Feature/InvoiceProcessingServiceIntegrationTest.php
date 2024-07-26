<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Services\InvoiceProcessingService;
use App\Services\QueueService;
use Mockery;
use Tests\TestCase;

class InvoiceProcessingServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->invoiceProcessingService = new InvoiceProcessingService($this->queueServiceMock);

        $this->billing = Billing::create([
            'id' => 1,
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ]);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_process_invoice_and_generate_queue_message()
    {
        $message = json_encode(['uuid' => 'test-uuid', 'debit_id' => 1]);

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) {
                return $messageData['uuid'] === 'test-uuid' && $messageData['debit_id'] === 1;
            }), InvoiceProcessingService::QUEUE_NAME);

        $this->invoiceProcessingService->processMessage($message);

        $this->assertDatabaseHas('billings', [
            'id' => 1,
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ]);
    }
}
