<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\LineProcessingService;
use App\Services\QueueService;
use App\Models\Billing;
use Mockery;
use Exception;

class LineProcessingServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueServiceMock = Mockery::mock(QueueService::class);
        $this->lineProcessingService = new LineProcessingService($this->queueServiceMock);

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
    public function it_can_process_line_and_generate_queue_message()
    {
        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'John Doe,12345678901,johndoe@example.com,10.00,2023-03-30,2']);

        Billing::where('id', 2)->delete();

        $this->queueServiceMock->shouldReceive('publishMessage')
            ->once()
            ->with(Mockery::on(function ($messageData) {
                return $messageData['uuid'] === 'test-uuid' && $messageData['debit_id'] === 2;
            }), 'process_invoice');

        $this->lineProcessingService->processMessage($message);

        $this->assertDatabaseHas('billings', [
            'id' => 2,
            'government_id' => '12345678901',
            'email' => 'johndoe@example.com',
            'name' => 'John Doe',
            'amount' => 10.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_handles_existing_debit()
    {
        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'Test User,12345678901,test@example.com,100.00,2023-03-30,1']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(LineProcessingService::MESSAGE_EXCEPTION_DEBT_CHECK);

        $this->lineProcessingService->processMessage($message);
    }

    /** @test */
    public function it_handles_zero_amount()
    {
        $message = json_encode(['uuid' => 'test-uuid', 'line' => 'John Doe,12345678901,johndoe@example.com,0.00,2023-03-30,2']);

        Billing::where('id', 2)->delete();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(LineProcessingService::MESSAGE_EXCEPTION_VALUE);

        $this->lineProcessingService->processMessage($message);
    }
}
