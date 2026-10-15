<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BackendValidationAuditTest extends TestCase
{
    use RefreshDatabase;

    public static function historyResources(): array
    {
        return [['experience'], ['education']];
    }

    #[DataProvider('historyResources')]
    public function test_partial_updates_cannot_move_start_date_after_existing_end_date(string $resource): void
    {
        $user = User::factory()->create();
        $entry = $resource === 'experience'
            ? $user->workExperience()->create(['company_name' => 'Company', 'job_title' => 'Developer', 'start_date' => '2020-01-01', 'end_date' => '2021-01-01'])
            : $user->education()->create(['institution' => 'University', 'degree' => 'bachelors', 'start_date' => '2020-01-01', 'end_date' => '2021-01-01']);

        $this->actingAs($user)->patchJson(route('backend.'.$resource.'.update', $entry->id), ['start_date' => '2022-01-01'])
            ->assertUnprocessable()->assertJsonValidationErrors('start_date');
        $this->assertSame('2020-01-01', $entry->fresh()->start_date->format('Y-m-d'));
    }

    #[DataProvider('historyResources')]
    public function test_array_dates_return_validation_errors_instead_of_server_errors(string $resource): void
    {
        $user = User::factory()->create();
        $payload = $resource === 'experience'
            ? ['company_name' => 'Company', 'job_title' => 'Developer']
            : ['institution' => 'University', 'degree' => 'bachelors'];

        $this->actingAs($user)->postJson(route('backend.'.$resource.'.store'), $payload + ['start_date' => ['invalid'], 'end_date' => '2021-01-01'])
            ->assertUnprocessable()->assertJsonValidationErrors('start_date');
    }

    public function test_array_foreign_keys_return_validation_errors_instead_of_server_errors(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'employer'])->save();
        $skill = Skill::create(['name' => 'PHP']);

        $this->actingAs($user)->postJson(route('backend.user-skills.store'), ['skill_id' => [1]])
            ->assertUnprocessable()->assertJsonValidationErrors('skill_id');
        $this->postJson(route('backend.endorsements.store'), ['endorsed_user_id' => [1], 'skill_id' => $skill->id])
            ->assertUnprocessable()->assertJsonValidationErrors('endorsed_user_id');
    }

    #[DataProvider('historyResources')]
    public function test_binary_and_impossible_dates_are_rejected(string $resource): void
    {
        $user = User::factory()->create();
        $payload = $resource === 'experience'
            ? ['company_name' => 'Company', 'job_title' => 'Developer']
            : ['institution' => 'University', 'degree' => 'bachelors'];
        $this->actingAs($user);

        foreach (["2020-01-0\0", '2020-02-30', 'not-a-date'] as $date) {
            $this->postJson(route('backend.'.$resource.'.store'), $payload + ['start_date' => $date])
                ->assertUnprocessable()->assertJsonValidationErrors('start_date');
        }
    }
}
