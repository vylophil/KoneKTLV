import { Head, useForm } from '@inertiajs/react';
import { JobBrowser, type PublicJob } from '@/components/jobs/job-browser';

type MatchedJob = PublicJob & { matchScore: number };

type JobsIndexProps = Readonly<{
    jobs: PublicJob[];
    matchedJobs: MatchedJob[];
    search: string;
    canUploadResume: boolean;
    hasResume: boolean;
    resume: { name: string; downloadUrl: string } | null;
}>;

export default function JobsIndex({
    jobs,
    matchedJobs,
    search,
    canUploadResume,
    hasResume,
    resume,
}: JobsIndexProps) {
    const resumeForm = useForm<{ resume: File | null }>({ resume: null });

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
                                href={canUploadResume ? '/dashboard' : '/login'}
                            >
                                {canUploadResume ? 'Dashboard' : 'Sign in'}
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
                                Discover roles that fit your experience and meet
                                the people who can help you grow.
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
                        id="resume-upload"
                        className="border-b border-[#dce2d9] bg-[#e7eee5]"
                    >
                        <div className="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:px-10">
                            <div>
                                <p className="text-sm font-semibold text-[#b64c35]">
                                    Start with your experience
                                </p>
                                <h2 className="mt-2 text-2xl font-semibold tracking-tight">
                                    Upload your resume
                                </h2>
                                <p className="mt-2 max-w-lg text-sm leading-6 text-[#52635a]">
                                    Add a text-based PDF to your private
                                    profile. KoneKT compares resume keywords
                                    with open job details to rank suggestions.
                                </p>
                            </div>

                            {canUploadResume ? (
                                <div>
                                    <form
                                        className="flex flex-col gap-3 sm:flex-row sm:items-start"
                                        encType="multipart/form-data"
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            resumeForm.post('/profile/resume', {
                                                forceFormData: true,
                                            });
                                        }}
                                    >
                                        <div className="min-w-0 flex-1">
                                            <label
                                                className="sr-only"
                                                htmlFor="resume-file"
                                            >
                                                Resume PDF
                                            </label>
                                            <input
                                                id="resume-file"
                                                type="file"
                                                accept="application/pdf,.pdf"
                                                required
                                                onChange={(event) =>
                                                    resumeForm.setData(
                                                        'resume',
                                                        event.currentTarget
                                                            .files?.[0] ?? null,
                                                    )
                                                }
                                                className="min-h-12 w-full border border-[#bfcec0] bg-[#f8faf6] px-3 py-2 text-sm file:mr-3 file:border-0 file:bg-[#dce8dc] file:px-3 file:py-2 file:font-medium file:text-[#183b2d]"
                                            />
                                            {resumeForm.errors.resume && (
                                                <p
                                                    className="mt-2 text-sm text-[#a6382a]"
                                                    role="alert"
                                                >
                                                    {resumeForm.errors.resume}
                                                </p>
                                            )}
                                            {resumeForm.progress && (
                                                <output
                                                    className="mt-2 text-sm text-[#496454]"
                                                >
                                                    Uploading{' '}
                                                    {resumeForm.progress.percentage}%
                                                </output>
                                            )}
                                        </div>
                                        <button
                                            className="min-h-12 shrink-0 bg-[#183b2d] px-5 font-semibold text-white transition-colors hover:bg-[#24543f] disabled:cursor-not-allowed disabled:opacity-60"
                                            type="submit"
                                            disabled={
                                                resumeForm.processing ||
                                                resumeForm.data.resume === null
                                            }
                                        >
                                            {resumeForm.processing
                                                ? 'Uploading...'
                                                : 'Upload PDF'}
                                        </button>
                                    </form>
                                    <p className="mt-2 text-xs text-[#52635a]">
                                        PDF only, maximum 5 MB. Scanned image-only PDFs cannot be matched yet.
                                    </p>
                                    {resume && (
                                        <p className="mt-3 text-sm text-[#183b2d]">
                                            Current resume:{' '}
                                            <a
                                                className="font-semibold underline underline-offset-2"
                                                href={resume.downloadUrl}
                                            >
                                                {resume.name}
                                            </a>
                                        </p>
                                    )}
                                </div>
                            ) : (
                                <p className="text-sm text-[#52635a]">
                                    <a
                                        className="font-semibold text-[#183b2d] underline underline-offset-2"
                                        href="/login"
                                    >
                                        Sign in
                                    </a>{' '}
                                    to upload a resume to your profile.
                                </p>
                            )}
                        </div>
                    </section>

                    {canUploadResume && hasResume && (
                        <section
                            id="matches"
                            className="border-b border-[#dce2d9] bg-[#f5f6f0]"
                        >
                            <div className="mx-auto max-w-7xl px-6 py-12 lg:px-10">
                                <p className="text-sm font-semibold text-[#b64c35]">
                                    Based on your resume
                                </p>
                                <div className="mt-2 flex flex-wrap items-end justify-between gap-3">
                                    <h2 className="text-3xl font-semibold tracking-tight">
                                        Matches from your resume
                                    </h2>
                                    {resume && (
                                        <a
                                            className="text-sm font-semibold text-[#183b2d] underline underline-offset-2"
                                            href={resume.downloadUrl}
                                        >
                                            {resume.name}
                                        </a>
                                    )}
                                </div>
                                <p className="mt-2 max-w-2xl text-sm leading-6 text-[#52635a]">
                                    Ranked by shared keywords between your PDF
                                    and each open role. Use these suggestions as
                                    a starting point, not a guarantee of fit.
                                </p>

                                <div className="mt-6 divide-y divide-[#dce2d9] border-y border-[#dce2d9]">
                                    {matchedJobs.length > 0 ? (
                                        matchedJobs.map((job) => (
                                            <article
                                                className="flex flex-wrap items-start justify-between gap-4 py-5"
                                                key={job.id}
                                            >
                                                <div className="min-w-0 flex-1">
                                                    <h3 className="font-semibold">
                                                        {job.title}
                                                    </h3>
                                                    <p className="mt-1 text-sm text-[#52635a]">
                                                        {job.company} ·{' '}
                                                        {job.location}
                                                    </p>
                                                    <p className="mt-2 max-w-2xl text-sm leading-6 text-[#52635a]">
                                                        {job.description}
                                                    </p>
                                                    <p className="mt-2 text-xs text-[#496454]">
                                                        {job.workType} ·{' '}
                                                        {job.workArrangement}
                                                    </p>
                                                </div>
                                                <div className="border border-[#b8cbbb] px-3 py-2 text-sm font-semibold text-[#183b2d]">
                                                    {Math.round(job.matchScore)}% keyword match
                                                </div>
                                            </article>
                                        ))
                                    ) : (
                                        <div className="py-8">
                                            <h3 className="font-semibold">
                                                No keyword matches yet.
                                            </h3>
                                            <p className="mt-2 text-sm text-[#52635a]">
                                                Try a resume with selectable
                                                text, or check back when more
                                                related roles are posted.
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </section>
                    )}

                    <JobBrowser jobs={jobs} search={search} />

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
