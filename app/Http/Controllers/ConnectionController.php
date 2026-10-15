<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Connection> */
class ConnectionController extends ResourceController
{
    /** @var class-string<Connection> */
    protected string $model = Connection::class;

    protected ?string $actorColumn = 'requester_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        $id = $this->user($request)->getKey();
        if ($record !== null) {
            return ['receiver_id' => ['prohibited'],
                'status' => ['required', Rule::in($record->getAttribute('receiver_id') === $id ? ['accepted', 'rejected', 'blocked'] : ['blocked'])],
                'message' => [$record->getAttribute('requester_id') === $id && $record->getAttribute('status') === 'pending' ? 'nullable' : 'prohibited', 'string', 'max:10000']];
        }

        return ['receiver_id' => ['required', 'integer', Rule::notIn([$id]), Rule::exists('users', 'id')->where('is_active', true), Rule::unique('connections', 'receiver_id')->where('requester_id', $id)],
            'status' => ['prohibited'], 'message' => ['nullable', 'string', 'max:10000']];
    }

    /** @return Builder<Connection> */
    protected function query(Request $request): Builder
    {
        $id = $this->user($request)->getKey();

        return Connection::query()->where(fn ($query) => $query->where('requester_id', $id)->orWhere('receiver_id', $id));
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
        if (in_array($action, ['update', 'edit'], true) && $record !== null) {
            abort_if($record->getAttribute('status') === 'blocked', 409, 'Blocked connections cannot be updated.');
        }
    }
}
