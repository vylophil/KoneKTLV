import { Link, useForm } from '@inertiajs/react';
import {
    EmptyState,
    JobRow,
    PageTitle,
    Pagination,
    Portal,
    type Job,
    type Paginated,
} from '@/components/portal';

type Match = {
    job: Job;
    score: number;
    terms: string[];
    computedAt: string | null;
};
export default function Matches({
    matches,
    hasResume,
}: {
    matches: Paginated<Match>;
    hasResume: boolean;
}) {
    const form = useForm({});
    const error = (form.errors as Record<string, string>).resume;
    return (
        <Portal title="Resume matches">
            <PageTitle
                title="Matches for your experience"
                description="Roles ranked by shared keywords in your resume and the job description. Scores are a starting point for your search."
                action={
                    hasResume && (
                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                form.post('/matches/refresh');
                            }}
                        >
                            <button
                                type="submit"
                                className="kt-button kt-button-secondary"
                                disabled={form.processing}
                            >
                                {form.processing
                                    ? 'Refreshing…'
                                    : 'Refresh matches'}
                            </button>
                        </form>
                    )
                }
            />
            {error && (
                <p className="kt-error mb-6" role="alert">
                    {error}
                </p>
            )}
            {!hasResume ? (
                <EmptyState
                    title="Start with your resume"
                    description="Upload a text-based PDF in your profile to find roles with relevant keywords."
                    href="/profile?section=resume"
                    action="Upload your resume"
                />
            ) : matches.data.length === 0 ? (
                <EmptyState
                    title="No keyword matches yet"
                    description="Refresh to check the latest roles, or browse jobs directly. A missing keyword match doesn’t mean you’re unqualified."
                    href="/jobs"
                    action="Browse all jobs"
                />
            ) : (
                <>
                    <p className="kt-meta mb-5">
                        {matches.total} matching roles ·{' '}
                        <Link
                            className="underline"
                            href="/profile?section=resume"
                        >
                            Manage resume
                        </Link>
                    </p>
                    {matches.data.map((match) => (
                        <div key={match.job.id}>
                            <JobRow job={match.job}>
                                <span className="kt-status">
                                    {Math.round(match.score)}% keyword overlap
                                </span>
                                {match.computedAt && (
                                    <time
                                        className="kt-meta"
                                        dateTime={match.computedAt}
                                    >
                                        Updated {match.computedAt.slice(0, 10)}
                                    </time>
                                )}
                            </JobRow>
                            <details className="mb-6">
                                <summary className="kt-text-link">
                                    Why this role matched
                                </summary>
                                <p className="kt-prose mt-2">
                                    {match.terms.length
                                        ? `Shared terms: ${match.terms.join(', ')}.`
                                        : 'Refresh matches to see the shared keywords for this role.'}{' '}
                                    Scores do not evaluate your suitability or
                                    predict a hiring outcome.
                                </p>
                            </details>
                        </div>
                    ))}
                    <Pagination page={matches} />
                </>
            )}
        </Portal>
    );
}
