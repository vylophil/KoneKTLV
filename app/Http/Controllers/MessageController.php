<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** @extends ResourceController<Message> */
class MessageController extends ResourceController
{
    /** @var class-string<Message> */
    protected string $model = Message::class;

    protected ?string $actorColumn = 'sender_id';

    /** @return array<string, list<mixed>> */
    protected function rules(Request $request, ?Model $record = null): array
    {
        if ($record !== null) {
            return $record->getAttribute('sender_id') === $this->user($request)->getKey()
                ? ['receiver_id' => ['prohibited'], 'content' => ['required', 'string', 'max:10000'], 'is_read' => ['prohibited']]
                : ['receiver_id' => ['prohibited'], 'content' => ['prohibited'], 'is_read' => ['required', 'boolean']];
        }

        return ['receiver_id' => ['bail', 'required', 'integer', Rule::exists('users', 'id')->where('is_active', true),
            function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                if (! $this->connected($this->user($request), (int) $value)) {
                    $fail('Messaging requires an accepted connection.');
                }
            }], 'content' => ['required', 'string', 'max:10000'], 'is_read' => ['prohibited']];
    }

    /** @return Builder<Message> */
    protected function query(Request $request): Builder
    {
        $id = $this->user($request)->getKey();

        return Message::query()->where(fn ($query) => $query->where('sender_id', $id)->orWhere('receiver_id', $id));
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $user = $this->user($request);
        if ($action === 'destroy' && $record !== null) {
            abort_unless($record->getAttribute('sender_id') === $user->getKey(), 403);
        }
    }
}
