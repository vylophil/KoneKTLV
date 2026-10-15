import { useState } from 'react';

export type PublicJob = {
    id: number;
    title: string;
    company: string;
    location: string;
    workType: string;
    workArrangement: string;
    description: string;
    requirements: string | null;
    responsibilities: string | null;
};

type ArrangementFilter = 'All' | 'Remote' | 'Hybrid' | 'On Site';
type SortOrder = 'Newest' | 'Title A-Z';

type JobBrowserProps = Readonly<{
    jobs: PublicJob[];
    search: string;
}>;

const arrangements: ArrangementFilter[] = [
    'All',
    'Remote',
    'Hybrid',
    'On Site',
];

const jobTypes = [
    'Full Time',
    'Part Time',
    'Contract',
    'Internship',
    'Freelance',
];

export function JobBrowser({ jobs, search }: JobBrowserProps) {
    const [arrangement, setArrangement] = useState<ArrangementFilter>('All');
    const [typeFilter, setTypeFilter] = useState('All types');
    const [sortOrder, setSortOrder] = useState<SortOrder>('Newest');
    const filteredJobs = jobs.filter(
        (job) =>
            (arrangement === 'All' || job.workArrangement === arrangement) &&
            (typeFilter === 'All types' || job.workType === typeFilter),
    );
    const displayedJobs = [...filteredJobs];

    if (sortOrder === 'Title A-Z') {
        displayedJobs.sort((first, second) =>
            first.title.localeCompare(second.title),
        );
    }

    const hasFilters = arrangement !== 'All' || typeFilter !== 'All types';
    const hasControlsChanged = hasFilters || sortOrder !== 'Newest';

    return (
        <section id="jobs" className="border-y border-[#dce2d9] bg-white">
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
                        Search by role or location. Filter the latest openings
                        by work style and job type.
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
                        placeholder="Job title, company, or location"
                        className="min-h-12 min-w-0 flex-1 border border-[#cbd5cb] bg-[#fbfcf9] px-4 text-base outline-none placeholder:text-[#78867e] focus:border-[#24543f]"
                    />
                    <button
                        className="min-h-12 bg-[#183b2d] px-7 font-semibold text-white transition-colors hover:bg-[#24543f]"
                        type="submit"
                    >
                        Search jobs
                    </button>
                </form>

                <div className="mt-6 flex flex-col gap-5 border-y border-[#e4e9e2] py-5 lg:flex-row lg:items-end lg:justify-between">
                    <fieldset>
                        <legend className="mb-2 text-xs font-semibold text-[#52635a]">
                            Work arrangement
                        </legend>
                        <div className="inline-flex flex-wrap border border-[#cbd5cb] bg-[#fbfcf9] p-1">
                            {arrangements.map((option) => (
                                <button
                                    className={`min-h-9 px-3 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e45e42] ${arrangement === option ? 'bg-[#183b2d] text-white' : 'text-[#52635a] hover:bg-[#e7eee5]'}`}
                                    key={option}
                                    type="button"
                                    aria-pressed={arrangement === option}
                                    onClick={() => setArrangement(option)}
                                >
                                    {option}
                                </button>
                            ))}
                        </div>
                    </fieldset>

                    <div className="flex flex-wrap items-end gap-3">
                        <div>
                            <label
                                className="mb-2 block text-xs font-semibold text-[#52635a]"
                                htmlFor="job-type-filter"
                            >
                                Job type
                            </label>
                            <select
                                id="job-type-filter"
                                className="min-h-10 border border-[#cbd5cb] bg-[#fbfcf9] px-3 text-sm text-[#17251e] focus:border-[#24543f] focus:outline-none"
                                value={typeFilter}
                                onChange={(event) =>
                                    setTypeFilter(event.target.value)
                                }
                            >
                                <option>All types</option>
                                {jobTypes.map((jobType) => (
                                    <option key={jobType}>{jobType}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label
                                className="mb-2 block text-xs font-semibold text-[#52635a]"
                                htmlFor="job-sort-order"
                            >
                                Sort by
                            </label>
                            <select
                                id="job-sort-order"
                                className="min-h-10 border border-[#cbd5cb] bg-[#fbfcf9] px-3 text-sm text-[#17251e] focus:border-[#24543f] focus:outline-none"
                                value={sortOrder}
                                onChange={(event) =>
                                    setSortOrder(
                                        event.target.value as SortOrder,
                                    )
                                }
                            >
                                <option>Newest</option>
                                <option>Title A-Z</option>
                            </select>
                        </div>

                        {hasControlsChanged && (
                            <button
                                className="min-h-10 px-2 text-sm font-semibold text-[#b64c35] underline underline-offset-2"
                                type="button"
                                onClick={() => {
                                    setArrangement('All');
                                    setTypeFilter('All types');
                                    setSortOrder('Newest');
                                }}
                            >
                                Clear filters
                            </button>
                        )}
                    </div>
                </div>

                <div className="mt-4 flex items-center justify-between gap-3 text-sm text-[#52635a]">
                    <p aria-live="polite">
                        {displayedJobs.length} of {jobs.length} latest roles
                    </p>
                    {hasFilters && <p>Filtered from your current search</p>}
                </div>

                <JobResults
                    jobs={displayedJobs}
                    totalJobs={jobs.length}
                    search={search}
                />
            </div>
        </section>
    );
}

type JobResultsProps = Readonly<{
    jobs: PublicJob[];
    totalJobs: number;
    search: string;
}>;

function JobResults({ jobs, totalJobs, search }: JobResultsProps) {
    if (jobs.length === 0) {
        const emptyState = getEmptyState(totalJobs, search);

        return (
            <div className="mt-8 border-y border-[#e4e9e2] py-10">
                <h3 className="text-lg font-semibold">{emptyState.title}</h3>
                <p className="mt-2 max-w-xl text-sm leading-6 text-[#52635a]">
                    {emptyState.description}
                </p>
            </div>
        );
    }

    return (
        <div className="mt-8 divide-y divide-[#e4e9e2] border-y border-[#e4e9e2]">
            {jobs.map((job) => (
                <JobCard job={job} key={job.id} />
            ))}
        </div>
    );
}

function getEmptyState(totalJobs: number, search: string) {
    if (totalJobs > 0) {
        return {
            title: 'No roles match these filters.',
            description:
                'Change or clear the selected filters to see more roles.',
        };
    }

    if (search) {
        return {
            title: 'No open roles match that search.',
            description: 'Try another title, company, or location.',
        };
    }

    return {
        title: 'No open roles yet.',
        description:
            'Check back soon as employers add opportunities to KoneKT.',
    };
}

function JobCard({ job }: Readonly<{ job: PublicJob }>) {
    const [expanded, setExpanded] = useState(false);
    const detailsId = `job-details-${job.id}`;

    return (
        <article className="py-5 transition-colors hover:bg-[#fbfcf9]">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 className="font-semibold">{job.title}</h3>
                    <p className="mt-1 text-sm text-[#52635a]">
                        {job.company} · {job.location}
                    </p>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-[#52635a]">
                        {job.description}
                    </p>
                </div>
                <div className="flex shrink-0 flex-wrap gap-2">
                    <span className="border border-[#d6dfd5] px-3 py-1.5 text-sm text-[#496454]">
                        {job.workType}
                    </span>
                    <span className="border border-[#d6dfd5] px-3 py-1.5 text-sm text-[#496454]">
                        {job.workArrangement}
                    </span>
                </div>
            </div>

            <button
                className="mt-4 text-sm font-semibold text-[#183b2d] underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#e45e42]"
                type="button"
                aria-expanded={expanded}
                aria-controls={detailsId}
                onClick={() => setExpanded(!expanded)}
            >
                {expanded ? 'Hide role details' : 'View role details'}
            </button>

            <div
                className="mt-4 grid gap-5 border-l-2 border-[#e45e42] bg-[#f5f6f0] px-5 py-4 sm:grid-cols-2"
                id={detailsId}
                hidden={!expanded}
            >
                <div>
                    <h4 className="text-sm font-semibold">Responsibilities</h4>
                    <p className="mt-2 text-sm leading-6 whitespace-pre-line text-[#52635a]">
                        {job.responsibilities ||
                            'Details will be provided by the employer.'}
                    </p>
                </div>
                <div>
                    <h4 className="text-sm font-semibold">Requirements</h4>
                    <p className="mt-2 text-sm leading-6 whitespace-pre-line text-[#52635a]">
                        {job.requirements ||
                            'Details will be provided by the employer.'}
                    </p>
                </div>
            </div>
        </article>
    );
}
