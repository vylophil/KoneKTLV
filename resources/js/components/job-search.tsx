export function JobSearch() {
    return (
        <form action="/jobs" method="get" role="search" className="job-search">
            <label className="sr-only" htmlFor="job-search">
                Job title or keyword
            </label>
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
            >
                <circle cx="10.5" cy="10.5" r="6.5" />
                <path d="m16 16 4 4" />
            </svg>
            <input
                id="job-search"
                name="search"
                type="search"
                maxLength={100}
                placeholder="Job title, company, or location"
            />
            <button type="submit">Search jobs</button>
        </form>
    );
}
