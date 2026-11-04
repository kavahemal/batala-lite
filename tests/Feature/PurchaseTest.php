<?php

namespace Tests\Feature;

use App\Models\CylinderType;
use App\Models\Purchase;

class PurchaseTest extends OperationsTestCase
{
    public function test_purchase_validation_and_save(): void
    {
        $t = $this->type;
        $other = CylinderType::where('brand_id', '!=', $t->brand_id)->first();
        $before = Purchase::count();
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([['', '', '']]))->assertSessionHasErrors('brand_id');
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $t->id, 1], [$t->brand_id, $t->id, 2]]))->assertSessionHasErrors('cylinder_type_id.1');
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $other->id, 1]]))->assertSessionHasErrors('cylinder_type_id.0');
        $this->assertSame($before, Purchase::count());

        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $t->id, 20]]))->assertSessionHasNoErrors();
        $purchase = Purchase::latest('id')->first();
        $this->assertSame(20, (int) $purchase->items()->sum('quantity'));

        // B3: failed edit keeps typed rows
        $this->actingAs($this->admin)->from("/purchase/{$purchase->id}/edit")
            ->put("/purchase/{$purchase->id}", ['truck_no' => 'bad'] + $this->purchasePayload([[$t->brand_id, $t->id, 33]]))->assertSessionHasErrors('truck_no');
        $this->actingAs($this->admin)->get("/purchase/{$purchase->id}/edit")->assertSee('value="33"', false);
    }

    public function test_trucks_and_drivers_learnt_from_purchase_form(): void
    {
        $t = $this->type;
        $as = $this->actingAs($this->admin);
        $purchase = fn (array $extra) => $extra + $this->purchasePayload([[$t->brand_id, $t->id, 5]]);

        $as->post('/purchase', $purchase(['truck_no' => 'gj-03 kt 4455', 'driver_name' => 'Jignesh Vaghela', 'driver_mobile' => '9825012345']))->assertSessionHasNoErrors();
        $truck = \App\Models\Truck::where('truck_no', 'GJ03KT4455')->first();
        $driver = \App\Models\Driver::where('mobile', '9825012345')->first();
        $this->assertNotNull($truck);
        $this->assertSame([$truck->id, $driver->id], [Purchase::latest('id')->first()->truck_id, Purchase::latest('id')->first()->driver_id]);
        $this->assertSame($driver->id, $truck->driver_id);

        // Same mobile, other spelling: same driver, name updated to the newest
        $as->post('/purchase', $purchase(['truck_no' => 'GJ03KT4455', 'driver_name' => 'jignesh  vaghela', 'driver_mobile' => '9825012345']))->assertSessionHasNoErrors();
        $this->assertSame(1, \App\Models\Driver::where('mobile', '9825012345')->count());
        $this->assertSame('jignesh vaghela', $driver->fresh()->name);

        // Same name, other mobile: another driver; the truck remembers the last one
        $as->post('/purchase', $purchase(['truck_no' => 'GJ03KT4455', 'driver_name' => 'Jignesh Vaghela', 'driver_mobile' => '9898012345']))->assertSessionHasNoErrors();
        $this->assertSame(2, \App\Models\Driver::whereRaw('LOWER(name) = ?', ['jignesh vaghela'])->count());
        $this->assertSame('9898012345', $truck->fresh()->driver->mobile);

        // Mobile optional, a driver without mobile is matched by name; a bad mobile is rejected
        $as->post('/purchase', $purchase(['driver_name' => 'Bharat Gohil', 'driver_mobile' => '']))->assertSessionHasNoErrors();
        $as->post('/purchase', $purchase(['driver_name' => 'bharat gohil']))->assertSessionHasNoErrors();
        $this->assertSame(1, \App\Models\Driver::whereNull('mobile')->whereRaw('LOWER(name) = ?', ['bharat gohil'])->count());
        $as->post('/purchase', $purchase(['driver_mobile' => '098250']))->assertSessionHasErrors('driver_mobile');

        // An inactive driver typed again is active again
        $driver->update(['is_active' => false]);
        $as->post('/purchase', $purchase(['driver_name' => 'Jignesh Vaghela', 'driver_mobile' => '9825012345']))->assertSessionHasNoErrors();
        $this->assertTrue($driver->fresh()->is_active);

        // Suggestions on the form ("Name · mobile" keeps same-name drivers apart), truck no shown with spaces
        $as->get('/purchase/create')->assertOk()->assertSee('id="truck-suggestions"', false)->assertSee('value="GJ03KT4455"', false)
            ->assertSee('Jignesh Vaghela · 9898012345')->assertSee('Jignesh Vaghela · 9825012345');
        $as->getJson('/purchase', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSee('9898012345')->assertSee('GJ 03 KT 4455');
    }

    public function test_purchase_pages_render_and_escape_remarks(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $t->id, 10]]))->assertSessionHasNoErrors();
        $purchase = Purchase::latest('id')->first();
        $purchase->update(['remarks' => '<script>x</script>']);

        $this->actingAs($this->admin)->get('/purchase/create')->assertOk();
        $this->actingAs($this->admin)->get("/purchase/{$purchase->id}/edit")->assertOk();
        $this->actingAs($this->admin)->get("/purchase/{$purchase->id}")->assertOk()->assertDontSee('<script>x</script>', false)->assertSee('&lt;script&gt;', false);

        // Deleting a purchase takes its cylinders out of the godown again
        $this->actingAs($this->admin)->deleteJson("/purchase/{$purchase->id}")->assertJson(['status' => true]);
        $this->assertSame(10, app(\App\Services\StockService::class)->godown()[$t->id]);
    }
}
