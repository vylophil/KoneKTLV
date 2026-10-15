<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserSkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_skills_can_retrieve_each_other_with_pivot_details(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'PHP']);

        $user->skills()->attach($skill, [
            'proficiency_level' => 'advanced',
            'endorsement_count' => 3,
        ]);

        $linkedSkill = $user->skills()->firstOrFail();
        $linkedUser = $skill->users()->firstOrFail();

        $this->assertTrue($linkedSkill->is($skill));
        $this->assertTrue($linkedUser->is($user));
        $this->assertSame('advanced', $linkedSkill->pivot->proficiency_level);
        $this->assertSame(3, $linkedUser->pivot->endorsement_count);
        $this->assertNotNull($linkedSkill->pivot->created_at);
        $this->assertNotNull($linkedUser->pivot->updated_at);

        $user->skills()->updateExistingPivot($skill->id, ['proficiency_level' => 'expert']);
        $this->assertSame('expert', $skill->users()->firstOrFail()->pivot->proficiency_level);
    }

    public function test_attaching_a_skill_uses_default_proficiency_and_endorsement_count(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'Laravel']);

        $user->skills()->attach($skill);

        $this->assertDatabaseHas('user_skills', [
            'user_id' => $user->id,
            'skill_id' => $skill->id,
            'proficiency_level' => 'beginner',
            'endorsement_count' => 0,
        ]);
    }

    public function test_a_user_cannot_have_the_same_skill_twice(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'SQL']);
        $user->skills()->attach($skill);

        $this->expectException(UniqueConstraintViolationException::class);

        $user->skills()->attach($skill);
    }

    public function test_a_user_cannot_be_linked_to_a_missing_skill(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        $user->skills()->attach(999);
    }

    public function test_deleting_a_skill_removes_its_user_links(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'Java']);
        $user->skills()->attach($skill);

        $skill->delete();

        $this->assertDatabaseCount('user_skills', 0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_deleting_a_user_removes_their_skill_links(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['name' => 'Python']);
        $user->skills()->attach($skill);

        DB::table('users')->where('id', $user->id)->delete();

        $this->assertDatabaseCount('user_skills', 0);
        $this->assertDatabaseHas('skills', ['id' => $skill->id]);
    }
}
