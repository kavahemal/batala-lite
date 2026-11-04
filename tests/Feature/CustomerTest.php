<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;

class CustomerTest extends OperationsTestCase
{
    public function test_duplicate_mobile_blocked(): void
    {
        $as = $this->actingAs($this->admin);
        $mobile = $this->customer->mobile_number;

        // Live check: other customers with the number (the one being edited left out); not 10 digits = nothing
        $as->getJson("/customers/check-mobile?mobile={$mobile}")->assertExactJson([['customer_no' => $this->customer->customer_no, 'name' => $this->customer->name]]);
        $as->getJson("/customers/check-mobile?mobile={$mobile}&except={$this->customer->id}")->assertExactJson([]);
        $as->getJson('/customers/check-mobile?mobile=123')->assertExactJson([]);
        $as->get('/customers/create')->assertOk()->assertSee('mobile-duplicate', false);

        // One customer per mobile: blocked, the error names who has it; a deleted customer's mobile is free again
        $as->post('/customers', ['name' => 'Second Entry', 'mobile_number' => $mobile, 'address' => '', 'is_active' => 1])
            ->assertSessionHasErrors(['mobile_number' => "This mobile is already used by {$this->customer->name} ({$this->customer->customer_no})."]);
        $this->assertSame(0, Customer::where('name', 'Second Entry')->count());
        $as->post('/customers', ['name' => 'Unique', 'mobile_number' => '9000000001', 'address' => '', 'is_active' => 1])
            ->assertSessionHas('success', 'Customer added successfully!');
        Customer::where('name', 'Unique')->first()->delete();
        $as->post('/customers', ['name' => 'Reused', 'mobile_number' => '9000000001', 'address' => '', 'is_active' => 1])->assertSessionHasNoErrors();
        // Add with the Active switch unticked (not posted) → saved inactive
        $as->post('/customers', ['name' => 'Inactive New', 'mobile_number' => '9000000002', 'address' => ''])->assertSessionHasNoErrors();
        $this->assertFalse((bool) Customer::where('name', 'Inactive New')->value('is_active'));

        // Staff without customer permissions can't use the check
        $this->actingAs(User::where('email', 'staff@example.com')->first())->getJson("/customers/check-mobile?mobile={$mobile}")->assertForbidden();
    }

    public function test_customer_update_saves_all_fields(): void
    {
        $c = $this->customer;
        $as = $this->actingAs($this->admin);
        $as->put("/customers/{$c->id}", ['name' => 'Renamed Patel', 'mobile_number' => '9111111111', 'address' => 'New address'/* is_active unticked */])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Customer updated successfully!');
        $c->refresh();
        $this->assertSame(['Renamed Patel', '9111111111', 'New address', false], [$c->name, $c->mobile_number, $c->address, (bool) $c->is_active]);

        // Another customer's mobile: blocked; keeping its own mobile is fine
        $other = Customer::factory()->create(['mobile_number' => '9222222222']);
        $as->put("/customers/{$c->id}", ['name' => 'Renamed Patel', 'mobile_number' => '9222222222', 'address' => '', 'is_active' => 1])
            ->assertSessionHasErrors(['mobile_number' => "This mobile is already used by {$other->name} ({$other->customer_no})."]);
        $as->put("/customers/{$c->id}", ['name' => 'Renamed Patel', 'mobile_number' => '9111111111', 'address' => '', 'is_active' => 1])->assertSessionHasNoErrors();
        $this->assertSame('9111111111', $c->fresh()->mobile_number);
        $this->assertTrue((bool) $c->fresh()->is_active);

        // Failed validation keeps the typed values (switch included)
        $as->from("/customers/{$c->id}/edit")->put("/customers/{$c->id}", ['name' => '', 'mobile_number' => '123', 'address' => ''])
            ->assertSessionHasErrors(['name', 'mobile_number']);
        $as->get("/customers/{$c->id}/edit")->assertOk()->assertDontSee('id="is_active" value="1" checked', false);
    }

    public function test_customer_and_type_saves_check_their_own_permissions(): void
    {
        // Staff has no customer / cylinder permissions; give only the ones each save needs
        $staff = User::where('email', 'staff@example.com')->first();
        $staff->givePermissionTo(['customers.index', 'customers.create', 'customers.edit']);
        $as = $this->actingAs($staff);
        $as->post('/customers', ['name' => 'Staff Added', 'mobile_number' => '9000000021', 'address' => '', 'is_active' => 1])->assertSessionHasNoErrors()->assertRedirect('/customers');
        $as->put("/customers/{$this->customer->id}", ['name' => 'Staff Edited', 'mobile_number' => '9000000022', 'address' => ''])->assertSessionHasNoErrors()->assertRedirect('/customers');
        $this->assertSame('Staff Edited', $this->customer->fresh()->name);

        $type = $this->type;
        $data = ['brand_id' => $type->brand_id, 'name' => $type->name, 'weight' => $type->weight] + $type->only(['is_active']);
        $staff->givePermissionTo(['cylinder-types.index', 'cylinder-types.edit']);
        $this->actingAs($staff->fresh())->put("/cylinder-types/{$type->id}", $data)->assertRedirect();
        $this->assertFalse($staff->fresh()->can('cylinder-brands.edit'));
    }

    public function test_customer_form_name_english_only(): void
    {
        $as = $this->actingAs($this->admin);
        $as->post('/customers', ['name' => 'Ramesh/Patel', 'mobile_number' => '9000000011', 'address' => '', 'is_active' => 1])
            ->assertSessionHasErrors(['name' => Customer::NAME_MESSAGE]);
        $as->post('/customers', ['name' => "R.K. D'Souza & Sons-2", 'mobile_number' => '9000000011', 'address' => '', 'is_active' => 1])->assertSessionHasNoErrors();
        $as->put("/customers/{$this->customer->id}", ['name' => 'Ramesh@Patel', 'mobile_number' => '9000000012', 'address' => ''])->assertSessionHasErrors('name');
    }
}
