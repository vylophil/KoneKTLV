import { Head } from '@inertiajs/react';

type Job = {
    id: number;
    title: string;
    company: string;
    location: string;
    workType: string;
};

type JobsIndexProps = Readonly<{
    jobs: Job[];
    search: string;
}>;

export default function JobsIndex({ jobs, search }: JobsIndexProps) {
    return (
        <>
            <Head title="Find your next move" />
            <div className="min-h-screen bg-[#f5f6f0] text-[#17251e]">
                <header className="border-b border-[#dce2d9] bg-[#f5f6f0]">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
                        <a
                            className="text-2xl font-semibold tracking-tight"
                            href="/"
                            aria-label="KoneKT home"
                        >
                            Kone<span className="text-[#e45e42]">K</span>T
                        </a>
                        <div className="flex items-center gap-6 text-sm font-medium">
                            <a className="hover:text-[#e45e42]" href="#jobs">
                                Find a job
                            </a>
                            <a
                                className="hover:text-[#e45e42]"
                                href="#employers"
                            >
                                For employers
                            </a>
                            <a
                                className="rounded-sm bg-[#183b2d] px-4 py-2.5 text-white transition-colors hover:bg-[#24543f]"
                                href="#jobs"
                            >
                                Get started
                            </a>
                        </div>
                    </nav>
                </header>

                <main>
                    <section className="mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-10 lg:py-24">
                        <div>
                            <p className="mb-5 text-sm font-semibold tracking-wide text-[#b64c35]">
                                A better way to find work
                            </p>
                            <h1 className="max-w-2xl text-5xl leading-[1.06] font-semibold tracking-tight sm:text-6xl">
                                Find work that moves you forward.
                            </h1>
                            <p className="mt-6 max-w-xl text-lg leading-8 text-[#52635a]">
                                Discover roles that fit your experience and
                                meet the people who can help you grow.
                            </p>
                            <a
                                className="mt-8 inline-flex items-center rounded-sm bg-[#e45e42] px-5 py-3 font-semibold text-white transition-colors hover:bg-[#c94e36]"
                                href="#jobs"
                            >
                                Explore opportunities
                            </a>
                        </div>

                        <div className="relative overflow-hidden border border-[#d6dfd5] bg-[#e7eee5] p-8 sm:p-10">
                            <div className="absolute top-0 right-0 h-32 w-32 translate-x-1/3 -translate-y-1/3 rounded-full bg-[#d3dfd0]" />
                            <div className="relative">
                                <p className="text-sm font-semibold text-[#496454]">
                                    Your next opportunity
                                </p>
                                <h2 className="mt-3 max-w-sm text-3xl leading-tight font-semibold">
                                    More than a job search. A place to connect.
                                </h2>
                                <div className="mt-8 grid grid-cols-2 gap-3">
                                    <div className="border border-[#d2ddd1] bg-[#f5f6f0] p-4">
                                        <p className="text-2xl font-semibold text-[#183b2d]">
                                            01
                                        </p>
                                        <p className="mt-2 text-sm text-[#52635a]">
                                            Find roles for your skills
                                        </p>
                                    </div>
                                    <div className="border border-[#d2ddd1] bg-[#f5f6f0] p-4">
                                        <p className="text-2xl font-semibold text-[#e45e42]">
                                            02
                                        </p>
                                        <p className="mt-2 text-sm text-[#52635a]">
                                            Build your network
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        id="jobs"
                        className="border-y border-[#dce2d9] bg-white"
                    >
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
                                    Search by role or location. Resume-based
                                    matching will build on this job directory.
                                </p>
                            </div>

                            <form
                                action="/"
                                method="get"
                                className="mt-8 flex flex-col gap-3 sm:flex-row"
                            >
                                <label className="sr-only" htmlFor="job-search">
                                    Job title or keyword
                                </label>
                                <input
                                    id="job-search"
                                    name="search"
                                    defaultValue={search}
                                    placeholder="Job title or keyword"
                                    className="min-h-12 min-w-0 flex-1 border border-[#cbd5cb] bg-[#fbfcf9] px-4 text-base outline-none placeholder:text-[#78867e] focus:border-[#24543f]"
                                />
                                <button
                                    className="min-h-12 bg-[#183b2d] px-7 font-semibold text-white transition-colors hover:bg-[#24543f]"
                                    type="submit"
                                >
                                    Search jobs
                                </button>
                            </form>

                            <div className="mt-8 divide-y divide-[#e4e9e2] border-y border-[#e4e9e2]">
                                {jobs.length > 0 ? (
                                    jobs.map((job) => (
                                        <article
                                            className="flex flex-wrap items-center justify-between gap-4 py-5"
                                            key={job.id}
                                        >
                                            <div>
                                                <h3 className="font-semibold">
                                                    {job.title}
                                                </h3>
                                                <p className="mt-1 text-sm text-[#52635a]">
                                                    {job.company} · {job.location}
                                                </p>
                                            </div>
                                            <span className="border border-[#d6dfd5] px-3 py-1.5 text-sm text-[#496454]">
                                                {job.workType}
                                            </span>
                                        </article>
                                    ))
                                ) : (
                                    <div className="py-10">
                                        <h3 className="text-lg font-semibold">
                                            The directory is ready for its first
                                            roles.
                                        </h3>
                                        <p className="mt-2 max-w-xl text-sm leading-6 text-[#52635a]">
                                            Job listings will appear here as
                                            employers join KoneKT. Search and
                                            resume matching can connect to those
                                            listings next.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </section>

                    <section
                        id="employers"
                        className="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-10 text-sm text-[#52635a] lg:flex-row lg:items-center lg:justify-between lg:px-10"
                    >
                        <p>
                            <span className="font-semibold text-[#17251e]">
                                Building a team?
                            </span>{' '}
                            Employer job posting is part of the KoneKT roadmap.
                        </p>
                        <p>Good work starts with a connection.</p>
                    </section>
                </main>
            </div>
        </>
    );
}