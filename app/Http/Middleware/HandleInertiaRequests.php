<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $previousUser = $request->user()?->getAuthIdentifier();
        $response = parent::handle($request, $next);

        // Run after Fortify regenerates/invalidates the session so the flag survives logout.
        if ($previousUser !== $request->user()?->getAuthIdentifier()) {
            Inertia::clearHistory();

            // A fresh document also drops React state from the previous session.
            if ($request->header('X-Inertia') && $response instanceof RedirectResponse) {
                $response = Inertia::location($response->getTargetUrl());
            }
        }

        // Every app page includes session-specific props, including public pages.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'flash' => ['success' => fn () => $request->session()->get('success')],
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'role']),
                'roles' => $user?->getRoleNames()->sort()->values()->all() ?? [],
                'permissions' => $user?->getAllPermissions()->pluck('name')->sort()->values()->all() ?? [],
            ],
        ];
    }
}
