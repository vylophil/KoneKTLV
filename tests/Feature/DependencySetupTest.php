<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DependencySetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_fortify_authenticates_a_user(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_role_middleware_restricts_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->assertDatabaseCount('roles', 3);

        Route::get('/test-admin', fn () => response()->noContent())
            ->middleware(['web', 'auth', 'role:Admin']);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/test-admin')->assertForbidden();

        $user->assignRole('Admin');
        $this->get('/test-admin')->assertNoContent();
    }
}
