import { Head, Link, useForm } from '@inertiajs/react';

type DashboardProps = Readonly<{
    user: {
        name: string;
        email: string;
        role: string;
    };
    profile: {
        headline: string | null;
        bio: string | null;
        location: string | null;
        phone: string | null;
        website: string | null;
        linkedinUrl: string | null;
        githubUrl: string | null;
        industry: string | null;
        yearsOfExperience: number | null;
    } | null;
    profileCompletion: number;
    resume: { name: string; downloadUrl: string } | null;
    skills: { name: string; proficiency: string | null }[];
    experience: {
        id: number;
        company: string;
        jobTitle: string;
        location: string | null;
        startDate: string | null;
        endDate: string | null;
        description: string | null;
    }[];
    education: {
        id: number;
        institution: string;
        degree: string;
        fieldOfStudy: string | null;
        startDate: string | null;
        endDate: string | null;
        grade: string | null;
    }[];
    stats: {
        applications: number;
        savedJobs: number;
        matches: number;
    };
}>;

export default function Dashboard({
    user,
    profile,
    profileCompletion,
    resume,
    skills,
    experience,
    education,
    stats,
}: DashboardProps) {
    const logout = useForm({});
    const deleteAccount = useForm({ username: '' });

    return (
        <>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-[#f5f6f0] text-[#17251e]">
                <header className="border-b border-[#dce2d9]">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between gap-5 px-6 py-5 lg:px-10">
                        <Link
                            className="text-2xl font-semibold tracking-tight"
                            href="/"
                            aria-label="KoneKT home"
                        >
                            Kone<span className="text-[#e45e42]">K</span>T
                        </Link>
                        <div className="flex items-center gap-5 text-sm font-medium">
                            <Link
                                className="text-[#52635a] hover:text-[#183b2d]"
                                href="/#jobs"
                            >
                                Find a job
                            </Link>
                            <Link
                                className="border-b-2 border-[#e45e42] py-2 text-[#183b2d]"
                                href="/dashboard"
                                aria-current="page"
                            >
                                Dashboard
                            </Link>
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    logout.post('/logout');
                                }}
                            >
                                <button
                                    className="min-h-9 border border-[#cbd5cb] px-3 text-[#52635a] transition-colors hover:bg-[#e7eee5] hover:text-[#183b2d] disabled:opacity-60"
                                    type="submit"
                                    disabled={logout.processing}
                                >
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </nav>
                </header>

                <main className="mx-auto max-w-7xl px-6 py-10 lg:px-10 lg:py-14">
                    <div className="flex flex-wrap items-end justify-between gap-5 border-b border-[#dce2d9] pb-8">
                        <div>
                            <p className="text-sm font-semibold text-[#b64c35]">
                                {user.role}
                            </p>
                            <h1 className="mt-2 text-4xl font-semibold tracking-tight">
                                Your dashboard
                            </h1>
                            <p className="mt-2 text-[#52635a]">
                                {user.name} · {user.email}
                            </p>
                        </div>
                        <Link
                            className="inline-flex min-h-11 items-center bg-[#183b2d] px-5 font-semibold text-white transition-colors hover:bg-[#24543f]"
                            href="/#jobs"
                        >
                            Browse jobs
                        </Link>
                    </div>

                    <section className="grid gap-8 border-b border-[#dce2d9] py-8 md:grid-cols-2 md:items-center">
                        <div>
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="font-semibold">Profile completion</h2>
                                <p className="text-sm font-semibold text-[#183b2d]">
                                    {profileCompletion}%
                                </p>
                            </div>
                            <progress
                                className="mt-3 h-2 w-full accent-[#e45e42]"
                                max={100}
                                value={profileCompletion}
                                aria-label="Profile completion"
                            />
                            <p className="mt-2 text-sm text-[#52635a]">
                                Add profile details, skills, and career history
                                to help employers understand your experience.
                            </p>
                        </div>

                        <dl className="grid grid-cols-3 divide-x divide-[#dce2d9] border-y border-[#dce2d9] py-4 text-center">
                            <Metric label="Applications" value={stats.applications} />
                            <Metric label="Saved jobs" value={stats.savedJobs} />
                            <Metric label="Resume matches" value={stats.matches} />
                        </dl>
                    </section>

                    <div className="grid gap-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(280px,0.7fr)] lg:gap-16">
                        <div>
                            <section className="border-b border-[#dce2d9] py-8">
                                <div className="flex flex-wrap items-baseline justify-between gap-3">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Profile
                                    </h2>
                                    <p className="text-sm text-[#52635a]">
                                        Personal and professional information
                                    </p>
                                </div>
                                {profile ? (
                                    <>
                                        <h3 className="mt-6 text-lg font-semibold">
                                            {profile.headline || 'Add a professional headline'}
                                        </h3>
                                        <p className="mt-2 max-w-3xl whitespace-pre-line text-sm leading-6 text-[#52635a]">
                                            {profile.bio || 'A short professional summary will help employers get to know you.'}
                                        </p>
                                        <dl className="mt-6 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                                            <Detail label="Location" value={profile.location} />
                                            <Detail label="Phone" value={profile.phone} />
                                            <Detail label="Industry" value={profile.industry} />
                                            <Detail
                                                label="Experience"
                                                value={
                                                    profile.yearsOfExperience === null
                                                        ? null
                                                        : `${profile.yearsOfExperience} years`
                                                }
                                            />
                                            <Detail label="Website" value={profile.website} link />
                                            <Detail label="LinkedIn" value={profile.linkedinUrl} link />
                                            <Detail label="GitHub" value={profile.githubUrl} link />
                                        </dl>
                                    </>
                                ) : (
                                    <p className="mt-5 text-sm leading-6 text-[#52635a]">
                                        Your profile details have not been added yet.
                                    </p>
                                )}
                            </section>

                            <section className="border-b border-[#dce2d9] py-8">
                                <div className="flex flex-wrap items-baseline justify-between gap-3">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Experience
                                    </h2>
                                    <span className="text-sm text-[#52635a]">
                                        {experience.length} entries
                                    </span>
                                </div>
                                {experience.length > 0 ? (
                                    <div className="mt-5 divide-y divide-[#e4e9e2]">
                                        {experience.map((item) => (
                                            <article className="py-5 first:pt-0" key={item.id}>
                                                <h3 className="font-semibold">{item.jobTitle}</h3>
                                                <p className="mt-1 text-sm text-[#52635a]">
                                                    {item.company}
                                                    {item.location ? ` · ${item.location}` : ''}
                                                </p>
                                                <p className="mt-1 text-xs text-[#718078]">
                                                    {item.startDate || 'Start date not set'} – {item.endDate || 'Present'}
                                                </p>
                                                {item.description && (
                                                    <p className="mt-3 whitespace-pre-line text-sm leading-6 text-[#52635a]">
                                                        {item.description}
                                                    </p>
                                                )}
                                            </article>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="mt-5 text-sm text-[#52635a]">
                                        No experience has been added yet.
                                    </p>
                                )}
                            </section>

                            <section className="py-8">
                                <div className="flex flex-wrap items-baseline justify-between gap-3">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Education
                                    </h2>
                                    <span className="text-sm text-[#52635a]">
                                        {education.length} entries
                                    </span>
                                </div>
                                {education.length > 0 ? (
                                    <div className="mt-5 divide-y divide-[#e4e9e2]">
                                        {education.map((item) => (
                                            <article className="py-5 first:pt-0" key={item.id}>
                                                <h3 className="font-semibold">{item.institution}</h3>
                                                <p className="mt-1 text-sm text-[#52635a]">
                                                    {item.degree}
                                                    {item.fieldOfStudy ? ` · ${item.fieldOfStudy}` : ''}
                                                </p>
                                                <p className="mt-1 text-xs text-[#718078]">
                                                    {item.startDate || 'Start year not set'} – {item.endDate || 'Present'}
                                                </p>
                                                {item.grade && (
                                                    <p className="mt-2 text-sm text-[#52635a]">
                                                        Grade: {item.grade}
                                                    </p>
                                                )}
                                            </article>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="mt-5 text-sm text-[#52635a]">
                                        No education has been added yet.
                                    </p>
                                )}
                            </section>
                        </div>

                        <aside>
                            <section className="border-b border-[#dce2d9] py-8 lg:pt-8">
                                <div className="flex items-baseline justify-between gap-3">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Resume
                                    </h2>
                                    <Link
                                        className="text-sm font-semibold text-[#183b2d] underline underline-offset-2"
                                        href="/#resume-upload"
                                    >
                                        {resume ? 'Replace' : 'Upload'}
                                    </Link>
                                </div>
                                {resume ? (
                                    <div className="mt-5 border-l-2 border-[#e45e42] bg-[#e7eee5] px-4 py-4">
                                        <p className="break-all font-medium">{resume.name}</p>
                                        <a
                                            className="mt-3 inline-flex text-sm font-semibold text-[#183b2d] underline underline-offset-2"
                                            href={resume.downloadUrl}
                                        >
                                            Download PDF
                                        </a>
                                    </div>
                                ) : (
                                    <p className="mt-5 text-sm leading-6 text-[#52635a]">
                                        Add a text-based PDF to get job suggestions matched to your experience.
                                    </p>
                                )}
                            </section>

                            <section className="border-b border-[#dce2d9] py-8">
                                <div className="flex flex-wrap items-baseline justify-between gap-3">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        Skills
                                    </h2>
                                    <span className="text-sm text-[#52635a]">
                                        {skills.length} listed
                                    </span>
                                </div>
                                {skills.length > 0 ? (
                                    <ul className="mt-5 divide-y divide-[#e4e9e2]">
                                        {skills.map((skill) => (
                                            <li
                                                className="flex items-center justify-between gap-3 py-3 first:pt-0"
                                                key={skill.name}
                                            >
                                                <span className="font-medium">{skill.name}</span>
                                                <span className="text-sm capitalize text-[#52635a]">
                                                    {skill.proficiency || 'Level not set'}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="mt-5 text-sm leading-6 text-[#52635a]">
                                        Add skills to help your resume matches find relevant roles.
                                    </p>
                                )}
                            </section>
                        </aside>
                    </div>

                    <section className="mt-10 border-t border-[#d7bdb5] pt-8">
                        <h2 className="text-xl font-semibold text-[#8d352b]">
                            Account controls
                        </h2>
                        <details className="mt-4 border border-[#d7bdb5] bg-white px-5 py-4">
                            <summary className="cursor-pointer font-semibold text-[#8d352b]">
                                Delete account
                            </summary>
                            <div className="mt-4 max-w-2xl">
                                <p className="text-sm leading-6 text-[#52635a]">
                                    This permanently deletes your account,
                                    profile, resume, and related activity. This
                                    cannot be undone.
                                </p>
                                <form
                                    className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end"
                                    onSubmit={(event) => {
                                        event.preventDefault();
                                        deleteAccount.delete('/account');
                                    }}
                                >
                                    <div className="min-w-0 flex-1">
                                        <label
                                            className="mb-2 block text-sm font-medium"
                                            htmlFor="delete-account-username"
                                        >
                                            Type your username (email) exactly
                                        </label>
                                        <input
                                            id="delete-account-username"
                                            autoComplete="off"
                                            spellCheck={false}
                                            value={deleteAccount.data.username}
                                            onChange={(event) =>
                                                deleteAccount.setData(
                                                    'username',
                                                    event.target.value,
                                                )
                                            }
                                            className="min-h-11 w-full border border-[#cbd5cb] px-3 outline-none focus:border-[#8d352b]"
                                            placeholder={user.email}
                                            required
                                        />
                                        {deleteAccount.errors.username && (
                                            <p
                                                className="mt-2 text-sm text-[#a6382a]"
                                                role="alert"
                                            >
                                                {deleteAccount.errors.username}
                                            </p>
                                        )}
                                    </div>
                                    <button
                                        className="min-h-11 shrink-0 bg-[#8d352b] px-4 font-semibold text-white transition-colors hover:bg-[#74291f] disabled:cursor-not-allowed disabled:opacity-50"
                                        type="submit"
                                        disabled={
                                            deleteAccount.processing ||
                                            deleteAccount.data.username !== user.email
                                        }
                                    >
                                        {deleteAccount.processing
                                            ? 'Deleting account...'
                                            : 'Delete my account'}
                                    </button>
                                </form>
                            </div>
                        </details>
                    </section>
                </main>
            </div>
        </>
    );
}

function Metric({ label, value }: Readonly<{ label: string; value: number }>) {
    return (
        <div className="px-2">
            <dd className="text-2xl font-semibold text-[#183b2d]">{value}</dd>
            <dt className="mt-1 text-xs text-[#52635a]">{label}</dt>
        </div>
    );
}

function Detail({
    label,
    value,
    link = false,
}: Readonly<{ label: string; value: string | null; link?: boolean }>) {
    return (
        <div>
            <dt className="text-xs font-semibold text-[#52635a]">{label}</dt>
            <dd className="mt-1 break-words text-sm">
                {value && link ? (
                    <a
                        className="text-[#183b2d] underline underline-offset-2"
                        href={value}
                        target="_blank"
                        rel="noreferrer"
                    >
                        {value}
                    </a>
                ) : (
                    value || 'Not added'
                )}
            </dd>
        </div>
    );
}