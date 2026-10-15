import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { InputHTMLAttributes, ReactNode } from 'react';
import type { Auth } from '@/types';
import { Brand, ArrowIcon } from '@/components/brand';

type Shared = {
    auth: Auth;
    flash: { success?: string };
};
const destinations = [
    ['/dashboard', 'Overview'],
    ['/jobs', 'Find jobs'],
    ['/matches', 'Matches'],
    ['/applications', 'Applications'],
    ['/saved-jobs', 'Saved jobs'],
    ['/profile', 'Profile'],
] as const;

export function Portal({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    const { props, url } = usePage<Shared>();
    const logout = useForm({});
    const member = props.auth.user;
    const isAuth =
        !member &&
        ['/login', '/register', '/forgot-password', '/reset-password'].some(
            (path) => url.split('?')[0].startsWith(path),
        );
    const staffLinks: [string, string][] = [];
    if (
        props.auth.roles.some((role) => ['Admin', 'Manager'].includes(role)) &&
        props.auth.permissions.includes('manage skills')
    ) {
        staffLinks.push(['/staff/skills', 'Skill catalog']);
    }
    if (
        props.auth.roles.includes('Admin') &&
        props.auth.permissions.includes('manage user roles')
    ) {
        staffLinks.push(['/admin/users', 'User access']);
    }
    const links = (
        member
            ? [...destinations, ...staffLinks]
            : [
                  ['/jobs', 'Find jobs'],
                  ['/login', 'Sign in'],
                  ['/register', 'Create account'],
              ]
    ).map(([href, label]) => (
        <Link
            key={href}
            href={href}
            className="kt-nav-link"
            aria-current={
                (
                    href === '/jobs'
                        ? url.split('?')[0].startsWith('/jobs')
                        : url.split('?')[0] === href
                )
                    ? 'page'
                    : undefined
            }
        >
            <span className="kt-nav-marker" aria-hidden="true" />
            {label}
        </Link>
    ));
    return (
        <div className={member ? 'kt-shell kt-member' : 'kt-shell'}>
            <Head title={title} />
            <a className="kt-skip" href="#main">
                Skip to content
            </a>
            <header className="kt-header">
                <div className="kt-header-inner">
                    <Link
                        href={member ? '/dashboard' : '/'}
                        className="kt-logo"
                        aria-label="KoneKT home"
                    >
                        <Brand />
                    </Link>
                    <nav
                        aria-label="Main navigation"
                        className="kt-desktop-nav"
                    >
                        {links}
                    </nav>
                    {member && (
                        <div className="kt-member-summary">
                            <span className="kt-avatar" aria-hidden="true">
                                {member.name
                                    .split(' ')
                                    .map((part) => part[0])
                                    .slice(0, 2)
                                    .join('')}
                            </span>
                            <div>
                                <span>{member.name}</span>
                                <small>
                                    {member.role?.replaceAll('_', ' ')}
                                </small>
                            </div>
                            <Link href="/settings" className="kt-settings-link">
                                Settings
                            </Link>
                        </div>
                    )}
                    {member && (
                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                logout.post('/logout', { replace: true });
                            }}
                        >
                            <button
                                type="submit"
                                className="kt-text-button"
                                disabled={logout.processing}
                            >
                                Sign out
                            </button>
                        </form>
                    )}
                    <details className="kt-mobile-nav">
                        <summary>Menu</summary>
                        <nav aria-label="Mobile navigation">
                            {links}
                            {member && (
                                <Link href="/settings" className="kt-nav-link">
                                    Settings
                                </Link>
                            )}
                        </nav>
                    </details>
                </div>
            </header>
            <main id="main" className="kt-main">
                {props.flash?.success && (
                    <p className="kt-notice" role="status">
                        {props.flash.success}
                    </p>
                )}
                {isAuth ? (
                    <div className="kt-auth-layout">
                        {children}
                        <aside className="kt-auth-story">
                            <h2>Good work starts with a connection.</h2>
                            <p>
                                Discover roles that fit your experience, find
                                matches from your resume, and keep your
                                applications in one place.
                            </p>
                            <Link href="/jobs" className="kt-auth-explore">
                                Explore open roles <ArrowIcon />
                            </Link>
                        </aside>
                    </div>
                ) : (
                    children
                )}
            </main>
            <footer className="kt-footer">
                <span>KoneKT · Your next move, with purpose.</span>
                {member && <Link href="/settings">Account settings</Link>}
            </footer>
        </div>
    );
}

