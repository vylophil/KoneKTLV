<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Skill> */
class SkillController extends ResourceController
{
    /** @var class-string<Skill> */
    protected string $model = Skill::class;

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('skills', 'name')->ignore($record)],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (in_array($action, ['create', 'store', 'edit', 'update', 'destroy'], true)) {
            abort_unless($user->can('manage skills'), 403);
        }
    }
}
