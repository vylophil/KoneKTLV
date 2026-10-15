import { useForm } from '@inertiajs/react';
import {
    EmptyState,
    JobRow,
    PageTitle,
    Pagination,
    Portal,
    type Job,
    type Paginated,
} from '@/components/portal';

type Application = {
    id: number;
    status: string;
    appliedAt: string | null;
    coverLetter: string | null;
    resumeUrl: string | null;
    notes: string | null;
    selfReported: boolean;
    job: Job;
};
function TrackedApplication({ item }: { item: Application }) {
    const initialStatus = [
        'opened',
        'applied',
        'interview',
        'offered',
        'accepted',
        'rejected',
        'withdrawn',
    ].includes(item.status)
        ? item.status
        : 'applied';
    const form = useForm({ status: initialStatus, notes: item.notes ?? '' });
    return (
        <div>
            <JobRow job={item.job}>
                <span className="kt-status">
                    {item.status === 'opened'
                        ? 'Link opened'
                        : item.status === 'pending'
                          ? 'Legacy record'
                          : item.status}
                </span>
                <p className="kt-meta">
                    {item.selfReported
                        ? 'Self-reported progress'
                        : item.status === 'opened'
                          ? 'Not confirmed as applied'
                          : 'Historical record'}
                </p>
                {item.appliedAt && (
                    <p className="kt-meta">Recorded {item.appliedAt}</p>
                )}
                {item.resumeUrl && (
                    <a className="kt-text-link" href={item.resumeUrl}>
                        Archived resume copy
                    </a>
                )}
            </JobRow>
            <details className="mb-6">
                <summary className="kt-text-link">
                    Update your progress and notes
                </summary>
                <form
                    className="mt-4 max-w-2xl"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(`/applications/${item.id}`, {
                            preserveScroll: true,
                        });
                    }}
                >
                    <div className="kt-field">
                        <label htmlFor={`status-${item.id}`}>
                            Your progress
                        </label>
                        <select
                            id={`status-${item.id}`}
                            value={form.data.status}
                            onChange={(event) =>
                                form.setData('status', event.target.value)
                            }
                        >
                            {[
                                ['opened', 'Opened link — not applied yet'],
                                ['applied', 'Applied'],
                                ['interview', 'Interview'],
                                ['offered', 'Offer received'],
                                ['accepted', 'Offer accepted'],
                                ['rejected', 'Rejected'],
                                ['withdrawn', 'Withdrawn'],
                            ].map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                        {form.errors.status && (
                            <p className="kt-error" role="alert">
                                {form.errors.status}
                            </p>
                        )}
                    </div>
                    <div className="kt-field mt-4">
                        <label htmlFor={`notes-${item.id}`}>
                            Private notes
                        </label>
                        <textarea
                            id={`notes-${item.id}`}
                            value={form.data.notes}
                            maxLength={10000}
                            onChange={(event) =>
                                form.setData('notes', event.target.value)
                            }
                        />
                        {form.errors.notes && (
                            <p className="kt-error" role="alert">
                                {form.errors.notes}
                            </p>
                        )}
                    </div>
                    <div className="kt-form-actions">
                        <button
                            className="kt-button"
                            disabled={form.processing}
                        >
                            {form.processing ? 'Saving…' : 'Save progress'}
                        </button>
                    </div>
                </form>
            </details>
            {item.coverLetter && (
                <details className="mb-6">
                    <summary className="kt-text-link">
                        Archived cover letter
                    </summary>
                    <p className="kt-prose mt-3">{item.coverLetter}</p>
                </details>
            )}
        </div>
    );
}
export default function Applications({
    applications,
}: {
    applications: Paginated<Application>;
}) {
    return (
        <Portal title="Application tracker">
            <PageTitle
                title="Your application tracker"
                description="Keep a personal record of roles you’ve visited and applied to. Applications happen on the source site; you control the progress recorded here."
            />
            {applications.data.length ? (
                applications.data.map((item) => (
                    <TrackedApplication key={item.id} item={item} />
                ))
            ) : (
                <EmptyState
                    title="Start tracking your next move"
                    description="Open a role’s application link, then mark it as applied once you’ve submitted on the original site."
                    href="/jobs"
                    action="Explore jobs"
                />
            )}
            <Pagination page={applications} />
        </Portal>
    );
}
