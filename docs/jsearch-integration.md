# JSearch Philippine integration

## Configuration

```dotenv
JSEARCH_API_KEY=your_private_key
JSEARCH_MONTHLY_LIMIT=180
```

Keep the key in the ignored `.env` or deployed secret configuration. Never put it
in a `VITE_` variable, source code, browser request, or repository. JSearch enables
automatically when a key exists; `JSEARCH_ENABLED=false` explicitly disables it.
Run `php artisan migrate` and rebuild frontend assets after pulling this change.

The integration calls the fixed HTTPS `/jsearch/search-v2` endpoint with an
`x-api-key` header, `country=ph`, `language=en`, and `num_pages=1`. It sends search
keywords and location only, never resumes or private profile data. Authentication
headers are not forwarded through upstream redirects. There are bounded timeouts,
no automatic retries that could spend additional credits, and a short cooldown
after upstream failures.

## Requests, caching, and pagination

Each provider page contains up to 10 jobs. The provider's `data.cursor` supplies
the next page; the app does not fabricate an overall result count. Identical
search parameters share a six-hour cache across users. Concurrent identical
searches use a cache lock. Cache hits do not make another provider request.

An atomic database counter in `job_api_usage` limits outbound requests per calendar
month, including failed attempts. Clearing caches does not reset that counter.
The default 180 leaves headroom below the published 200-request free allowance,
but requests from other programs and the initial manual connectivity probe are
outside this counter. Check the provider dashboard for actual account-wide usage.

There is no additional salary or company-review API subscription. Salary estimates
are not invented. If the listing provides salary text, retain it; numeric salary
fields preserve their supplied pay period and do not assume a currency. Missing
salary is shown as “Salary not disclosed.” A false remote flag does not establish
that a position is on-site; unknown arrangement stays unknown.

On timeout, rate limiting, exhausted app allowance, or malformed API output, show
a safe message and provide `/jobs?cached=1`. Error bodies and secret headers are
never included in page props. Provider search does not guarantee that an external
job will remain open; last-retrieved dates are shown and the original site handles
the final application.

## Data identity and ownership migration

`source_key` is a SHA-256 fingerprint of provider, country, and the external job ID.
It has a unique index; repeated or concurrent discoveries update the same local
listing. Imported jobs carry a source ID, publisher, company-name metadata, source
posting time, retrieval time, salary period/text, and the original application URL.
Country must be `PH`; malformed records and non-HTTP(S) application URLs are skipped.

Company and employer foreign keys become nullable. Imported jobs have neither
owner, so a fake employer login is unnecessary. Job type can be unknown. Existing
companies, jobs, saved records, and historical application data are preserved.
The legacy account business-role column remains for compatibility; it is not used
to authorize source posting, application access, or the job-seeker UI. Spatie
Admin/Manager/User access remains unchanged. Manual company, job, and job-skill
write routes have been retired; their read routes remain for legacy consumers.

Application status expands to support `opened` and `applied`; private notes,
self-reporting metadata, and an opened timestamp support the personal tracker.
Opening an external link leaves `applied_at` empty. The seeker confirms application
submission and maintains later progress. No employer gets access to these records.

Back up the database before migrating an existing environment. This workspace has
an ignored pre-integration SQLite copy under `.tools/`. Rollback retains nullable
ownership and expanded status types to avoid rejecting imported/history rows;
restoring the prior app and exact prior schema requires restoring a matching backup.
Do not delete imported jobs merely because one result page no longer includes them:
saved jobs and tracker history reference those stable local IDs.

## Tests and live checks

PHPUnit sets `JSEARCH_ENABLED=false` so the general suite never spends real credits.
Integration tests enable it with a dummy key and `Http::fake`, and reject stray HTTP.
Coverage includes cache/budget behavior, cursor requests, missing/invalid fields,
country and URL validation, absent employer ownership, privacy, retired writes,
and the distinction between link opening and self-reported submission.

The initial authenticated provider probe returned HTTP 200 and ten Philippine
software-developer listings. A default Philippine search then imported ten listings
with null employer/company owners. Only public listing summaries were printed.

## Provider references

- [JSearch documentation](https://www.openwebninja.com/api/jsearch/docs)
- [Published OpenAPI](https://openwebninja.s3.us-east-1.amazonaws.com/portal/openapi/jsearch.yaml)
- [Provider usage terms](https://www.openwebninja.com/terms)
