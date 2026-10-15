# KoneKT job-seeker flow audit

Audited October 15, 2026 (UTC+8), commit `90edbc6`.

Status: historical audit of the pre-fix app. The follow-up implementation is on
`fix/job-seeker-flow`; see `docs/job-seeker-flow.md` for the current route and
behavior contract. Findings below describe the audited commit, not the updated UI.

## Verdict

The active UI mixes discovery, marketing, resume management, and recommendations on the homepage. The dashboard adds a second long page, but offers very few actions. This supports the user's concern about page overload. More importantly, the primary job-seeker journey ends before applying, saving a role, or completing a profile.

The recommended first change is to give tasks clear destinations and working next steps. Visual cleanup should follow those decisions. Creating more routes by itself will not complete the missing workflows.

## Method and limits

- Reviewed active Laravel routes, controllers, React page compositions, job browser, authentication screens, styles, and resume matcher using CodeGraph and direct reads for files or sections it did not return.
- Exercised the running app at `http://127.0.0.1:8000` using the supplied local test account. Recorded 24 HTTP checks, plus targeted search and password-recovery checks.
- The browser connection inventory returned no apps or browsers. No rendered desktop/mobile inspection, pointer interaction, keyboard walkthrough, screenshot comparison, or screen-reader testing was possible. Layout overflow and perceived visual density are therefore unverified visually. Source observations and the user's report of clutter are identified separately.
- Earlier verification on this same commit passed all 87 PHP tests, TypeScript checking, and the production build. Passing those checks does not establish a complete user journey.
- Impeccable's mechanical detector returned `[]` for `resources/js/pages` and `resources/js/components/jobs`. That does not validate navigation or feature completeness.
- No application source was changed. No account was created or deleted, no application was submitted, and no existing resume was replaced. Registration submission and successful upload/replacement were covered by existing automated tests rather than live account mutations.

## Current journey

| Step                     | Current destination and result                                                                        | Assessment                                                                                  |
| ------------------------ | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Arrive                   | `/` renders marketing, resume upload, optional matches, job list, and employer roadmap content        | Four different tasks share one page                                                         |
| Browse                   | `/#jobs`, text search, arrangement/type filters, sorting                                              | Search works; discovery is limited and transient                                            |
| Inspect a job            | Inline expansion shows requirements and responsibilities                                              | No addressable detail page, full description, Save, or Apply action                         |
| Sign in                  | `/login`; invalid credentials return 422; supplied credentials succeed                                | Working core authentication                                                                 |
| Register                 | `/register` renders                                                                                   | Form submission covered by automated tests; not exercised against the live account database |
| Dashboard                | `/dashboard` renders profile, completion, stats, career history, resume, skills, and account deletion | Mostly a read-only overview                                                                 |
| Complete profile         | Dashboard prompts for missing information                                                             | No editing controls for profile, experience, education, or skills                           |
| Upload/replace resume    | Dashboard links to `/#resume-upload`; upload redirects to `/#matches`                                 | Repeated trips to the homepage                                                              |
| Review matches           | Homepage match summaries with keyword percentages                                                     | No detail/Save/Apply next step; no dashboard link to matches                                |
| Applications/saved roles | Numeric dashboard metrics                                                                             | No lists or navigation from those metrics                                                   |
| Networking               | Homepage promises connections; uploaded chat files exist outside active entry points                  | `/networking-chat` returns 404                                                              |
| Recover password         | No recovery link on sign-in; direct guest request to `/forgot-password` returns 500                   | Recovery flow is incomplete                                                                 |
| Sign out                 | POST `/logout` returns 204; subsequent dashboard visit redirects to sign-in                           | Working                                                                                     |

The test account currently has 0% completion, no profile, no resume, and zero application/saved/match counts. A missing-resume download correctly returns 404; this is expected for this account and is not reported as a bug.

## Prioritized findings

### F1 — P0: Browsing cannot lead to an application

**Category:** Implementation integrity / task completion.

**Evidence:** `resources/js/components/jobs/job-browser.tsx:241-299` implements job cards with an inline details toggle as the only action. `resources/js/pages/jobs/index.tsx:249-275` renders matched jobs without links or actions. Backend application resources exist, but are not connected to these screens.

**Impact:** A seeker can discover a role but cannot apply or save it through the UI. The dashboard's application and saved-job counters provide no usable continuation. This is an unimplemented frontend flow, rather than evidence that the backend application endpoint is broken.

**Recommendation:** Add an addressable job detail view with full description, requirements, employer information, and Apply/Save actions. Complete submission, confirmation, duplicate-application handling, and application tracking. Connect dashboard metrics to their corresponding lists.

### F2 — P0: Profile completion asks users to perform unavailable actions

**Category:** Implementation integrity / onboarding.

**Evidence:** `resources/js/pages/dashboard/index.tsx:127-195`, `197-267`, and `300-328` show completion advice and missing-data messages, but no profile, experience, education, or skills editors. `app/Http/Controllers/DashboardController.php:30-40` counts nine completion criteria, most of which cannot be filled through this UI. Live account state confirms 0% completion with no profile.

