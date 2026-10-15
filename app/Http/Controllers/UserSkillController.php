<?php

namespace App\Http\Controllers;

use App\Models\UserSkill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<UserSkill> */
class UserSkillController extends ResourceController
{
    /** @var class-string<UserSkill> */
    protected string $model = UserSkill::class;

    protected ?string $actorColumn = 'user_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'skill_id' => ['required', 'integer', 'min:0', Rule::exists('skills', 'id'), Rule::unique('user_skills', 'skill_id')->where('user_id', $this->user($request)->getKey())->ignore($record)],
            'proficiency_level' => ['sometimes', Rule::in(['beginner', 'intermediate', 'advanced', 'expert'])],
        ];
    }
}
