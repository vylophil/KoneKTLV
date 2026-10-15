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
    appliedAt: string;
    coverLetter: string | null;
    resumeUrl: string | null;
    job: Job;
};
export default function Applications({
    applications,
}: {
    applications: Paginated<Application>;
}) {
    return (
        <Portal title="Applications">
            <PageTitle
                title="Your applications"
                description="Follow each application from the day you send it."
            />
            {applications.data.length ? (
                applications.data.map((item) => (
                    <div key={item.id}>
                        <JobRow job={item.job}>
                            <span className="kt-status">{item.status}</span>
                            <p className="kt-meta">Sent {item.appliedAt}</p>
                            {item.resumeUrl && (
                                <a
                                    className="kt-text-link"
                                    href={item.resumeUrl}
                                >
                                    Attached resume
                                </a>
                            )}
                        </JobRow>
                        {item.coverLetter && (
                            <details className="mb-6">
                                <summary className="kt-text-link">
                                    Your cover letter
                                </summary>
                                <p className="kt-prose mt-3">
                                    {item.coverLetter}
                                </p>
                            </details>
                        )}
                    </div>
                ))
            ) : (
                <EmptyState
                    title="You haven’t applied yet"
                    description="Find a role that fits, open its details, and send your first application with your resume."
                    href="/jobs"
                    action="Explore jobs"
                />
            )}
            <Pagination page={applications} />
        </Portal>
    );
}
