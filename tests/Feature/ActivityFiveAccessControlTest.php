<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Database\Seeders\ActivityFiveDemoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ActivityFiveAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_seeder_is_repeatable_backfills_members_and_preserves_staff_assignments(): void
    {
        $member = User::factory()->create();
        $admin = $this->staff('Admin');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('permissions', 3);
        $this->assertTrue($member->fresh()->hasExactRoles(['User']));
        $this->assertTrue($admin->fresh()->hasExactRoles(['Admin']));
        $this->assertTrue($admin->fresh()->can('manage skills'));
        $this->assertTrue($admin->fresh()->can('manage job matches'));
        $this->assertTrue($admin->fresh()->can('manage user roles'));
        $this->assertFalse($member->fresh()->can('manage skills'));
    }

    public function test_registration_assigns_only_the_member_role_and_logout_ends_access(): void
    {
        $this->post('/register', [
            'name' => 'New Member', 'email' => 'member@example.com',
            'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password',
        ])->assertRedirect('/dashboard')->assertSessionHasNoErrors();

        $member = User::where('email', 'member@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($member);
        $this->assertTrue($member->hasExactRoles(['User']));
        $this->assertTrue(Hash::check('new-secure-password', $member->password));
        $this->get('/admin/users')->assertForbidden();
        $this->get('/staff/skills')->assertForbidden();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_registration_rejects_privilege_fields_and_invalid_credentials(): void
    {
        $this->postJson('/register', [
            'name' => 'Injected Admin', 'email' => 'injected@example.com',
            'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password',
            'role' => 'Admin', 'roles' => ['Admin'], 'permissions' => ['manage skills'], 'is_active' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors(['role', 'roles', 'permissions', 'is_active']);
        $this->assertDatabaseMissing('users', ['email' => 'injected@example.com']);

        $this->postJson('/register', [
            'name' => '', 'email' => 'not-an-email', 'password' => 'short', 'password_confirmation' => 'different',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_rejects_wrong_passwords_and_throttles_repeated_attempts(): void
    {
        $member = $this->staff('User');
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', ['email' => $member->email, 'password' => 'incorrect-password'])
                ->assertUnprocessable()->assertJsonValidationErrors('email');
        }
        $this->postJson('/login', ['email' => $member->email, 'password' => 'password'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_password_reset_rejects_invalid_and_reused_tokens(): void
    {
        $member = $this->staff('User');
        $payload = [
            'email' => $member->email, 'token' => 'invalid-token',
            'password' => 'replacement-password', 'password_confirmation' => 'replacement-password',
        ];
        $this->postJson('/reset-password', $payload)->assertUnprocessable()->assertJsonValidationErrors('email');
        $payload['token'] = Password::createToken($member);
        $this->postJson('/reset-password', $payload)->assertOk();
        $this->assertTrue(Hash::check('replacement-password', $member->fresh()->password));
        $this->postJson('/reset-password', $payload)->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_guests_and_members_cannot_access_staff_pages_or_forge_mutations(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/staff/skills')->assertRedirect('/login');
        $this->putJson('/admin/users/1/role', ['role' => 'Admin'])->assertUnauthorized();
        $this->postJson('/staff/skills', ['name' => 'Forged'])->assertUnauthorized();

        $member = $this->staff('User');
        $target = User::factory()->create();
        $skill = Skill::create(['name' => 'Existing']);
        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->get('/staff/skills')->assertForbidden();
        $this->putJson('/admin/users/'.$target->id.'/role', ['role' => 'Admin'])->assertForbidden();
        $this->postJson('/staff/skills', ['name' => 'Forged'])->assertForbidden();
        $this->putJson('/staff/skills/'.$skill->id, ['name' => 'Changed'])->assertForbidden();
        $this->deleteJson('/staff/skills/'.$skill->id)->assertForbidden();
        $this->postJson('/backend/skills', ['name' => 'Forged'])->assertForbidden();
        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'Existing']);
        $this->assertFalse($target->fresh()->hasRole('Admin'));
    }

    public function test_manager_can_maintain_skills_through_web_and_json_but_cannot_manage_roles(): void
    {
        $manager = $this->staff('Manager');
        $this->actingAs($manager)->get('/staff/skills')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('staff/skills'));
        $this->post('/staff/skills', ['name' => 'Laravel', 'category' => 'Backend'])->assertRedirect()->assertSessionHasNoErrors();
        $skill = Skill::where('name', 'Laravel')->firstOrFail();
        $this->put('/staff/skills/'.$skill->id, ['name' => 'Laravel PHP', 'category' => 'Web'])->assertRedirect();
        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'Laravel PHP', 'category' => 'Web']);
        $this->postJson('/backend/skills', ['name' => 'React'])->assertCreated();
        $this->delete('/staff/skills/'.$skill->id)->assertRedirect();
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
        $this->get('/admin/users')->assertForbidden();
        $this->putJson('/admin/users/'.$manager->id.'/role', ['role' => 'Admin'])->assertForbidden();
        $this->getJson('/backend/job-matches/create')->assertForbidden();
    }

    public function test_skill_forms_validate_uniqueness_length_and_server_owned_fields(): void
    {
        $this->actingAs($this->staff('Manager'));
        Skill::create(['name' => 'Laravel']);
        $this->postJson('/staff/skills', ['name' => 'Laravel'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/staff/skills', ['name' => '', 'category' => str_repeat('a', 101)])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'category']);
        $this->postJson('/staff/skills', ['name' => 'Injected', 'id' => 99])
            ->assertUnprocessable()->assertJsonValidationErrors('id');
    }

    public function test_admin_can_change_another_users_access_without_changing_their_domain_role(): void
    {
        $admin = $this->staff('Admin');
        $member = $this->staff('User');
        $member->forceFill(['role' => 'employer'])->save();
        $this->actingAs($admin)->get('/admin/users')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('admin/users')
                ->has('roleOptions', 3)->has('users.data', 2));
        $this->put('/admin/users/'.$member->id.'/role', ['role' => 'Manager'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($member->fresh()->hasExactRoles(['Manager']));
        $this->assertSame('employer', $member->fresh()->getAttribute('role'));
        $this->actingAs($member->fresh())->get('/staff/skills')->assertOk();

        $this->actingAs($admin)->put('/admin/users/'.$member->id.'/role', ['role' => 'User'])->assertRedirect();
        $this->actingAs($member->fresh())->get('/staff/skills')->assertForbidden();
        $this->postJson('/backend/skills', ['name' => 'Revoked'])->assertForbidden();
    }

    public function test_admin_role_form_rejects_unknown_roles_self_changes_and_permission_injection(): void
    {
        $admin = $this->staff('Admin');
        $target = $this->staff('User');
        $this->actingAs($admin)->putJson('/admin/users/'.$target->id.'/role', ['role' => 'Super Admin'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->putJson('/admin/users/'.$admin->id.'/role', ['role' => 'User'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->putJson('/admin/users/'.$target->id.'/role', ['role' => 'Admin', 'permissions' => ['manage user roles']])
            ->assertUnprocessable()->assertJsonValidationErrors('permissions');
        $this->assertTrue($admin->fresh()->hasRole('Admin'));
        $this->assertTrue($target->fresh()->hasExactRoles(['User']));
    }

    public function test_a_permission_alone_cannot_bypass_the_admin_role_guard(): void
    {
        $member = $this->staff('User');
        $member->givePermissionTo('manage user roles');
        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->putJson('/admin/users/'.$member->id.'/role', ['role' => 'Admin'])->assertForbidden();
    }

    public function test_a_staff_role_without_its_permission_cannot_bypass_the_permission_guard(): void
    {
        $manager = $this->staff('Manager');
        $manager->roles()->firstOrFail()->revokePermissionTo('manage skills');
        $this->actingAs($manager->fresh())->get('/staff/skills')->assertForbidden();
        $this->postJson('/backend/skills', ['name' => 'Denied'])->assertForbidden();
    }

    public function test_shared_auth_props_expose_effective_access_without_private_account_fields(): void
    {
        $this->get('/login')->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', null)->has('auth.roles', 0)->has('auth.permissions', 0));
        $this->actingAs($this->staff('Manager'))->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('auth.roles', ['Manager'])->where('auth.permissions', ['manage skills'])
            ->missing('auth.user.password')->missing('auth.user.remember_token')->missing('auth.user.is_active'));
    }

    public function test_inactive_accounts_cannot_login_or_use_existing_authenticated_sessions(): void
    {
        $admin = $this->staff('Admin');
        $admin->forceFill(['is_active' => false])->save();
        $this->postJson('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
        $this->get('/dashboard')->assertForbidden();
        $this->postJson('/backend/skills', ['name' => 'Inactive'])->assertForbidden();
    }

    public function test_staff_and_role_mutations_require_csrf_tokens(): void
    {
        $admin = $this->staff('Admin');
        $member = $this->staff('User');
        $this->app->detectEnvironment(fn () => 'local');
        $this->actingAs($admin)->postJson('/staff/skills', ['name' => 'No token'])->assertStatus(419);
        $this->putJson('/admin/users/'.$member->id.'/role', ['role' => 'Admin'])->assertStatus(419);
        $this->withSession(['_token' => 'activity-five-token'])->postJson('/staff/skills', ['name' => 'With token'],
            ['X-CSRF-TOKEN' => 'activity-five-token'])->assertRedirect();
    }

    public function test_demo_seeder_is_local_only_and_does_not_promote_existing_accounts(): void
    {
        $existing = User::factory()->create(['email' => 'admin@example.com']);
        $this->seed(ActivityFiveDemoSeeder::class);
        $this->assertFalse($existing->fresh()->hasRole('Admin'));
        $manager = User::where('email', 'manager@example.com')->firstOrFail();
        $this->assertTrue($manager->hasRole('Manager'));
        $manager->syncRoles(['User']);
        $this->seed(ActivityFiveDemoSeeder::class);
        $this->assertTrue($manager->fresh()->hasExactRoles(['User']));

        User::where('email', 'manager@example.com')->delete();
        $this->app->detectEnvironment(fn () => 'production');
        app(ActivityFiveDemoSeeder::class)->run();
        $this->assertDatabaseMissing('users', ['email' => 'manager@example.com']);
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
