import { Head, Link } from '@inertiajs/react';
import { Brand, ArrowIcon } from '@/components/brand';
import { JobSearch } from '@/components/job-search';
import { JobRow, type Job } from '@/components/portal';
export default function Home({ jobs }: { jobs: Job[] }) {
    return (
        <>
            <Head title="Find your next move" />
            <div className="kt-home-shell">
                <a className="kt-skip" href="#main">
                    Skip to content
                </a>
                <header className="site-header">
                    <nav
                        className="site-nav page-container"
                        aria-label="Main navigation"
                    >
                        <Link href="/" aria-label="KoneKT home">
                            <Brand />
                        </Link>
                        <div className="home-nav">
                            <Link href="/jobs">Find a job</Link>
                            <a href="#next-step">How it works</a>
                            <Link href="/login" className="kt-button">
                                Sign in
                            </Link>
                        </div>
                    </nav>
                </header>
                <main id="main">
                    <section
                        className="page-container hero"
                        aria-labelledby="home-heading"
                    >
                        <div className="hero-main">
                            <h1 id="home-heading">
                                Find work that
                                <br className="hidden sm:block" />{' '}
                                <span>moves you forward.</span>
                            </h1>
                            <p className="hero-description">
                                <strong className="hero-original-message">
                                    More than a job search. A place to connect.
                                </strong>
                                Discover roles that fit your experience, find
                                matches from your resume, and keep your
                                applications in one place.
                            </p>
                            <JobSearch />
                            <div className="hero-shortcuts">
                                <a href="#jobs">
                                    Explore opportunities <ArrowIcon />
                                </a>
                                <a href="/profile?section=resume">
                                    Match with your resume <ArrowIcon />
                                </a>
                            </div>
                            <ul
                                className="hero-journey"
                                aria-label="Your next steps"
                            >
                                <li>
                                    <Link href="/jobs">
                                        <svg
                                            aria-hidden="true"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="1.6"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        >
                                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a22 22 0 0 0 18 0" />
                                            <rect
                                                x="3"
                                                y="7"
                                                width="18"
                                                height="14"
                                                rx="3"
                                            />
                                            <path d="M12 12v3" />
                                        </svg>
                                        <span>
                                            Find roles for your experience
                                        </span>
                                    </Link>
                                </li>
                                <li>
                                    <Link href="/applications">
                                        <svg
                                            aria-hidden="true"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="1.6"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        >
                                            <rect
                                                x="5"
                                                y="3"
                                                width="14"
                                                height="18"
                                                rx="3"
                                            />
                                            <path d="M9 7h6m-6 4h6m-6 5 2 2 4-4" />
                                        </svg>
                                        <span>
                                            Apply and track your progress
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                        <aside
                            className="opportunity-preview"
                            aria-label="Opportunity preview"
                        >
                            {jobs[0] ? (
                                <>
                                    <div className="preview-top">
                                        <span
                                            className="company-monogram"
                                            aria-hidden="true"
                                        >
                                            {jobs[0].company
                                                .split(' ')
                                                .map((word) => word[0])
                                                .slice(0, 2)
                                                .join('')}
                                        </span>
                                        <span>{jobs[0].company}</span>
                                        <span className="live-indicator">
                                            Open role
                                        </span>
                                    </div>
                                    <h2>{jobs[0].title}</h2>
                                    <p className="preview-location">
                                        {jobs[0].location}
                                    </p>
                                    <div className="preview-tags">
                                        <span>{jobs[0].workType}</span>
                                        <span>{jobs[0].workArrangement}</span>
                                    </div>
                                    <p className="preview-description">
                                        {jobs[0].description}
                                    </p>
                                    <a
                                        href={'/jobs/' + jobs[0].id}
                                        className="preview-link"
                                    >
                                        Explore this role <ArrowIcon />
                                    </a>
                                </>
                            ) : (
                                <>
                                    <h2>Your next chapter starts here.</h2>
                                    <p className="preview-description">
                                        Build your profile and add your resume
                                        while new opportunities arrive.
                                    </p>
                                    <a
                                        className="preview-link"
                                        href="/register"
                                    >
                                        Build your profile <ArrowIcon />
                                    </a>
                                </>
                            )}
                            <div className="preview-bottom">
                                <span>Work. Connect. Grow.</span>
                                <Brand />
                            </div>
                        </aside>
                    </section>
                    <section id="jobs" className="jobs-section">
                        <div className="page-container section-space">
                            <div className="section-heading">
                                <h2>Explore open roles</h2>
                                <Link
                                    className="kt-text-link home-all-jobs"
                                    href="/jobs"
                                >
                                    View all jobs and filters <ArrowIcon />
                                </Link>
                            </div>
                            <div className="home-job-list">
                                {jobs.map((job) => (
                                    <JobRow key={job.id} job={job} />
                                ))}
                            </div>
                            {!jobs.length && (
                                <p className="kt-empty">
                                    Check back soon, or create a profile so
                                    you&apos;re ready when the right role
                                    appears.
                                </p>
                            )}
                        </div>
                    </section>
                    <section id="next-step" className="home-next-step">
                        <div className="page-container">
                            <div>
                                <h2>A clearer path to your next role.</h2>
                                <p>
                                    Build your profile, find keyword matches
                                    from your resume, and keep your applications
                                    in one place.
                                </p>
                            </div>
                            <Link href="/register" className="kt-button">
                                Create your profile <ArrowIcon />
                            </Link>
                        </div>
                    </section>
                </main>
                <footer className="page-container site-footer">
                    <p>Good work starts with your next move.</p>
                    <Link href="/jobs">Explore opportunities</Link>
                </footer>
            </div>
        </>
    );
}
