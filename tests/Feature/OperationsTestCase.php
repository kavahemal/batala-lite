<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CylinderType;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class OperationsTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected CylinderType $type;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->type = CylinderType::first();
        $this->customer = Customer::factory()->create();

        // 10 full cylinders of $type in godown
        Purchase::factory()->create()->movements()->delete();
        Purchase::first()->addStock(['brand_id' => $this->type->brand_id, 'cylinder_type_id' => $this->type->id, 'quantity' => 10]);
    }

    protected function payload(array $rows, array $extra = []): array
    {
        return $extra + [
            'status' => 'pending',
            'customer_id' => $this->customer->id,
            'booking_date' => '2026-10-31',
            'scheduled_date' => '',
            'remarks' => '',
            'brand_id' => array_column($rows, 0),
            'cylinder_type_id' => array_column($rows, 1),
            'quantity' => array_column($rows, 2),
        ];
    }

    protected function purchasePayload(array $rows): array
    {
        return [
            'truck_no' => 'GJ03AB1234', 'driver_name' => 'Ramesh Patel', 'purchase_date' => '2026-10-31', 'remarks' => '',
            'brand_id' => array_column($rows, 0), 'cylinder_type_id' => array_column($rows, 1), 'quantity' => array_column($rows, 2),
        ];
    }
}
