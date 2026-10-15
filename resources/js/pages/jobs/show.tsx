import { Link, useForm, usePage } from '@inertiajs/react';
import { PageTitle, Portal, type Job } from '@/components/portal';

export default function JobDetail({
    job,
    isSaved,
    application,
    hasResume,
    backUrl,
}: {
    job: Job;
    isSaved: boolean;
    application: { id: number; status: string } | null;
    hasResume: boolean;
    backUrl: string;
}) {
    const { auth } = usePage<{ auth: { user: { role: string } | null } }>()
        .props;
    const apply = useForm({ cover_letter: '' });
    const save = useForm({});
    const applicationError = (apply.errors as Record<string, string>)
        .application;
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
                        <p className="kt-prose">{job.description}</p>
                    </section>
                    <section className="kt-section">
                        <h2>What you’ll do</h2>
                        <p className="kt-prose">
                            {job.responsibilities ||
                                'The employer has not added responsibilities for this role yet.'}
                        </p>
                    </section>
                    <section className="kt-section">
                        <h2>What you’ll bring</h2>
                        <p className="kt-prose">
                            {job.requirements ||
                                'The employer has not added specific requirements yet.'}
                        </p>
                    </section>
                    {job.companyDescription && (
                        <section className="kt-section">
                            <h2>About {job.company}</h2>
                            <p className="kt-prose">{job.companyDescription}</p>
                        </section>
                    )}
                    {auth.user?.role === 'job_seeker' &&
                        !application &&
                        job.isOpen && (
                            <section className="kt-section" id="apply">
                                <h2>Apply for this role</h2>
                                {hasResume ? (
                                    <form
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            apply.post(`/jobs/${job.id}/apply`);
                                        }}
                                    >
                                        <p className="mb-5">
                                            A private copy of your current
                                            resume will be attached to this
                                            application.
                                        </p>
                                        <div className="kt-field">
                                            <label htmlFor="cover-letter">
                                                Cover letter{' '}
                                                <span className="font-normal">
                                                    (optional)
                                                </span>
                                            </label>
                                            <textarea
                                                id="cover-letter"
                                                maxLength={10000}
                                                value={apply.data.cover_letter}
                                                aria-describedby={
                                                    apply.errors.cover_letter
                                                        ? 'cover-letter-error'
                                                        : undefined
                                                }
                                                onChange={(event) =>
                                                    apply.setData(
                                                        'cover_letter',
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                            {apply.errors.cover_letter && (
                                                <p
                                                    id="cover-letter-error"
                                                    className="kt-error"
                                                    role="alert"
                                                >
                                                    {apply.errors.cover_letter}
                                                </p>
                                            )}
                                        </div>
                                        {applicationError && (
                                            <p
                                                role="alert"
                                                className="kt-error"
                                            >
                                                {applicationError}
                                            </p>
                                        )}
                                        <div className="kt-form-actions">
                                            <button
                                                type="submit"
                                                className="kt-button"
                                                disabled={apply.processing}
                                            >
                                                {apply.processing
                                                    ? 'Sending application…'
                                                    : 'Send application'}
                                            </button>
                                            <Link
                                                className="kt-text-link"
                                                href="/profile?section=resume"
                                            >
                                                Review your resume
                                            </Link>
                                        </div>
                                    </form>
                                ) : (
                                    <>
                                        <p>
                                            Upload your resume before applying.
                                            You can return to this role when
                                            it’s ready.
                                        </p>
                                        <Link
                                            className="kt-text-link"
                                            href="/profile?section=resume"
                                        >
                                            Upload a resume
                                        </Link>
                                    </>
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
                            <dt>Salary</dt>
                            <dd>
                                {job.salaryMin || job.salaryMax
                                    ? `${job.currency ?? 'PHP'} ${job.salaryMin ?? '—'} – ${job.salaryMax ?? '—'}`
                                    : 'Not specified'}
                            </dd>
                        </div>
                        {job.deadline && (
                            <div>
                                <dt>Apply by</dt>
                                <dd>{job.deadline}</dd>
                            </div>
                        )}
                    </dl>
                    {!job.isOpen ? (
                        <p>This role is no longer accepting applications.</p>
                    ) : application ? (
                        <>
                            <p className="kt-status">{application.status}</p>
                            <Link href="/applications" className="kt-button">
                                View your application
                            </Link>
                        </>
                    ) : auth.user?.role === 'job_seeker' ? (
                        <a href="#apply" className="kt-button">
                            {hasResume
                                ? 'Apply for this role'
                                : 'Get ready to apply'}
                        </a>
                    ) : !auth.user ? (
                        <>
                            <p>
                                Sign in to save this role or send an
                                application.
                            </p>
                            <Link
                                href={`/jobs/${job.id}/apply`}
                                method="get"
                                className="kt-button"
                            >
                                Sign in to apply
                            </Link>
                        </>
                    ) : (
                        <p>
                            Applications are available to job-seeker accounts.
                        </p>
                    )}
                    {auth.user?.role === 'job_seeker' &&
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
                                    type="submit"
                                    disabled={save.processing}
                                >
                                    {isSaved
                                        ? 'Remove from saved jobs'
                                        : 'Save job'}
                                </button>
                            </form>
                        )}
                </aside>
            </div>
        </Portal>
    );
}
