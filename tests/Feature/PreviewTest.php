<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PreviewTest extends OperationsTestCase
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function lockedModules(): array
    {
        return array_filter(config('permissions'), fn ($menu) => $menu['locked'] ?? false);
    }

    public function test_every_locked_module_has_a_preview_page(): void
    {
        $locked = $this->lockedModules();
        $this->assertSame(
            ['delivery', 'stock-adjustments', 'trucks', 'drivers', 'pending-booking-report', 'empties-owed-report', 'customer-history-report', 'driver-report', 'customer-import'],
            array_keys($locked)
        );

        // Staff has no permissions at all and still sees every preview
        $staff = User::where('email', 'staff@example.com')->first();
        foreach ($locked as $key => $menu) {
            $this->assertNotEmpty($menu['preview']['summary'], $key);
            $this->assertNotEmpty($menu['preview']['images'], $key);
            $this->assertArrayNotHasKey('permissions', $menu, $key);

            $this->actingAs($staff)->get("/full-version/$key")->assertOk()
                ->assertSee($menu['title'])->assertSee('Full version')
                ->assertSee('This module is part of the full version, which is private. Live demo available on request.')
                ->assertSee($menu['preview']['points'][0])
                ->assertDontSee('Coming soon');
        }

        // Unknown modules, normal modules and headers have no preview; guests go to login
        $this->actingAs($staff)->get('/full-version/nothing')->assertNotFound();
        $this->actingAs($staff)->get('/full-version/booking')->assertNotFound();
        $this->actingAs($staff)->get('/full-version/separator-reports')->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->get('/full-version/delivery')->assertRedirect('/login');
    }

    public function test_preview_shows_existing_screenshots_only(): void
    {
        $dir = public_path('images/preview');
        $file = "$dir/loading-sheet.png";
        $existed = is_file($file);
        if (! $existed) {
            file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        }

        try {
            $res = $this->actingAs($this->admin)->get('/full-version/delivery')->assertOk()
                ->assertSee(asset('images/preview/loading-sheet.png'))->assertSee('Loading Sheet');
            $this->assertContains('loading-sheet.png', $res->viewData('images')->all());
            foreach ($res->viewData('images') as $image) {
                $this->assertFileExists("$dir/$image");
            }
        } finally {
            if (! $existed) {
                unlink($file);
            }
        }
    }

    public function test_sidebar_shows_locked_items_to_everyone(): void
    {
        $staff = User::where('email', 'staff@example.com')->first();
        $res = $this->actingAs($staff)->get('/home')->assertOk();
        foreach ($this->lockedModules() as $key => $menu) {
            if ($menu['in_menu'] ?? true) {
                $res->assertSee(route('preview', $key));
            } else {
                $res->assertDontSee(route('preview', $key));
            }
        }
        // Headers with only locked items still show; items staff has no permission for don't
        $res->assertSee('Trucks &amp; drivers', false)->assertSee('Reports')->assertSee('title="Part of the full version"', false)
            ->assertDontSee(route('booking.index'))->assertDontSee(route('stock-report.index'));

        // Admin: normal items plus locked ones; Import button on Customers opens its preview
        $this->actingAs($this->admin)->get('/home')->assertSee(route('booking.index'))->assertSee(route('preview', 'delivery'));
        $this->actingAs($this->admin)->get('/customers')->assertSee(route('preview', 'customer-import'));
        $this->actingAs($this->admin)->get('/full-version/delivery')->assertSee('class="sidebar-item active"', false);
    }

    public function test_locked_modules_have_no_permissions_and_no_permission_rows(): void
    {
        $expected = collect(config('permissions'))->pluck('permissions')->filter()->flatten()->sort()->values()->all();
        $this->assertSame($expected, Permission::orderBy('name')->pluck('name')->sort()->values()->all());
        foreach (array_keys($this->lockedModules()) as $key) {
            $this->assertFalse(Permission::where('name', 'like', "$key.%")->exists(), $key);
        }

        // Role-permission screen: no rows / empty headers for locked modules
        $staff = Role::findByName('staff');
        $rows = $this->actingAs($this->admin)->get("/role-permissions/{$staff->id}/edit")->assertOk()
            ->assertSee('data-permission="booking.edit"', false)->assertDontSee('data-permission="delivery', false)
            ->viewData('permissionsConfig');
        $this->assertSame(
            ['separator-booking', 'customers', 'booking', 'purchase', 'separator-cylinders', 'cylinder-brands', 'cylinder-types', 'separator-reports', 'stock-report', 'separator-users', 'users'],
            array_keys($rows)
        );
    }
}
