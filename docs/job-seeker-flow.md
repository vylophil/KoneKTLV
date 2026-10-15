# Job-seeker web flow

KoneKT has one public account type: job seeker. Admin, Manager, and User remain
internal Spatie permissions for maintaining the platform. An employer name in a
job listing is metadata; it does not represent a KoneKT login.

| Route                | Responsibility                                                               |
| -------------------- | ---------------------------------------------------------------------------- |
| `/`                  | Public introduction and previews of already-retrieved jobs                   |
| `/jobs`              | Philippine JSearch queries, filters, and cursor pagination                   |
| `/jobs/{job}`        | Source details, Save, external application link, tracker entry               |
| `/jobs/{job}/out`    | Redirect to the stored application URL; record an authenticated user's visit |
| `/dashboard`         | Member overview and profile-completion tasks                                 |
| `/profile?section=…` | Personal details, career history, skills, private resume                     |
| `/matches`           | Local keyword matching against retrieved listings and refresh                |
| `/saved-jobs`        | Private shortlist and removal controls                                       |
| `/applications`      | Private, user-maintained application tracker and notes                       |
| `/settings`          | Account information and account deletion                                     |

## Discovery and source data

The shared Portal, Brand, JobSearch, and JobRow components retain the current UI.
The source key is never shared with React. JSearch requires no employer or company
owner when importing jobs. JobView supports optional legacy company relationships
and imported company-name metadata.

Live search returns one provider page at a time. Query, city/region, remote-only
filter, employment type, and cursor are retained in the URL. Title sorting is
explicitly limited to the current provider page; no total result count is invented.
Previously retrieved listings can also be browsed with database pagination when
live search is unavailable. Unknown salary, work arrangement, and requirements
remain unknown. Existing saved jobs and tracker entries are not removed because
a later search fails or omits a previously returned listing.

## Apply externally and track personally

The external link opens the original listing in a new tab, with no resume or
profile data attached by KoneKT. Guests can follow it without an account. For an
active signed-in account, opening it creates or updates an owner-scoped tracker
record with status `opened`. That event does not claim an application was sent.

After submitting on the source website, the seeker can mark `applied`. Tracker
statuses and notes are self-reported; employer review, offers, or rejections are
not automatically verified or synchronized. Repeated clicks and confirmations
reuse the user/job record. Other accounts, including legacy job owners, cannot
read or change the seeker's tracker or download archived private resumes.

Historical cover letters and resume copies remain available to their owner.
No new private application copies are created by the tracker. Profile resumes
stay private and are used for local keyword matching. Account deletion cleans
the account's existing private resume files and historical application files.

## Profiles, matching, and staff

Profile editing retains existing validators and ownership checks. Matching ranks
retrieved jobs against selectable PDF text; it does not search the entire external
market or assess hiring suitability. A seeker can search relevant roles, then
refresh Matches to include the newly retrieved listings.

Staff skill maintenance and role-assignment pages retain their existing server
permissions. The legacy `users.role` column and nullable company/employer foreign
keys are compatibility data, not a second public account type or an authorization
grant. Manual listing/company/job-skill writes return 405.

## Verification

`JSearchTest` covers Philippine requests, safe source normalization, unknown data,
credential privacy, deduplication, cursor pagination, request caching, durable
monthly limits, safe upstream failures, external redirects, and private tracking.
Existing authentication, profile, PDF, matching, staff permission, and ownership
tests continue to apply. Automated tests disable live JSearch by default and use
fake HTTP responses with a dummy key when testing the integration.

See [JSearch integration](jsearch-integration.md) for configuration and migration.
