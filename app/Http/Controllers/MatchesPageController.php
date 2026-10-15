<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ResumeJobMatcher;
use App\Support\JobView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Throwable;

class MatchesPageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->user($request);
        $matches = $user->jobMatches()->whereHas('jobPosting', fn ($query) => $query->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', today())))
            ->with('jobPosting.company')->orderByDesc('match_score')->orderBy('id')->paginate(12);

        return Inertia::render('jobs/matches', [
            'matches' => $matches->withQueryString()->through(fn ($match) => [
                'job' => JobView::data($match->jobPosting), 'score' => (float) $match->match_score,
                'terms' => $match->matched_terms ?? [], 'computedAt' => $match->computed_at?->toIso8601String(),
            ]),
            'hasResume' => is_string($user->profile?->resume_path) && Storage::disk('local')->exists($user->profile->resume_path),
        ]);
    }

    public function refresh(Request $request, ResumeJobMatcher $matcher): RedirectResponse
    {
        $user = $this->user($request);
        $path = $user->profile?->resume_path;
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['resume' => 'Upload a resume in your profile first.']);
        }
        try {
            $config = new Config;
            $config->setDecodeMemoryLimit(50 * 1024 * 1024);
            $text = (new Parser([], $config))->parseFile(Storage::disk('local')->path($path))->getText();
        } catch (Throwable) {
            throw ValidationException::withMessages(['resume' => 'We could not read your saved PDF. Replace it with a text-based PDF in your profile.']);
        }
        if (trim($text) === '') {
            throw ValidationException::withMessages(['resume' => 'Your PDF has no selectable text. Please replace it in your profile.']);
        }
        $count = $matcher->refresh($user, $text);

        return to_route('matches.index')->with('success', "Matches refreshed. {$count} roles share keywords with your resume.");
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->is_active, 403);

        return $user;
    }
}
