import { Head, Link } from '@inertiajs/react';
import type { Job } from '@/components/portal';

export default function Home({ jobs }: { jobs: Job[] }) {
    return (
        <>
            <Head title="Find your next move" />
            <div className="min-h-screen bg-[#f5f6f0] text-[#17251e]">
                <a className="kt-skip" href="#main">
                    Skip to content
                </a>
                <header className="border-b border-[#dce2d9]">
                    <nav
                        aria-label="Main navigation"
                        className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-5 lg:px-10"
                    >
                        <Link
                            className="text-2xl font-semibold tracking-tight"
                            href="/"
                            aria-label="KoneKT home"
                        >
                            Kone<span className="text-[#e45e42]">K</span>T
                        </Link>
                        <div className="flex flex-wrap items-center gap-4 text-sm font-medium sm:gap-6">
                            <Link
                                className="inline-flex min-h-11 items-center hover:text-[#a83e2c]"
                                href="/jobs"
                            >
                                Find a job
                            </Link>
                            <a
                                className="inline-flex min-h-11 items-center hover:text-[#a83e2c]"
                                href="#next-step"
                            >
                                How it works
                            </a>
                            <Link
                                className="inline-flex min-h-11 items-center rounded-sm bg-[#183b2d] px-4 text-white transition-colors hover:bg-[#24543f]"
                                href="/login"
                            >
                                Sign in
                            </Link>
                        </div>
                    </nav>
                </header>

                <main id="main">
                    <section className="mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-10 lg:py-24">
                        <div>
                            <p className="mb-5 text-sm font-semibold tracking-wide text-[#b64c35]">
                                A better way to find work
                            </p>
                            <h1 className="max-w-2xl text-5xl leading-[1.06] font-semibold tracking-tight sm:text-6xl">
                                Find work that moves you forward.
                            </h1>
                            <p className="mt-6 max-w-xl text-lg leading-8 text-[#52635a]">
                                Discover roles that fit your experience, find
                                matches from your resume, and keep your
                                applications in one place.
                            </p>
                            <Link
                                className="mt-8 inline-flex min-h-12 items-center rounded-sm bg-[#c3472e] px-5 py-3 font-semibold text-white transition-colors hover:bg-[#a83e2c]"
                                href="/jobs"
                            >
                                Explore opportunities
                            </Link>
                        </div>

                        <div className="relative overflow-hidden border border-[#d6dfd5] bg-[#e7eee5] p-8 sm:p-10">
                            <div
                                aria-hidden="true"
                                className="absolute top-0 right-0 h-32 w-32 translate-x-1/3 -translate-y-1/3 rounded-full bg-[#d3dfd0]"
                            />
                            <div className="relative">
                                <p className="text-sm font-semibold text-[#496454]">
                                    Your next opportunity
                                </p>
                                <h2 className="mt-3 max-w-sm text-3xl leading-tight font-semibold">
                                    A clearer path to your next role.
                                </h2>
                                <ol className="mt-8 grid grid-cols-2 gap-3">
                                    <li className="border border-[#d2ddd1] bg-[#f5f6f0] p-4">
                                        <p
                                            aria-hidden="true"
                                            className="text-2xl font-semibold text-[#183b2d]"
                                        >
                                            01
                                        </p>
                                        <p className="mt-2 text-sm text-[#52635a]">
                                            Find roles for your experience
                                        </p>
                                    </li>
                                    <li className="border border-[#d2ddd1] bg-[#f5f6f0] p-4">
                                        <p
                                            aria-hidden="true"
                                            className="text-2xl font-semibold text-[#a83e2c]"
                                        >
                                            02
                                        </p>
                                        <p className="mt-2 text-sm text-[#52635a]">
                                            Apply and track your progress
                                        </p>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </section>

                    <section
                        id="next-step"
                        className="border-b border-[#dce2d9] bg-[#e7eee5]"
                    >
                        <div className="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-10">
                            <div>
                                <p className="text-sm font-semibold text-[#b64c35]">
                                    Start with your experience
                                </p>
                                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                                    Your resume. Your next move.
                                </h2>
                                <p className="mt-2 max-w-lg text-sm leading-6 text-[#52635a]">
                                    Add a text-based PDF to your private
                                    profile. Review shared keywords on your
                                    Matches page, then open a role to save it or
                                    apply.
                                </p>
                            </div>
                            <div className="text-sm text-[#52635a]">
                                <p>
                                    <Link
                                        className="inline-flex min-h-11 items-center font-semibold text-[#183b2d] underline underline-offset-2"
                                        href="/profile?section=resume"
                                    >
                                        Sign in to upload your resume
                                    </Link>
                                    <span> and find your matches.</span>
                                </p>
                                <p className="mt-1">
                                    New to KoneKT?{' '}
                                    <Link
                                        className="inline-flex min-h-11 items-center font-semibold text-[#183b2d] underline underline-offset-2"
                                        href="/register"
                                    >
                                        Create your profile
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section className="border-b border-[#dce2d9] bg-white">
                        <div className="mx-auto max-w-7xl px-6 py-14 lg:px-10">
                            <div className="flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p className="text-sm font-semibold text-[#b64c35]">
                                        Find your fit
                                    </p>
                                    <h2 className="mt-2 text-3xl font-semibold tracking-tight">
                                        Explore open roles
                                    </h2>
                                </div>
                                <p className="max-w-md text-sm leading-6 text-[#52635a]">
                                    Search by role, company, or location. Refine
                                    your results by work arrangement and job
                                    type on the jobs page.
                                </p>
                            </div>
                            <form
                                action="/jobs"
                                method="get"
                                className="mt-8 flex flex-col gap-3 sm:flex-row"
                            >
                                <label
                                    className="sr-only"
                                    htmlFor="home-search"
                                >
                                    Job title, company, or location
                                </label>
                                <input
                                    id="home-search"
                                    name="search"
                                    maxLength={100}
                                    placeholder="Job title, company, or location"
                                    className="min-h-12 min-w-0 flex-1 border border-[#cbd5cb] bg-[#fbfcf9] px-4 text-base placeholder:text-[#627268]"
                                />
                                <button
                                    className="min-h-12 bg-[#183b2d] px-7 font-semibold text-white transition-colors hover:bg-[#24543f]"
                                    type="submit"
                                >
                                    Search jobs
                                </button>
                            </form>
                            <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-y border-[#e4e9e2] py-4 text-sm text-[#52635a]">
                                <p>
                                    {jobs.length
                                        ? 'A look at the latest opportunities'
                                        : 'New opportunities are on their way'}
                                </p>
                                <Link
                                    className="inline-flex min-h-11 items-center font-semibold text-[#183b2d] underline underline-offset-2"
                                    href="/jobs"
                                >
                                    View all jobs and filters
                                </Link>
                            </div>
                            {jobs.map((job) => (
                                <article
                                    className="border-b border-[#e4e9e2] py-6 last:border-b-0"
                                    key={job.id}
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div className="min-w-0 flex-1">
                                            <h3 className="font-semibold">
                                                <Link
                                                    className="hover:underline"
                                                    href={`/jobs/${job.id}`}
                                                >
                                                    {job.title}
                                                </Link>
                                            </h3>
                                            <p className="mt-1 text-sm text-[#52635a]">
                                                {job.company} · {job.location}
                                            </p>
                                            <p className="mt-2 max-w-2xl text-sm leading-6 text-[#52635a]">
                                                {job.description}
                                            </p>
                                        </div>
                                        <div className="flex flex-wrap gap-2 text-sm text-[#496454]">
                                            <span className="border border-[#d6dfd5] px-3 py-1.5">
                                                {job.workType}
                                            </span>
                                            <span className="border border-[#d6dfd5] px-3 py-1.5">
                                                {job.workArrangement}
                                            </span>
                                        </div>
                                    </div>
                                    <Link
                                        className="mt-2 inline-flex min-h-11 items-center text-sm font-semibold text-[#183b2d] underline underline-offset-2"
                                        href={`/jobs/${job.id}`}
                                    >
                                        View role details
                                    </Link>
                                </article>
                            ))}
                            {jobs.length === 0 && (
                                <p className="py-8 text-sm leading-6 text-[#52635a]">
                                    Check back soon, or create a profile so
                                    you’re ready when the right role appears.
                                </p>
                            )}
                        </div>
                    </section>
                </main>
                <footer className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-6 py-8 text-sm text-[#52635a] lg:px-10">
                    <p>Good work starts with your next move.</p>
                    <Link
                        className="inline-flex min-h-11 items-center font-semibold text-[#183b2d] underline underline-offset-2"
                        href="/register"
                    >
                        Create your profile
                    </Link>
                </footer>
            </div>
        </>
    );
}
