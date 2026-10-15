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

type Filters = {
    search?: string;
    arrangement?: string;
    type?: string;
    sort?: string;
    location?: string;
    cursor?: string;
    cached?: string;
};
export default function Jobs({
    jobs,
    filters,
    liveSearch,
    providerEnabled,
    sourceError,
    nextSearchUrl,
    fetchedAt,
}: {
    jobs: Paginated<Job>;
    filters: Filters;
    liveSearch: boolean;
    providerEnabled: boolean;
    sourceError: string | null;
    nextSearchUrl: string | null;
    fetchedAt: string | null;
}) {
    const form = useForm({
        search: filters.search ?? '',
        arrangement: filters.arrangement ?? '',
        type: filters.type ?? '',
        sort: filters.sort ?? 'newest',
        location: filters.location ?? '',
    });
    const query = new URLSearchParams(
        Object.entries(filters).filter(([, value]) => !!value) as [
            string,
            string,
        ][],
    ).toString();
    return (
        <Portal title="Find jobs">
            <PageTitle
                title="Find your next role"
                description="Find real opportunities in the Philippines. Save roles, apply on the source site, and track your own progress."
            />
            {sourceError && (
                <div className="kt-notice" role="status">
                    <p>{sourceError}</p>
                    <Link className="kt-text-link" href="/jobs?cached=1">
                        Browse previously retrieved listings
                    </Link>
                </div>
            )}
            <div className="kt-search-layout">
                <form
                    className="kt-filters"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.get('/jobs');
                    }}
                >
                    <div className="kt-field">
                        <label htmlFor="search">
                            Role, company, or location
                        </label>
                        <input
                            id="search"
                            name="search"
                            value={form.data.search}
                            maxLength={100}
                            placeholder="e.g. Laravel developer"
                            onChange={(event) =>
                                form.setData('search', event.target.value)
                            }
                        />
                    </div>
                    {providerEnabled && (
                        <div className="kt-field">
                            <label htmlFor="location">
                                City or region (optional)
                            </label>
                            <input
                                id="location"
                                maxLength={100}
                                placeholder="e.g. Manila or Cebu"
                                value={form.data.location}
                                onChange={(event) =>
                                    form.setData('location', event.target.value)
                                }
                            />
                        </div>
                    )}
                    <div className="kt-field">
                        <label htmlFor="arrangement">Work arrangement</label>
                        <select
                            id="arrangement"
                            value={form.data.arrangement}
                            onChange={(event) =>
                                form.setData('arrangement', event.target.value)
                            }
                        >
                            <option value="">Any arrangement</option>
                            <option value="remote">Remote</option>
                            {!providerEnabled && (
                                <>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="on_site">On site</option>
                                </>
                            )}
                        </select>
                    </div>
                    <div className="kt-field">
                        <label htmlFor="type">Job type</label>
                        <select
                            id="type"
                            value={form.data.type}
                            onChange={(event) =>
                                form.setData('type', event.target.value)
                            }
                        >
                            <option value="">Any type</option>
                            {[
                                ['full_time', 'Full time'],
                                ['part_time', 'Part time'],
                                ['contract', 'Contract'],
                                ['internship', 'Internship'],
                                ...(!providerEnabled
                                    ? [['freelance', 'Freelance']]
                                    : []),
                            ].map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="kt-field">
                        <label htmlFor="sort">Sort by</label>
                        <select
                            id="sort"
                            value={form.data.sort}
                            onChange={(event) =>
                                form.setData('sort', event.target.value)
                            }
                        >
                            <option value="newest">
                                {providerEnabled
                                    ? 'Source relevance'
                                    : 'Newest first'}
                            </option>
                            <option value="title">
                                {providerEnabled
                                    ? 'Title A–Z (this page)'
                                    : 'Title A–Z'}
                            </option>
                        </select>
                    </div>
                    <button
                        className="kt-button"
                        disabled={form.processing}
                        type="submit"
                    >
                        {form.processing ? 'Searching…' : 'Search jobs'}
                    </button>
                    <Link className="kt-text-link" href="/jobs">
                        Clear search and filters
                    </Link>
                </form>
                <div>
                    <div className="kt-results-heading">
                        <p>
                            {jobs.total} {jobs.total === 1 ? 'role' : 'roles'}{' '}
                            {liveSearch
                                ? 'on this results page'
                                : 'in retrieved listings'}
                        </p>
                        {!liveSearch && jobs.total > 0 && (
                            <p>
                                {jobs.from}–{jobs.to} shown
                            </p>
                        )}
                    </div>
                    {liveSearch && fetchedAt && (
                        <p className="kt-meta mb-4">
                            Philippine listings via JSearch · Retrieved{' '}
                            <time dateTime={fetchedAt}>
                                {new Date(fetchedAt).toLocaleString('en-PH', {
                                    timeZone: 'Asia/Manila',
                                    dateStyle: 'medium',
                                    timeStyle: 'short',
                                })}
                            </time>
                            . Results may be cached.
                        </p>
                    )}
                    {jobs.data.length ? (
                        jobs.data.map((job) => (
                            <JobRow
                                key={job.id}
                                job={job}
                                query={query ? `?${query}` : ''}
                            />
                        ))
                    ) : (
                        <EmptyState
                            title="No roles match this search"
                            description="Try a broader keyword or clear a filter to see more opportunities."
                            href="/jobs"
                            action="Reset search"
                        />
                    )}
                    {liveSearch ? (
                        <nav
                            className="kt-pagination"
                            aria-label="Search results pages"
                        >
                            <Link
                                className="kt-text-link"
                                href={`/jobs?${new URLSearchParams(Object.entries(form.data).filter(([, value]) => value !== ''))}`}
                            >
                                First results
                            </Link>
                            {nextSearchUrl && (
                                <Link
                                    className="kt-button kt-button-secondary"
                                    href={nextSearchUrl}
                                >
                                    Next results
                                </Link>
                            )}
                        </nav>
                    ) : (
                        <Pagination page={jobs} />
                    )}
                </div>
            </div>
        </Portal>
    );
}
