<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\StockMovementType;
use App\Models\Booking;
use App\Models\CylinderType;
use App\Models\Purchase;
use App\Services\BookingService;
use App\Services\StockService;

class BookingTest extends OperationsTestCase
{
    public function test_b1_b2_no_rows_or_blank_rows_show_error(): void
    {
        $this->actingAs($this->admin)->post('/booking', $this->payload([]))->assertSessionHasErrors('brand_id');
        $this->actingAs($this->admin)->post('/booking', $this->payload([['', '', '']]))->assertSessionHasErrors('brand_id');
        $this->assertSame(0, Booking::count());
    }

    public function test_b4_duplicate_rows_rejected(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1], [$t->brand_id, $t->id, 2]]))->assertSessionHasErrors('cylinder_type_id.1');
    }

    public function test_b5_type_of_other_brand_rejected(): void
    {
        $other = CylinderType::where('brand_id', '!=', $this->type->brand_id)->first();
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$this->type->brand_id, $other->id, 1]]))->assertSessionHasErrors('cylinder_type_id.0');
    }

    public function test_b6_scheduled_before_booking_rejected(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1]], ['scheduled_date' => '2026-10-01']))->assertSessionHasErrors('scheduled_date');
    }

    public function test_b8_inactive_customer_rejected(): void
    {
        $t = $this->type;
        $this->customer->update(['is_active' => false]);
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1]]))->assertSessionHasErrors('customer_id');
    }

    public function test_delivered_status_not_selectable_on_form(): void
    {
        // Delivered only via "Mark delivered" (it writes the stock out); unknown statuses rejected too
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1]], ['status' => 'delivered']))->assertSessionHasErrors('status');
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1]], ['status' => 'assigned']))->assertSessionHasErrors('status');
        $this->assertSame(0, Booking::count());
    }

    public function test_within_stock_saves_not_short_and_blank_row_skipped(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([['', '', ''], [$t->brand_id, $t->id, 4]]))->assertSessionHasNoErrors()->assertRedirect('/booking');
        $booking = Booking::first();
        $this->assertFalse($booking->is_short_stock);
        $this->assertSame(4, (int) $booking->items()->sum('quantity'));
    }

    public function test_short_stock_needs_remarks_then_flags(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 12]]))->assertSessionHasErrors('remarks');
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 12]], ['remarks' => 'Customer OK to wait']))->assertSessionHasNoErrors();
        $this->assertTrue(Booking::first()->is_short_stock);
    }

    public function test_edit_does_not_count_own_quantity(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 8]]));
        $booking = Booking::first();
        // 10 in stock, this booking holds 8: raising it to 10 is still within stock
        $this->actingAs($this->admin)->put("/booking/{$booking->id}", $this->payload([[$t->brand_id, $t->id, 10]]))->assertSessionHasNoErrors();
        $this->assertFalse($booking->fresh()->is_short_stock);
    }

    public function test_cancelled_booking_never_short(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 50]], ['status' => 'cancelled']))->assertSessionHasNoErrors();
        $this->assertFalse(Booking::first()->is_short_stock);
    }

    public function test_locked_booking_cannot_be_edited_or_deleted(): void
    {
        $booking = Booking::factory()->status(BookingStatus::DELIVERED)->create();
        $this->actingAs($this->admin)->get("/booking/{$booking->id}/edit")->assertForbidden();
        $this->actingAs($this->admin)->delete("/booking/{$booking->id}")->assertForbidden();
    }

    public function test_mark_delivered_takes_stock_out(): void
    {
        $t = $this->type;
        $stock = app(StockService::class);
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 4]]))->assertSessionHasNoErrors();
        $booking = Booking::first();
        $this->assertSame([10, 4, 6], [$stock->godown()[$t->id], $stock->reserved()[$t->id], $stock->available()[$t->id]]);

        // Button shown on the pending booking's page
        $this->actingAs($this->admin)->get("/booking/{$booking->id}")->assertSee(route('booking.delivered', $booking))->assertSee('Mark delivered');

        $this->actingAs($this->admin)->post("/booking/{$booking->id}/delivered")
            ->assertRedirect("/booking/{$booking->id}")->assertSessionHas('success', "{$booking->booking_no} marked delivered.");
        $booking->refresh();
        $this->assertSame(BookingStatus::DELIVERED, $booking->status);
        // Godown 10 - 4 delivered = 6, nothing reserved any more, so still 6 to book
        $this->assertSame([6, 0, 6], [$stock->godown()[$t->id], $stock->reserved()[$t->id] ?? 0, $stock->available()[$t->id]]);

        // DELIVERY_OUT rows: booking as source, with customer + booking; booking lines unchanged
        $out = $booking->movements()->where('movement_type', StockMovementType::DELIVERY_OUT)->get();
        $this->assertSame([[-4, $this->customer->id, $booking->id]], $out->map(fn ($m) => [$m->quantity, $m->customer_id, $m->booking_id])->all());
        $this->assertSame(4, (int) $booking->items()->sum('quantity'));

        // Delivered: locked, no button, can't be marked again
        $this->actingAs($this->admin)->get("/booking/{$booking->id}")->assertOk()->assertDontSee(route('booking.delivered', $booking));
        $this->actingAs($this->admin)->get("/booking/{$booking->id}/edit")->assertForbidden();
        $this->actingAs($this->admin)->post("/booking/{$booking->id}/delivered")->assertForbidden();
        $this->assertSame(6, $stock->godown()[$t->id]);
    }

    public function test_mark_delivered_only_once_and_only_pending(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 3]]));
        $booking = Booking::first();
        $service = app(BookingService::class);

        // Double click: the second call finds it no longer pending and changes nothing
        $stale = Booking::find($booking->id);
        $this->assertTrue($service->markDelivered($booking));
        $this->assertFalse($service->markDelivered($stale));
        $this->assertSame(1, $booking->movements()->where('movement_type', StockMovementType::DELIVERY_OUT)->count());
        $this->assertSame(7, app(StockService::class)->godown()[$t->id]);

        // Cancelled bookings can't be marked
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 1]], ['status' => 'cancelled']));
        $cancelled = Booking::where('status', BookingStatus::CANCELLED)->first();
        $this->actingAs($this->admin)->post("/booking/{$cancelled->id}/delivered")->assertForbidden();
        $this->assertFalse($service->markDelivered($cancelled));
        $this->assertSame(BookingStatus::CANCELLED, $cancelled->fresh()->status);
    }

    public function test_mark_delivered_needs_booking_edit_permission(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 2]]));
        $booking = Booking::first();
        $staff = \App\Models\User::where('email', 'staff@example.com')->first();
        $staff->givePermissionTo('booking.index');

        $this->actingAs($staff)->get("/booking/{$booking->id}")->assertOk()->assertDontSee('Mark delivered');
        $this->actingAs($staff)->post("/booking/{$booking->id}/delivered")->assertForbidden();
        $this->assertSame(BookingStatus::PENDING, $booking->fresh()->status);
    }

    public function test_mark_delivered_frees_stock_for_short_bookings(): void
    {
        $t = $this->type;
        // 10 in stock: first booking 8 (covered), second 4 (short by 2)
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 8]], ['booking_date' => '2026-10-01']));
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 4]], ['booking_date' => '2026-10-02', 'remarks' => 'ok to wait']));
        [$first, $second] = Booking::orderBy('booking_date')->get();
        $this->assertTrue($second->fresh()->is_short_stock);

        // Delivering the first takes 8 out of the godown and its 8 reserved: second is still short (2 left, needs 4)
        $this->actingAs($this->admin)->post("/booking/{$first->id}/delivered");
        $this->assertTrue($second->fresh()->is_short_stock);
        $this->assertFalse($first->fresh()->is_short_stock);

        // A purchase of 2 covers it
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $t->id, 2]]));
        $this->assertFalse($second->fresh()->is_short_stock);
    }

    public function test_short_stock_flag_follows_stock_first_come_first_served(): void
    {
        $t = $this->type;
        // 10 in stock: first booking 6 (covered), second 6 (short)
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 6]], ['booking_date' => '2026-10-01']));
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 6]], ['booking_date' => '2026-10-02', 'remarks' => 'ok to wait']));
        [$first, $second] = Booking::orderBy('booking_date')->get();
        $this->assertFalse($first->is_short_stock);
        $this->assertTrue($second->is_short_stock);

        // A purchase arrives: second becomes covered
        $this->actingAs($this->admin)->post('/purchase', $this->purchasePayload([[$t->brand_id, $t->id, 5]]));
        $this->assertFalse($second->fresh()->is_short_stock);

        // Purchase deleted again: short again; cancelling the first frees stock for the second
        $purchase = Purchase::latest('id')->first();
        $this->actingAs($this->admin)->deleteJson("/purchase/{$purchase->id}");
        $this->assertTrue($second->fresh()->is_short_stock);
        $this->actingAs($this->admin)->put("/booking/{$first->id}", $this->payload([[$t->brand_id, $t->id, 6]], ['booking_date' => '2026-10-01', 'status' => 'cancelled']));
        $this->assertFalse($second->fresh()->is_short_stock);
        $this->assertFalse($first->fresh()->is_short_stock);
    }

    public function test_b3_failed_edit_keeps_typed_rows(): void
    {
        $t = $this->type;
        $this->actingAs($this->admin)->post('/booking', $this->payload([[$t->brand_id, $t->id, 2]]));
        $booking = Booking::first();
        $this->actingAs($this->admin)->from("/booking/{$booking->id}/edit")
            ->put("/booking/{$booking->id}", $this->payload([[$t->brand_id, $t->id, 7]], ['customer_id' => '']))
            ->assertSessionHasErrors('customer_id');
        $this->actingAs($this->admin)->get("/booking/{$booking->id}/edit")->assertSee('value="7"', false);
    }
}
