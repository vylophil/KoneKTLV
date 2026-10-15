# Job-seeker web flow

## Task ownership

The public home introduces the service. It does not contain resume-management or
matching forms. Signed-in visitors to `/` go to `/dashboard`. Legacy homepage
search URLs redirect to `/jobs` with their search term.

| Route                | Responsibility                                               |
| -------------------- | ------------------------------------------------------------ |
| `/jobs`              | Public, server-filtered and paginated discovery              |
| `/jobs/{job}`        | Full role information, Save, and application form            |
| `/dashboard`         | Compact member overview and actionable completion checklist  |
| `/profile?section=…` | Personal details, experience, education, skills, resume      |
| `/matches`           | Paginated keyword matches, explanations, timestamps, refresh |
| `/saved-jobs`        | Member's saved roles and removal controls                    |
| `/applications`      | Member's submissions, status, letter, attached resume        |
| `/settings`          | Account information and protected deletion controls          |

The shared `Portal` component owns navigation, page chrome, success announcements,
and responsive menu behavior. `JobView` owns consistent public job presentation.
The existing neutral/green/coral identity is retained with semantic CSS values,
visible focus, sufficient text contrast, and compact task-focused layouts.

## Discovery

Search, work arrangement, job type, sort, and page belong to the URL. Filters and
ordering run before pagination, with deterministic ID tie-breaks. Role links carry
the query back to the results page. Active, unexpired jobs appear in discovery.
Closed saved/applied roles remain accessible to their owner for reference.

Guest application links first visit an authenticated GET destination, allowing
Fortify's intended URL to return the visitor to the selected role after sign-in.
Applications and saved-job mutations enforce active job-seeker accounts.

## Applications and private resumes

The web application action validates the cover letter, open/deadline state, prior
application, account role, and presence of a private resume. It copies the resume
into private `applications/{user-id}/` storage and records that path on the
application. Replacing the profile resume leaves the submitted copy unchanged.
Duplicate submission errors are handled both before insertion and at the unique
database constraint. A failed insert removes its newly copied file.

`resume_path` is hidden from ordinary model serialization and is not accepted by
the existing backend resource validators. The private download action authorizes
the applicant or the job's employer and verifies file existence. Account deletion
cleans the user's application-resume directory as well as their profile resume.

Older backend-created applications without private snapshots continue to work and
simply omit the private attachment link. Existing backend `resume_url` behavior
is unchanged. File retention for employer-side job deletion is outside this flow;
application snapshots should be included in any future storage-retention policy.

## Profiles and matching

Profile forms reuse existing resource controllers' validated, ownership-scoped
operations, converting successful JSON operations to web redirects. Career entry
editing/deletion and skill removal cannot address another user's records. Resume
upload retains the existing PDF/memory/size safeguards and redirects to Matches.

Matching stores shared terms next to the score. Refresh re-reads the private PDF
and recomputes against current open roles, including newly posted jobs. Profile
skills are presented as professional information rather than falsely claiming to
affect the resume-text matcher. Matching does not evaluate suitability for hiring.

## Authentication and verification

Fortify provides login, registration, reset-link, and reset-password views. The
default signed-in destination is `/dashboard`; intended protected URLs take
precedence. Email delivery depends on the environment's configured mail transport.

`JobSeekerFlowTest` covers route separation, filters beyond the first page,
pagination, intended login destinations, idempotent saves, private application
snapshots, duplicate prevention, role checks, profile validation, career and skill
ownership, and the full password-reset notification/token flow.
`ResumeUploadTest` covers replacement and refresh against newly created roles.

The local live HTTP smoke test additionally exercises registration, 100% profile
completion, PDF upload, matching/refresh, save, apply, download, duplicate handling,
and cleanup with a temporary account. Browser visual/keyboard testing remains a
separate verification step when a browser connection is available.
