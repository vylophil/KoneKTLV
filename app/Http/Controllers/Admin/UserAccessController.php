<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserAccessController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        return Inertia::render('admin/users', [
            'search' => $search,
            'users' => User::query()->with('roles')
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')))
                ->orderBy('name')->orderBy('id')->paginate(15)->withQueryString()
                ->through(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->sort()->values()->all(),
                    'isCurrentUser' => $user->is($request->user()),
                ]),
            'roleOptions' => array_keys(AccessControl::ROLE_PERMISSIONS),
            'rolePermissions' => AccessControl::ROLE_PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys(AccessControl::ROLE_PERMISSIONS))],
            'permissions' => ['prohibited'],
            'is_active' => ['prohibited'],
        ]);

        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['role' => 'Ask another administrator to change your access.']);
        }

        DB::transaction(fn () => $user->syncRoles([$validated['role']]));

        return back()->with('success', 'Access updated for '.$user->name.'.');
    }
}