export function PageTitle({
    title,
    description,
    action,
}: {
    title: string;
    description?: string;
    action?: ReactNode;
}) {
    return (
        <div className="kt-page-heading">
            <div>
                <h1>{title}</h1>
                {description && <p>{description}</p>}
            </div>
            {action}
        </div>
    );
}

export function Field({
    label,
    error,
    id,
    ...props
}: InputHTMLAttributes<HTMLInputElement> & {
    label: string;
    error?: string;
    id: string;
}) {
    return (
        <div className="kt-field">
            <label htmlFor={id}>{label}</label>
            <input
                {...props}
                id={id}
                aria-invalid={!!error}
                aria-describedby={error ? `${id}-error` : undefined}
            />
            {error && (
                <p className="kt-error" id={`${id}-error`} role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

export function EmptyState({
    title,
    description,
    href,
    action,
}: {
    title: string;
    description: string;
    href: string;
    action: string;
}) {
    return (
        <div className="kt-empty">
            <h2>{title}</h2>
            <p>{description}</p>
            <Link className="kt-button" href={href}>
                {action}
            </Link>
        </div>
    );
}

export type Job = {
    id: number;
    title: string;
    company: string;
    location: string;
    workType: string;
    workArrangement: string;
    description: string;
    requirements: string | null;
    responsibilities: string | null;
    isOpen: boolean;
    salaryMin: string | null;
    salaryMax: string | null;
    currency: string | null;
    deadline: string | null;
    companyDescription: string | null;
};
export type Paginated<T> = {
    data: T[];
    total: number;
    from: number | null;
    to: number | null;
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};

export function Pagination({ page }: { page: Paginated<unknown> }) {
    if (page.last_page <= 1) return null;
    return (
        <nav className="kt-pagination" aria-label="Pagination">
            <span>
                Page {page.current_page} of {page.last_page}
            </span>
            <div>
                {page.prev_page_url && (
                    <Link
                        className="kt-button kt-button-secondary"
                        href={page.prev_page_url}
                    >
                        Previous
                    </Link>
                )}
                {page.next_page_url && (
                    <Link
                        className="kt-button kt-button-secondary"
                        href={page.next_page_url}
                    >
                        Next
                    </Link>
                )}
            </div>
        </nav>
    );
}

export function JobRow({
    job,
    query = '',
    children,
}: {
    job: Job;
    query?: string;
    children?: ReactNode;
}) {
    return (
        <article className="kt-job-row">
            <div className="kt-job-main">
                <div className="kt-company-identity">
                    <span className="kt-company-mark" aria-hidden="true">
                        {job.company
                            .split(' ')
                            .map((part) => part[0])
                            .slice(0, 2)
                            .join('')}
                    </span>
                    <p className="kt-meta">{job.company}</p>
                </div>
                <p className="kt-meta">{job.location}</p>
                <h2>
                    <Link href={`/jobs/${job.id}${query}`}>{job.title}</Link>
                </h2>
                <p>{job.description}</p>
                <div className="kt-job-meta">
                    <span>{job.workArrangement}</span>
                    <span>{job.workType}</span>
                    {!job.isOpen && <span>Applications closed</span>}
                </div>
            </div>
            <div className="kt-job-actions">
                {children}
                <Link className="kt-text-link" href={`/jobs/${job.id}${query}`}>
                    View role <ArrowIcon />
                </Link>
            </div>
        </article>
    );
}
