<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Support\SessionKey;
use Tests\TestCase;

class AuthenticationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_invalidates_guest_history_and_guest_only_routes_redirect_to_dashboard(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard')->assertSessionHas(SessionKey::CLEAR_HISTORY, true);

        $dashboard = $this->get('/dashboard')->assertOk();
        $this->assertTrue($dashboard->viewData('page')['clearHistory']);
        $this->assertTrue($dashboard->viewData('page')['encryptHistory']);

        foreach (['/login', '/register', '/forgot-password'] as $path) {
            $this->get($path)->assertRedirect('/dashboard');
        }

        // Normal member navigation must not invalidate all history on every visit.
        $profile = $this->get('/profile')->assertOk();
        $this->assertFalse($profile->viewData('page')['clearHistory'] ?? false);
    }

    public function test_logout_invalidates_private_history_and_cannot_restore_the_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => true])->assertRedirect();
        $this->get('/dashboard')->assertOk();
        $oldToken = session()->token();

        $this->post('/logout')->assertRedirect('/')->assertSessionHas(SessionKey::CLEAR_HISTORY, true);
        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
        $home = $this->get('/')->assertOk();
        $this->assertTrue($home->viewData('page')['clearHistory']);
        $this->assertTrue($home->viewData('page')['encryptHistory']);
        $this->assertNull($home->viewData('page')['props']['auth']['user']);

        foreach (['/dashboard', '/profile', '/applications', '/matches', '/saved-jobs', '/settings'] as $path) {
            $this->get($path)->assertRedirect('/login');
            $this->assertGuest();
        }

        $this->getJson('/backend/profiles')->assertUnauthorized();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_invalidates_guest_history(): void
    {
        $this->post('/register', [
            'name' => 'New Member', 'email' => 'new@example.com',
            'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password',
        ])->assertRedirect('/dashboard')->assertSessionHas(SessionKey::CLEAR_HISTORY, true);
        $this->assertTrue($this->get('/dashboard')->viewData('page')['clearHistory']);
    }

    public function test_inertia_authentication_changes_force_a_fresh_document_with_invalidated_history(): void
    {
        $user = User::factory()->create();
        $headers = ['X-Inertia' => 'true', 'Accept' => 'text/html'];
        $this->post('/login', ['email' => $user->email, 'password' => 'password'], $headers)
            ->assertStatus(409)->assertHeader('X-Inertia-Location', url('/dashboard'))
            ->assertSessionHas(SessionKey::CLEAR_HISTORY, true);
        $this->assertTrue($this->get('/dashboard')->viewData('page')['clearHistory']);

        $this->post('/logout', [], $headers)->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/'))->assertSessionHas(SessionKey::CLEAR_HISTORY, true);
        $this->assertGuest();
        $this->assertTrue($this->get('/')->viewData('page')['clearHistory']);
    }

    public function test_account_deletion_invalidates_private_history_after_the_session_is_destroyed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->delete('/account', ['username' => $user->email])
            ->assertRedirect('/')->assertSessionHas(SessionKey::CLEAR_HISTORY, true);
        $this->assertGuest();
        $this->assertTrue($this->get('/')->viewData('page')['clearHistory']);
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_failed_login_does_not_invalidate_history_and_pages_cannot_be_http_cached(): void
    {
        $user = User::factory()->create();
        $this->postJson('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertUnprocessable()->assertSessionMissing(SessionKey::CLEAR_HISTORY);
        $this->assertGuest();

        foreach (['/', '/login', '/register', '/jobs'] as $path) {
            $response = $this->get($path)->assertOk();
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertTrue($response->viewData('page')['encryptHistory']);
        }

        $response = $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
