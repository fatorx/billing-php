<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Billing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_billing()
    {
        $billingData = [
            'id' => '1',
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ];

        $billing = Billing::create($billingData);

        $this->assertDatabaseHas('billings', $billingData);
        $this->assertInstanceOf(Billing::class, $billing);
        $this->assertEquals($billingData['id'], $billing->id);
        $this->assertEquals($billingData['government_id'], $billing->government_id);
        $this->assertEquals($billingData['email'], $billing->email);
        $this->assertEquals($billingData['name'], $billing->name);
        $this->assertEquals($billingData['amount'], $billing->amount);
        $this->assertEquals($billingData['due_date'], $billing->due_date);
        $this->assertEquals($billingData['status'], $billing->status);
    }

    /** @test */
    public function it_can_find_a_billing_by_id()
    {
        $billingData = [
            'id' => '1',
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ];

        Billing::create($billingData);

        $billing = Billing::find('1');

        $this->assertInstanceOf(Billing::class, $billing);
        $this->assertEquals($billingData['id'], $billing->id);
    }

    /** @test */
    public function it_can_update_a_billing()
    {
        $billingData = [
            'id' => '1',
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ];

        $billing = Billing::create($billingData);

        $updatedData = [
            'name' => 'Updated User',
            'amount' => 150.00,
            'status' => 'paid',
        ];

        $billing->update($updatedData);

        $this->assertDatabaseHas('billings', array_merge($billingData, $updatedData));
    }

    /** @test */
    public function it_can_delete_a_billing()
    {
        $billingData = [
            'id' => '1',
            'government_id' => '12345678901',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'amount' => 100.00,
            'due_date' => '2023-03-30',
            'status' => 'pending',
        ];

        $billing = Billing::create($billingData);

        $billing->delete();

        $this->assertDatabaseMissing('billings', $billingData);
    }
}
