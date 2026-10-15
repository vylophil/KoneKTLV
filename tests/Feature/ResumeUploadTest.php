<?php

namespace Tests\Feature;

use App\Models\JobMatch;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase;

    private const PDF_MIME_TYPE = 'application/pdf';

    public function test_fortify_login_and_registration_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/login'));
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/register'));
    }

    public function test_a_user_can_upload_replace_and_download_a_private_resume(): void
    {
        Storage::fake('local');
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->createWithContent('resume.pdf', $this->textPdf('PHP Laravel developer')),
        ])->assertRedirect(route('matches.index'));

        $profile = Profile::query()->where('user_id', $user->id)->firstOrFail();
        $oldPath = $profile->resume_path;
        $this->assertSame('resume.pdf', $profile->resume_original_name);
        $this->assertTrue(Storage::disk('local')->exists($oldPath));

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->createWithContent('updated-resume.pdf', $this->textPdf('React engineer')),
        ])->assertRedirect(route('matches.index'));

        $profile->refresh();
        $this->assertSame('updated-resume.pdf', $profile->resume_original_name);
        $this->assertFalse(Storage::disk('local')->exists($oldPath));
        $this->assertTrue(Storage::disk('local')->exists($profile->resume_path));
        $this->actingAs($user)->get(route('profile.resume.download'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->get(route('profile.resume.download'))->assertNotFound();
    }

    public function test_resume_upload_rejects_non_pdf_and_files_over_five_megabytes(): void
    {
        Storage::fake('local');
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->create('resume.txt', 20, 'text/plain'),
        ])->assertSessionHasErrors('resume');

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->create('resume.pdf', 5121, self::PDF_MIME_TYPE),
        ])->assertSessionHasErrors('resume');

        $this->assertDatabaseCount('profiles', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles('resumes'));
    }

    public function test_uploading_a_text_pdf_computes_and_displays_job_matches(): void
    {
        Storage::fake('local');
        /** @var User $user */
        $user = User::factory()->create();
        $employer = User::factory()->create();
        $company = $employer->companies()->create(['name' => 'Backend Works']);
        $job = $employer->jobPostings()->create([
            'company_id' => $company->id,
            'title' => 'Laravel Backend Engineer',
            'description' => 'Build PHP APIs with MySQL and React.',
            'requirements' => 'Laravel PHP backend development',
            'job_type' => 'full_time',
        ]);
        $designerJob = $employer->jobPostings()->create([
            'company_id' => $company->id,
            'title' => 'Graphic Designer',
            'description' => 'Create illustrations and brand campaigns.',
            'requirements' => 'Adobe Illustrator and visual design',
            'job_type' => 'contract',
        ]);

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->createWithContent(
                'resume.pdf',
                $this->textPdf('PHP Laravel MySQL React backend engineer'),
            ),
        ])->assertRedirect(route('matches.index'));

        $match = JobMatch::query()->where('user_id', $user->id)->where('job_id', $job->id)->firstOrFail();
        $this->assertGreaterThan(0, (float) $match->match_score);
        $this->assertDatabaseCount('job_matches', 1);
        $this->actingAs($user)->get('/matches')->assertInertia(fn (Assert $page) => $page
            ->has('matches.data', 1)
            ->where('matches.data.0.job.id', $job->id)
            ->where('matches.data.0.job.title', 'Laravel Backend Engineer')
        );

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->createWithContent(
                'design-resume.pdf',
                $this->textPdf('Adobe Illustrator visual design'),
            ),
        ])->assertRedirect(route('matches.index'));

        $this->assertDatabaseMissing('job_matches', ['user_id' => $user->id, 'job_id' => $job->id]);
        $this->assertDatabaseHas('job_matches', ['user_id' => $user->id, 'job_id' => $designerJob->id]);
        $this->assertDatabaseCount('job_matches', 1);
        $this->actingAs($user)->get('/matches')->assertInertia(fn (Assert $page) => $page
            ->has('matches.data', 1)
            ->where('matches.data.0.job.id', $designerJob->id)
            ->where('matches.data.0.terms', fn ($terms) => in_array('illustrator', $terms->all(), true))
        );
        $newJob = $employer->jobPostings()->create([
            'company_id' => $company->id, 'title' => 'Illustrator',
            'description' => 'Create Adobe Illustrator designs.', 'job_type' => 'contract',
        ]);
        $this->post('/matches/refresh')->assertRedirect('/matches');
        $this->assertDatabaseHas('job_matches', ['user_id' => $user->id, 'job_id' => $newJob->id]);
    }

    public function test_upload_rejects_a_pdf_with_no_extractable_text(): void
    {
        Storage::fake('local');
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->from('/')->post(route('profile.resume.store'), [
            'resume' => UploadedFile::fake()->createWithContent('scanned-resume.pdf', $this->textPdf('')),
        ])->assertSessionHasErrors('resume');

        $this->assertDatabaseCount('profiles', 0);
        $this->assertDatabaseCount('job_matches', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles('resumes'));
    }

    private function textPdf(string $text): string
    {
        $stream = 'BT /F1 12 Tf 72 720 Td ('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text).') Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream).' >>'."\nstream\n{$stream}\nendstream",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($index = 1; $index <= 5; $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }
}
