import { Link, useForm, usePage } from '@inertiajs/react';
import { PageTitle, Portal, type Job } from '@/components/portal';

export default function JobDetail({
    job,
    isSaved,
    application,
    backUrl,
}: {
    job: Job;
    isSaved: boolean;
    application: { id: number; status: string } | null;
    backUrl: string;
}) {
    const { auth } = usePage<{ auth: { user: { name: string } | null } }>()
        .props;
    const track = useForm({});
    const save = useForm({});
    const salary =
        job.salaryText ||
        (job.salaryMin !== null || job.salaryMax !== null
            ? `${job.currency ?? ''} ${job.salaryMin ?? '—'} – ${job.salaryMax ?? '—'}${job.salaryPeriod ? ` / ${job.salaryPeriod.toLowerCase()}` : ' (pay period not supplied)'}`.trim()
            : 'Salary not disclosed');
    return (
        <Portal title={job.title}>
            <Link href={backUrl} className="kt-text-link">
                ← Back to results
            </Link>
            <div className="mt-5">
                <PageTitle
                    title={job.title}
                    description={`${job.company} · ${job.location}`}
                />
            </div>
            <div className="kt-columns">
                <div>
                    <section className="kt-section">
                        <h2>About this role</h2>
                        <p className="kt-prose">
                            {job.description ||
                                'A description was not supplied. Open the original listing for complete details.'}
                        </p>
                    </section>
                    {job.responsibilities && (
                        <section className="kt-section">
                            <h2>What you’ll do</h2>
                            <p className="kt-prose">{job.responsibilities}</p>
                        </section>
                    )}
                    {job.requirements && (
                        <section className="kt-section">
                            <h2>What you’ll bring</h2>
                            <p className="kt-prose">{job.requirements}</p>
                        </section>
                    )}
                    {job.companyDescription && (
                        <section className="kt-section">
                            <h2>About {job.company}</h2>
                            <p className="kt-prose">{job.companyDescription}</p>
                        </section>
                    )}
                    <section className="kt-section" id="apply">
                        <h2>Apply on the original site</h2>
                        <p>
                            Complete your application on the listing’s source
                            website. Opening the link does not submit an
                            application, and KoneKT does not send your resume.
                        </p>
                        {job.applyUrl && job.isOpen ? (
                            <a
                                className="kt-button mt-5"
                                href={`/jobs/${job.id}/out`}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Apply on source site{' '}
                                <span className="sr-only">
                                    (opens a new tab)
                                </span>
                            </a>
                        ) : (
                            <p className="mt-4">
                                An active application link is unavailable for
                                this archived listing.{' '}
                                <Link href="/jobs" className="kt-text-link">
                                    Find current roles
                                </Link>
                            </p>
                        )}
                    </section>
                    {auth.user && (
                        <section className="kt-section">
                            <h2>Your application tracker</h2>
                            <p>
                                After applying on the source site, mark it here
                                to keep a personal record. Updates are entered
                                by you.
                            </p>
                            {application && application.status !== 'opened' ? (
                                <Link
                                    className="kt-text-link mt-3"
                                    href="/applications"
                                >
                                    View and update your progress
                                </Link>
                            ) : (
                                <form
                                    className="mt-5"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        track.post(`/jobs/${job.id}/apply`);
                                    }}
                                >
                                    <button
                                        className="kt-button kt-button-secondary"
                                        disabled={track.processing}
                                    >
                                        I’ve applied — add to tracker
                                    </button>
                                </form>
                            )}
                        </section>
                    )}
                </div>
                <aside className="kt-sidebar">
                    <h2>Role at a glance</h2>
                    <dl>
                        <div>
                            <dt>Work arrangement</dt>
                            <dd>{job.workArrangement}</dd>
                        </div>
                        <div>
                            <dt>Job type</dt>
                            <dd>{job.workType}</dd>
                        </div>
                        <div>
                            <dt>Advertised salary</dt>
                            <dd>{salary}</dd>
                        </div>
                        {job.publisher && (
                            <div>
                                <dt>Listing publisher</dt>
                                <dd>{job.publisher}</dd>
                            </div>
                        )}
                        {job.fetchedAt && (
                            <div>
                                <dt>Last retrieved</dt>
                                <dd>
                                    <time dateTime={job.fetchedAt}>
                                        {new Date(job.fetchedAt).toLocaleString(
                                            'en-PH',
                                            {
                                                timeZone: 'Asia/Manila',
                                                dateStyle: 'medium',
                                                timeStyle: 'short',
                                            },
                                        )}
                                    </time>
                                </dd>
                            </div>
                        )}
                        {job.deadline && (
                            <div>
                                <dt>Apply by</dt>
                                <dd>{job.deadline}</dd>
                            </div>
                        )}
                    </dl>
                    {application && (
                        <p className="kt-status">
                            {application.status === 'opened'
                                ? 'Application link opened'
                                : application.status === 'pending'
                                  ? 'Legacy application record'
                                  : application.status}
                        </p>
                    )}
                    {auth.user ? (
                        (job.isOpen || isSaved) && (
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    if (isSaved)
                                        save.delete(`/jobs/${job.id}/save`, {
                                            preserveScroll: true,
                                        });
                                    else
                                        save.post(`/jobs/${job.id}/save`, {
                                            preserveScroll: true,
                                        });
                                }}
                            >
                                <button
                                    className="kt-button kt-button-secondary"
                                    disabled={save.processing}
                                >
                                    {isSaved
                                        ? 'Remove from saved jobs'
                                        : 'Save job'}
                                </button>
                            </form>
                        )
                    ) : (
                        <>
                            <p>
                                Sign in to save roles and track your own
                                application progress.
                            </p>
                            <Link className="kt-button" href="/saved-jobs">
                                Sign in to save jobs
                            </Link>
                        </>
                    )}
                </aside>
            </div>
        </Portal>
    );
}
