<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Support\JobView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->has('search')) {
            return to_route('jobs.index', ['search' => $request->string('search')->limit(100)->toString()]);
        }
        if ($request->user()) {
            return to_route('dashboard');
        }

        return Inertia::render('home', [
            'jobs' => JobPosting::query()->with('company')->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today()))
                ->latest()->orderByDesc('id')->limit(3)->get()->map(fn (JobPosting $job) => JobView::data($job)),
        ]);
    }
}
