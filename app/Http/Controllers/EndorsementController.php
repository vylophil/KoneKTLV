<?php

namespace App\Http\Controllers;

use App\Models\Endorsement;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Endorsement> */
class EndorsementController extends ResourceController
{
    /** @var class-string<Endorsement> */
    protected string $model = Endorsement::class;

    protected ?string $actorColumn = 'endorser_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        $peer = $request->input('endorsed_user_id', $record?->getAttribute('endorsed_user_id'));

        return ['endorsed_user_id' => $record !== null ? ['prohibited'] : ['bail', 'required', 'integer', Rule::exists('users', 'id')->where('is_active', true),
            function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                if (! $this->connected($this->user($request), (int) $value)) {
                    $fail('Endorsements require an accepted connection.');
                }
            }],
            'skill_id' => ['required', 'integer', Rule::exists('user_skills', 'skill_id')->where('user_id', $peer),
                Rule::unique('endorsements', 'skill_id')->where('endorser_id', $this->user($request)->getKey())->where('endorsed_user_id', $peer)->ignore($record)]];
    }

    /** @return Builder<Endorsement> */
    protected function query(Request $request): Builder
    {
        $id = $this->user($request)->getKey();

        return Endorsement::query()->where(fn ($query) => $query->where('endorser_id', $id)->orWhere('endorsed_user_id', $id));
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (in_array($action, ['update', 'edit', 'destroy'], true) && $record !== null) {
            abort_unless($record->getAttribute('endorser_id') === $user->getKey(), 403);
        }
        if ($action === 'update' && $record !== null) {
            abort_unless($this->connected($user, (int) $record->getAttribute('endorsed_user_id')), 403);
        }
    }
}
