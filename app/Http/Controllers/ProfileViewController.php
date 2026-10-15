<?php

namespace App\Http\Controllers;

use App\Models\ProfileView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<ProfileView> */
class ProfileViewController extends ResourceController
{
    /** @var class-string<ProfileView> */
    protected string $model = ProfileView::class;

    protected ?string $actorColumn = 'viewer_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'viewed_id' => ['required', 'integer', 'min:0', Rule::exists('users', 'id')->where('is_active', true), Rule::notIn([$this->user($request)->getKey()])],
        ];
    }

    /** @return Builder<ProfileView> */
    protected function query(Request $request): Builder
    {
        $id = $this->user($request)->getKey();

        return ProfileView::query()->where(fn ($query) => $query->where('viewer_id', $id)->orWhere('viewed_id', $id));
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if (in_array($action, ['update', 'edit', 'destroy'], true) && $record !== null) {
            abort_unless($record->getAttribute('viewer_id') === $user->getKey(), 403);
        }
    }
}
