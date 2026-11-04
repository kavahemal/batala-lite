<?php

namespace Tests\Feature;

use App\Models\Booking;

class PagesTest extends OperationsTestCase
{
    public function test_pages_render(): void
    {
        $booking = Booking::factory()->create();
        $this->actingAs($this->admin)->get('/booking/create')->assertOk()->assertSee('availableStock', false);
        $this->actingAs($this->admin)->get("/booking/{$booking->id}/edit")->assertOk()->assertSee($booking->booking_no);
        $this->actingAs($this->admin)->get("/booking/{$booking->id}")->assertOk();
        $this->actingAs($this->admin)->getJson('/booking?draw=1&start=0&length=10&filter_status=pending', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonPath('recordsFiltered', 1);
    }
}
