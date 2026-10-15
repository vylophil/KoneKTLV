<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SkillController;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();

        return Inertia::render('staff/skills', [
            'search' => $search,
            'skills' => Skill::query()
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%')))
                ->orderBy('name')->paginate(15, ['id', 'name', 'category'])->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        app(SkillController::class)->store($request);

        return back()->with('success', 'Skill added to the catalog.');
    }

    public function update(Request $request, string $record): RedirectResponse
    {
        app(SkillController::class)->update($request, $record);

        return back()->with('success', 'Skill updated.');
    }

    public function destroy(Request $request, string $record): RedirectResponse
    {
        app(SkillController::class)->destroy($request, $record);

        return back()->with('success', 'Skill removed from the catalog.');
    }
}
