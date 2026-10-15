<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JSearch;
use App\Services\JSearchUnavailable;
use App\Support\JobView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class JSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.jsearch.enabled' => true, 'services.jsearch.key' => 'test-jsearch-secret']);
        Http::preventStrayRequests();
    }

    public function test_philippine_search_imports_without_employer_accounts_and_preserves_unknown_fields(): void
    {
        Http::fake(['api.openwebninja.com/*' => Http::response($this->payload())]);
        $result = app(JSearch::class)->search(['search' => 'developer', 'location' => 'Manila']);
        $job = $result['jobs']->sole();
        $this->assertNull($job->employer_id);
        $this->assertNull($job->company_id);
        $this->assertNull($job->salary_min);
        $this->assertNull($job->work_arrangement);
        $this->assertSame('Example PH Studio', JobView::data($job)['company']);
        $this->assertSame('Arrangement not specified', JobView::data($job)['workArrangement']);
        $this->assertDatabaseCount('users', 0);
        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'test-jsearch-secret')
            && $request['country'] === 'ph' && $request['language'] === 'en' && $request['num_pages'] === 1
            && ! str_contains($request->url(), 'test-jsearch-secret'));
    }

    public function test_repeated_searches_use_cache_and_do_not_duplicate_jobs_or_spend_more_credits(): void
    {
        Http::fake(['api.openwebninja.com/*' => Http::response($this->payload())]);
        app(JSearch::class)->search([]);
        app(JSearch::class)->search([]);
        Http::assertSentCount(1);
        $this->assertDatabaseCount('job_postings', 1);
        $this->assertDatabaseHas('job_api_usage', ['provider' => 'jsearch', 'requests' => 1]);
    }

    public function test_jobs_page_uses_cursor_pagination_and_never_exposes_the_api_key(): void
    {
        Http::fake(['api.openwebninja.com/*' => Http::response($this->payload('cursor-next'))]);
        $this->get('/jobs?search=developer&arrangement=remote&type=full_time')->assertOk()->assertDontSee('test-jsearch-secret')
            ->assertInertia(fn (Assert $page) => $page->where('liveSearch', true)->has('jobs.data', 1)
                ->where('nextSearchUrl', fn ($url) => str_contains($url, 'cursor=cursor-next')));
        $this->get('/jobs?search=developer&cursor=cursor-next')->assertOk();
        Http::assertSent(fn ($request) => ($request['cursor'] ?? null) === 'cursor-next');
        Http::assertSent(fn ($request) => ($request['work_from_home'] ?? null) === 'true' && ($request['employment_types'] ?? null) === 'FULLTIME');
    }

    public function test_non_philippine_jobs_unsafe_links_and_invalid_data_are_not_imported(): void
    {
        $payload = $this->payload();
        $valid = $payload['data']['jobs'][0];
        $payload['data']['jobs'] = [
            [...$valid, 'job_country' => 'US'], [...$valid, 'job_apply_link' => 'javascript:alert(1)'],
            [...$valid, 'employer_name' => ['invalid']], [...$valid, 'job_title' => null],
        ];
        Http::fake(['api.openwebninja.com/*' => Http::response($payload)]);
        $this->assertCount(0, app(JSearch::class)->search([])['jobs']);
        $this->assertDatabaseCount('job_postings', 0);
    }

    public function test_upstream_failures_produce_a_safe_message_and_allow_cached_listing_browsing(): void
    {
        Http::fake(['api.openwebninja.com/*' => Http::response(['message' => 'private-upstream-error-dontshow'], 429)]);
        $this->get('/jobs')->assertOk()->assertDontSee('private-upstream-error-dontshow')->assertDontSee('test-jsearch-secret')
            ->assertInertia(fn (Assert $page) => $page->where('liveSearch', false)->where('sourceError', fn ($message) => str_contains($message, 'allowance')));
        $this->get('/jobs?cached=1')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_monthly_request_cap_survives_clearing_the_cache(): void
    {
        config(['services.jsearch.monthly_limit' => 1]);
        Http::fake(['api.openwebninja.com/*' => Http::response($this->payload())]);
        app(JSearch::class)->search(['search' => 'first']);
        Cache::flush();
        try {
            app(JSearch::class)->search(['search' => 'second']);
            $this->fail('The monthly budget was not enforced.');
        } catch (JSearchUnavailable $exception) {
            $this->assertStringContainsString('monthly', $exception->getMessage());
        }
        Http::assertSentCount(1);
    }

    public function test_apply_link_does_not_claim_submission_and_tracker_updates_are_private(): void
    {
        Http::fake(['api.openwebninja.com/*' => Http::response($this->payload())]);
        $job = app(JSearch::class)->search([])['jobs']->sole();
        $this->get('/jobs/'.$job->id.'/out')->assertRedirect('https://example.com/role');
        $this->assertDatabaseCount('job_applications', 0);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/jobs/'.$job->id.'/out')->assertRedirect('https://example.com/role');
        $record = $user->jobApplications()->sole();
        $this->assertSame('opened', $record->status);
        $this->assertNull($record->applied_at);
        $this->post('/jobs/'.$job->id.'/apply')->assertRedirect('/applications');
        $this->assertSame('applied', $record->fresh()->status);
        $this->assertNull($record->fresh()->resume_path);
        $this->put('/applications/'.$record->id, ['status' => 'interview', 'notes' => 'Private preparation'])->assertRedirect();
        $this->assertDatabaseHas('job_applications', ['id' => $record->id, 'status' => 'interview', 'self_reported' => true]);
        $this->actingAs(User::factory()->create())->put('/applications/'.$record->id, ['status' => 'accepted'])->assertNotFound();
        $this->get('/applications')->assertInertia(fn (Assert $page) => $page->has('applications.data', 0));
    }

    public function test_manual_job_company_and_job_skill_writes_are_retired(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['job-postings', 'companies', 'job-skills'] as $resource) {
            $this->postJson('/backend/'.$resource, [])->assertStatus(405);
            $this->patchJson('/backend/'.$resource.'/1', [])->assertStatus(405);
            $this->getJson('/backend/'.$resource)->assertOk();
        }
    }

    /** @return array<string, mixed> */
    private function payload(?string $cursor = null): array
    {
        return ['status' => 'OK', 'data' => ['cursor' => $cursor, 'jobs' => [[
            'job_id' => 'external-role-1', 'job_title' => 'PHP developer', 'job_country' => 'PH', 'employer_name' => 'Example PH Studio',
            'job_description' => 'Build PHP and Laravel applications.', 'job_apply_link' => 'https://example.com/role', 'job_is_remote' => false,
        ]]]];
    }
}
