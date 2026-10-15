import { Link } from '@inertiajs/react';
import { JobRow, Portal, type Job } from '@/components/portal';

export default function Home({ jobs }: { jobs: Job[] }) {
    return (
        <Portal title="Find your next move">
            <section className="kt-home">
                <h1>Good work starts with your next move.</h1>
                <p>
                    Find a role that fits your experience. Build your profile,
                    explore opportunities, and keep your applications in one
                    place.
                </p>
                <form action="/jobs" method="get" className="kt-home-search">
                    <label className="sr-only" htmlFor="home-search">
                        Role, company, or location
                    </label>
                    <input
                        className="kt-input"
                        id="home-search"
                        name="search"
                        maxLength={100}
                        placeholder="Role, company, or location"
                    />
                    <button className="kt-button shrink-0" type="submit">
                        Find jobs
                    </button>
                </form>
                <Link href="/register" className="kt-text-link mt-5">
                    Create your job-seeker profile →
                </Link>
            </section>
            <section className="mt-8">
                <div className="kt-section-heading mb-5">
                    <h2 className="text-2xl font-semibold">
                        Latest opportunities
                    </h2>
                    <Link className="kt-text-link" href="/jobs">
                        View all jobs →
                    </Link>
                </div>
                {jobs.map((job) => (
                    <JobRow key={job.id} job={job} />
                ))}
                {jobs.length === 0 && (
                    <p className="kt-meta py-8">
                        New roles will appear here as employers post them.
                    </p>
                )}
            </section>
        </Portal>
    );
}
