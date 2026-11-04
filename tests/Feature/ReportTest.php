<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CylinderType;
use App\Models\User;

class ReportTest extends OperationsTestCase
{
    public function test_stock_report(): void
    {
        $t = $this->type;
        $other = CylinderType::where('id', '!=', $t->id)->first();
        $as = $this->actingAs($this->admin);

        // 10 full of $t in godown: pending 4 + 3 (3 delivered later), cancelled 5 doesn't reserve; $other overbooked by 2
        $as->post('/booking', $this->payload([[$t->brand_id, $t->id, 4]]))->assertSessionHasNoErrors();
        $as->post('/booking', $this->payload([[$t->brand_id, $t->id, 3]]))->assertSessionHasNoErrors();
        $as->post('/booking', $this->payload([[$t->brand_id, $t->id, 5]], ['status' => 'cancelled']))->assertSessionHasNoErrors();
        $as->post('/booking', $this->payload([[$other->brand_id, $other->id, 2]], ['remarks' => 'ok to wait']))->assertSessionHasNoErrors();
        $delivered = Booking::latest('id')->skip(2)->first();
        $as->post("/booking/{$delivered->id}/delivered")->assertSessionHas('success');

        $res = $as->get('/stock-report')->assertOk()
            ->assertSee('Full in godown')->assertSee('Available to book')->assertDontSee('Empty in godown')->assertDontSee('Damaged')
            ->assertSee('short 2');
        $rows = $res->viewData('rows')->keyBy(fn ($row) => "{$row->brand} {$row->weight}");
        $mine = $rows["{$t->cylinderBrand->name} {$t->weight}"];
        $this->assertSame([7, 4, 3], [$mine->full, $mine->reserved, $mine->available]);
        $short = $rows["{$other->cylinderBrand->name} {$other->weight}"];
        $this->assertSame([0, 2, -2], [$short->full, $short->reserved, $short->available]);

        // Sub owner has it pre-assigned, staff doesn't
        $this->actingAs(User::where('email', 'subowner@example.com')->first())->get('/stock-report')->assertOk();
        $this->actingAs(User::where('email', 'staff@example.com')->first())->get('/stock-report')->assertForbidden();
    }

    public function test_dashboard_numbers(): void
    {
        $t = $this->type;
        $as = $this->actingAs($this->admin);
        $as->post('/booking', $this->payload([[$t->brand_id, $t->id, 4]], ['booking_date' => today()->toDateString()]));
        $as->post('/booking', $this->payload([[$t->brand_id, $t->id, 9]], ['booking_date' => today()->toDateString(), 'remarks' => 'ok to wait']));
        $as->post('/booking/'.Booking::first()->id.'/delivered');
        $as->post('/purchase', ['purchase_date' => today()->toDateString()] + $this->purchasePayload([[$t->brand_id, $t->id, 5]]))->assertSessionHasNoErrors();

        // 10 + 5 purchased - 4 delivered = 11 in godown, so the 9 booking is covered again
        $res = $as->get('/home')->assertOk()->assertSee("Today's bookings")->assertSee('Open bookings')->assertSee('Low stock')
            ->assertDontSee('Trucks out')->assertDontSee('Trucks in progress')->assertDontSee('Damaged in godown')->assertDontSee('Empties collected');
        $this->assertSame([2, 1, 0], [$res->viewData('bookings')->today, $res->viewData('bookings')->open, $res->viewData('bookings')->short]);
        // Last 7 days, today: 2 bookings, 5 purchased, 4 delivered
        $today = $res->viewData('lastDays')[0];
        $this->assertSame([2, 5, 4], [$today->bookings, $today->purchased, $today->delivered]);
    }
}
