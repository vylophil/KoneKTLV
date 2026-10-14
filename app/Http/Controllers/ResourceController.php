<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/** @template TModel of Model */
abstract class ResourceController extends Controller
{
    /** @var class-string<TModel> */
    protected string $model;

    protected ?string $actorColumn = null;

    /** @return array<string, list<mixed>> */
    abstract protected function rules(Request $request, ?Model $record = null): array;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'index');

        return response()->json($this->query($request)->orderBy('id')->paginate(25));
    }

    public function create(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'create');

        return response()->json(['fields' => $this->fields($request)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'store');
        $record = new $this->model($request->validate($this->validationRules($request)));
        if ($this->actorColumn !== null) {
            $record->setAttribute($this->actorColumn, $this->user($request)->getKey());
        }

        try {
            DB::transaction(fn () => $record->save());
        } catch (UniqueConstraintViolationException) {
            abort(409, 'This record already exists.');
        }

        return response()->json($record->refresh(), 201);
    }

    public function show(Request $request, string $record): JsonResponse
    {
        $item = $this->query($request)->findOrFail($record);
        $this->authorizeAction($request, 'show', $item);

        return response()->json($item);
    }

    public function edit(Request $request, string $record): JsonResponse
    {
        $item = $this->query($request)->findOrFail($record);
        $this->authorizeAction($request, 'edit', $item);

        return response()->json(['record' => $item, 'fields' => $this->fields($request, $item)]);
    }

    public function update(Request $request, string $record): JsonResponse
    {
        $item = $this->query($request)->findOrFail($record);
        $this->authorizeAction($request, 'update', $item);

        try {
            $data = $request->validate($this->validationRules($request, $item));
            DB::transaction(fn () => $item->update($data));
        } catch (UniqueConstraintViolationException) {
            abort(409, 'This record already exists.');
        }

        return response()->json($item->refresh());
    }

    public function destroy(Request $request, string $record): Response
    {
        $item = $this->query($request)->findOrFail($record);
        $this->authorizeAction($request, 'destroy', $item);
        DB::transaction(fn () => $item->delete());

        return response()->noContent();
    }

    /** @return Builder<TModel> */
    protected function query(Request $request): Builder
    {
        $user = $this->user($request);
        $query = $this->model::query();

        return $this->actorColumn === null ? $query : $query->where($this->actorColumn, $user->getKey());
    }

    protected function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->getAttribute('is_active'), 403);

        return $user;
    }

    protected function authorizeAction(Request $request, string $action, ?Model $record = null): void
    {
        $this->user($request);
    }

    /** @return array<string, list<mixed>> */
    private function validationRules(Request $request, ?Model $record = null): array
    {
        $rules = $this->rules($request, $record);
        if ($record !== null) {
            foreach ($rules as &$rule) {
                array_unshift($rule, 'sometimes');
            }
            unset($rule);
        }
        foreach (['id', 'created_at', 'updated_at', 'applied_at', 'saved_at', 'computed_at', 'sent_at', 'viewed_at', $this->actorColumn] as $field) {
            if ($field !== null) {
                $rules[$field] = ['prohibited'];
            }
        }

        return $rules;
    }

    /** @return list<string> */
    private function fields(Request $request, ?Model $record = null): array
    {
        return array_keys(array_filter($this->rules($request, $record),
            fn ($rules) => ! in_array('prohibited', $rules, true)));
    }

    protected function connected(User $user, int $peer): bool
    {
        return Connection::query()->where('status', 'accepted')
            ->where(fn ($query) => $query
                ->where(fn ($pair) => $pair->where('requester_id', $user->getKey())->where('receiver_id', $peer))
                ->orWhere(fn ($pair) => $pair->where('receiver_id', $user->getKey())->where('requester_id', $peer)))
            ->exists();
    }
}
