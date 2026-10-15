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

function SavedRow({ job }: { job: Job }) {
    const form = useForm({});
    return (
        <JobRow job={job}>
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.delete(`/jobs/${job.id}/save`, {
                        preserveScroll: true,
                    });
                }}
            >
                <button className="kt-text-button" disabled={form.processing}>
                    Remove
                </button>
            </form>
        </JobRow>
    );
}
export default function SavedJobs({ jobs }: { jobs: Paginated<Job> }) {
    return (
        <Portal title="Saved jobs">
            <PageTitle
                title="Saved jobs"
                description="Keep the opportunities you want to come back to in one place."
            />
            {jobs.data.length ? (
                jobs.data.map((job) => <SavedRow key={job.id} job={job} />)
            ) : (
                <EmptyState
                    title="Your shortlist starts here"
                    description="Save a role from its details page, then come back when you’re ready to apply."
                    href="/jobs"
                    action="Find jobs"
                />
            )}
            <Pagination page={jobs} />
        </Portal>
    );
}