**Impact:** A new user sees a task they cannot complete. Uploading a resume can satisfy one criterion, but cannot complete the rest.

**Recommendation:** Provide an editable profile destination with section-level Add/Edit actions. Link each incomplete checklist item directly to the appropriate editor and use specific empty-state actions such as “Add experience.”

### F3 — P1: Returning users repeatedly traverse the marketing homepage

**Category:** Information architecture / implementation integrity.

**Evidence:** `resources/js/pages/jobs/index.tsx:59-108` renders the same hero regardless of authentication. Resume upload follows at `110-219`, optional matches at `221-292`, and the job browser at `294`. Dashboard links lead to `/#jobs` and `/#resume-upload` at `resources/js/pages/dashboard/index.tsx:77`, `121`, and `278`. `config/fortify.php` sets the default post-authentication home to `/`; an intended protected destination can override it.

**Impact:** Marketing copy remains in the task path for established users, while resume actions and recommendations are scattered between the dashboard and homepage. This is the main source-backed explanation for the user's report of excessive reliance on one page. Exact scroll burden was not visually measured.

**Recommendation:** Keep `/` focused on public introduction and a search entry point. Give authenticated users a shared navigation shell and task-focused destinations for jobs, matches, profile, applications, and saved jobs. Put account deletion in Settings. Dashboard should summarize these areas and expose their next actions.

### F4 — P1: Discovery only filters a truncated, temporary set

**Category:** Implementation integrity / search continuity.

**Evidence:** `app/Http/Controllers/HomeController.php:19-33` limits the search result to 12 jobs before arrangement/type filters and title sorting are applied. `resources/js/components/jobs/job-browser.tsx:38-52` applies those controls to that array with local React state. The search form at `76-97` uses a normal GET to `/`, without an anchor or filter query parameters. There is no pagination in the active job browser.

**Impact:** Remote or contract roles outside the latest 12 results cannot be reached by those controls. “Title A-Z” only sorts the loaded subset. Search submission resets the local controls, and a shared URL does not reproduce their state. Returning from the dashboard similarly discards the discovery context.

**Recommendation:** Make `/jobs` own a URL-backed query for search, arrangement, type, sort, and page. Apply those controls before pagination on the server. Preserve the query when opening a role and returning to results. Report filtered total counts separately from the current page size.

### F5 — P1: Password recovery is unavailable

**Category:** Implementation integrity / error recovery.

**Evidence:** Live guest GET `/forgot-password` returns 500: `Target [Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse] is not instantiable.` `app/Providers/FortifyServiceProvider.php:33-34` registers login and registration views, but no recovery view. `resources/js/pages/auth/login.tsx` provides no recovery link despite password reset being enabled in Fortify configuration.

**Impact:** A user who forgets a password cannot recover access through the current interface.

**Recommendation:** Complete the reset-link and reset-password screens and their Fortify view responses, then add a recovery link to sign-in. Verify delivery configuration and success/error states separately from page rendering.

### F6 — P1: Some text colors fail normal-text contrast

**Category:** Accessibility.

**Evidence:** The white text on the coral Explore opportunities button (`resources/js/pages/jobs/index.tsx:71-75`) has a computed contrast ratio of **3.51:1**. Muted date text `#718078` on `#f5f6f0` (`resources/js/pages/dashboard/index.tsx:215,251`) is **3.82:1**. Both are normal-size text; WCAG 2.x SC 1.4.3 requires 4.5:1 for normal text. These ratios are calculated from the literal source colors, not screenshots.

**Impact:** Primary-action and career-date text is harder to read for users with low vision or in poor viewing conditions.

**Recommendation:** Define semantic accent/button and muted-text colors that meet 4.5:1 in these combinations, preserving the existing green/coral identity. The main body color `#52635a` on the warm background already reaches about 5.87:1.

### F7 — P1: Networking promises exceed the active app

**Category:** Implementation integrity / expectations.

**Evidence:** Homepage copy at `resources/js/pages/jobs/index.tsx:67-69,85-103` promotes connections and building a network. Live authenticated `/networking-chat` returns 404. `bootstrap/app.php` loads `routes/web.php`; the new chat route is in root `web.php`, its Blade file is in root `chat/`, and the uploaded dashboard is in root `dashboard/`. React's page resolver loads `resources/js/pages/**/*.tsx`, not root `dashboard/`.

**Impact:** The latest upload is present in Git but does not appear in the running application. Networking claims create expectations with no reachable destination. Employer posting is explicitly labeled roadmap content, which is clearer than the networking messaging.

**Recommendation:** Integrate the uploaded files into the real route/view/page entry points and complete their backing interactions before promoting networking. Until that work is ready, describe it as upcoming rather than presenting it as a current capability.

### F8 — P2: Resume matching has no clear refresh or explanation path

**Category:** Implementation integrity / clarity.

