<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Purchase;
use App\Models\Truck;
use App\Services\BookingService;
use App\Services\StockService;
use Illuminate\Console\Command;

class FakeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fake-data
                            {--customers=5 : Number of fake customers}
                            {--purchases=5 : Number of fake purchases}
                            {--bookings=10 : Number of fake bookings}
                            {--delivered=3 : How many of them are marked delivered}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create fake test data with all factories (customers, trucks, drivers, purchases, bookings). Local only.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->isProduction()) {
            $this->error('Refusing to create fake data in production.');

            return self::FAILURE;
        }

        // Customers, plus the trucks / drivers purchases come with
        Customer::factory((int) $this->option('customers'))->create();

        Truck::factory(3)->create();
        Truck::factory()->inactive()->create();
        Driver::factory(2)->create();
        Driver::factory(2)->create(['name' => fake()->randomElement(['Ramesh Patel', 'Suresh Rathod', 'Mahesh Solanki'])]);
        Driver::factory()->withoutMobile()->create();
        Driver::factory()->inactive()->create();

        // Purchases before bookings so there is stock to book against
        Purchase::factory((int) $this->option('purchases'))->create();
        Booking::factory((int) $this->option('bookings'))->create();

        // Some bookings handed over already, the same way the "Mark delivered" button does it.
        // Only ones the godown covers (not short), so full stock never goes below zero
        app(StockService::class)->refreshShortStock();
        Booking::where('status', BookingStatus::PENDING)->where('is_short_stock', false)
            ->inRandomOrder()->take((int) $this->option('delivered'))->get()
            ->each(fn (Booking $booking) => app(BookingService::class)->markDelivered($booking));

        // Factories skip the booking form, so flag short-stock bookings here
        $short = app(StockService::class)->refreshShortStock();

        $this->info("Fake data created ({$short} short-stock bookings). Totals now:");
        $this->table(['Customers', 'Purchases', 'Bookings', 'Trucks', 'Drivers'], [[Customer::count(), Purchase::count(), Booking::count(), Truck::count(), Driver::count()]]);

        return self::SUCCESS;
    }
}
