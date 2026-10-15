<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ResumeJobMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ResumeController extends Controller
{
    public function store(Request $request, ResumeJobMatcher $matcher): RedirectResponse
    {
        $validated = $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        /** @var UploadedFile $file */
        $file = $validated['resume'];
        try {
            $config = new Config;
            $config->setDecodeMemoryLimit(50 * 1024 * 1024);
            $resumeText = (new Parser([], $config))->parseFile($file->getPathname())->getText();
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'resume' => 'We could not read this PDF. Please upload a text-based PDF.',
            ]);
        }

        if (trim($resumeText) === '') {
            throw ValidationException::withMessages([
                'resume' => 'This PDF has no selectable text. Please upload a text-based resume.',
            ]);
        }

        $profile = $user->profile()->firstOrCreate([]);
        $oldPath = $profile->resume_path;
        $path = $file->store("resumes/{$user->getKey()}", 'local');
        abort_unless(is_string($path), 500, 'Resume upload failed.');

        try {
            DB::transaction(function () use ($profile, $path, $file, $matcher, $user, $resumeText): void {
                $profile->update([
                    'resume_path' => $path,
                    'resume_original_name' => $file->getClientOriginalName(),
                ]);
                $matcher->refresh($user, $resumeText);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if (is_string($oldPath) && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->to(route('home').'#matches');
    }

    public function download(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $profile = $user->profile;
        abort_unless(
            is_string($profile?->resume_path) && Storage::disk('local')->exists($profile->resume_path),
            404,
        );

        return response()->download(
            Storage::disk('local')->path($profile->resume_path),
            $profile->resume_original_name ?? 'resume.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