**Evidence:** `app/Http/Controllers/ResumeController.php:34,58` parses and refreshes matches during upload. `app/Services/ResumeJobMatcher.php:19-69` compares resume text with job terms; it does not use the seeker's listed skills. Yet `resources/js/pages/dashboard/index.tsx:325` says adding skills helps resume matches. The UI offers neither a match-refresh action nor a last-computed date or matched-term explanation. The visible match list is capped at eight (`HomeController.php:52`).

**Impact:** Users cannot tell why they received a score, how fresh it is, or how to obtain recommendations for newly added jobs without another upload. Adding listed skills does not affect this matcher as the dashboard copy implies.

**Recommendation:** Give Matches its own destination with a refresh action, last-run timestamp, and shared/missing keyword explanation. Correct the skills copy or implement the claimed connection. Keep the existing disclosure that percentages measure keyword overlap.

### F9 — P2: Responsive navigation and styling need a focused follow-up

**Category:** Responsive / theming. **Confidence:** source-confirmed styling facts; device consequences remain unverified.

**Evidence:** Both page headers use a single horizontal flex row with multiple text links, fixed gaps, and no mobile navigation branch (`jobs/index.tsx:30-54`, `dashboard/index.tsx:66-103`). Several controls specify 36px or 40px minimum heights. `resources/css/app.css` defines the font family but no semantic color tokens; page components repeat literal hex colors and separate header markup.

**Impact:** There is a credible risk of crowded navigation and small touch controls on phones, but actual overflow was not observed. Repeated local styles make it harder to keep page hierarchy and interaction states consistent.

**Recommendation:** Visually test 360px, 390px, 768px, and desktop widths before claiming a responsive defect. Add an appropriate compact mobile navigation pattern, generous targets where needed, and one shared application header. Centralize the colors and spacing decisions that govern the active surfaces. Preserve the current identity unless a redesign is separately requested.

## Provisional health score

These are implementation-review judgments, not measured usability scores or a WCAG certification. Unavailable rendered/device evidence limits confidence, especially for responsiveness and performance.

| Dimension                | Score            | Basis                                                                                                              |
| ------------------------ | ---------------- | ------------------------------------------------------------------------------------------------------------------ |
| Accessibility            | 2/4              | Useful labels, progress and expanded states; two confirmed contrast failures and limited inline-error associations |
| Performance              | 3/4              | Lazy page imports and a roughly 109 KB gzip shared JS bundle; no runtime profiling or network throttling           |
| Theming                  | 0/4              | Literal page colors; no semantic color-token layer in the active stylesheet                                        |
| Responsive               | 2/4 provisional  | Breakpoints exist for content; navigation/touch checks remain outstanding                                          |
| Implementation integrity | 1/4              | Multiple core task paths stop before a user can complete them                                                      |
| Total                    | 8/20 provisional | Major flow work needed; visual polish alone will leave the task failures intact                                    |

Severity totals: 2 P0, 5 P1, 2 P2, 0 P3. P0 here means the named user task is unavailable in the current UI, not that every page or backend endpoint is broken.

## Proposed task ownership

| Destination     | Owns                                                        |
| --------------- | ----------------------------------------------------------- |
| `/`             | Short public introduction and job-search entry point        |
| `/jobs`         | Search, persistent filters, results, pagination             |
| `/jobs/:id`     | Full role information, Save, Apply                          |
| `/dashboard`    | Short overview, recent activity, next actions               |
| `/matches`      | Resume recommendations, explanation, refresh                |
| `/profile`      | Editable details, career history, skills, resume management |
| `/applications` | Submitted applications and status                           |
| `/saved-jobs`   | Bookmarked roles                                            |
| `/settings`     | Account preferences and account deletion                    |

These can all remain within the existing Laravel/Inertia/React architecture. The flow problem does not require replacing the framework or abandoning client-side navigation. Route ownership should follow tasks; one trivial section does not need a separate page merely to increase the page count.

## Suggested execution order

1. Define task ownership and shared navigation (`$impeccable shape`).
2. Complete apply/save and profile-editing journeys, with route-level and end-to-end coverage. These require functional implementation beyond a visual skill pass.
3. Repair recovery, search continuity, and match freshness (`$impeccable harden`).
4. Remove duplicate marketing content from member tasks and make empty states actionable (`$impeccable distill`, `$impeccable onboard`, `$impeccable clarify`).
5. Verify mobile layouts and correct contrast (`$impeccable adapt`, `$impeccable colorize`).
6. Refine hierarchy, spacing, and consistency after the flows work (`$impeccable layout`, then `$impeccable polish`).

## What is already useful

- Core login, logout, protected-dashboard access, text search, and expected missing-file behavior worked in the live HTTP checks.
- Invalid credentials and missing-resume input return field-level 422 validation errors.
- Existing tests cover private resume access, upload/replacement, matching, ownership, and backend resource validation.
- The resume UI states the PDF limit, handles upload errors/progress, and describes matching as keyword overlap.
- The employer section explicitly identifies unimplemented posting as roadmap work.
- The basic warm neutral, green, and coral palette can be retained while the task structure improves.

The next visual pass should inspect the real rendered screens before making claims about exact spacing, overflow, fold position, touch behavior, or contrast after interaction states change.
