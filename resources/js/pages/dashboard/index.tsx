import { Link } from '@inertiajs/react';
import { PageTitle, Portal } from '@/components/portal';

type Props = {
    user: { name: string };
    profile: {
        headline: string | null;
        bio: string | null;
        location: string | null;
        phone: string | null;
        industry: string | null;
    } | null;
    profileCompletion: number;
    resume: { name: string; downloadUrl: string } | null;
    skills: unknown[];
    experience: unknown[];
    education: unknown[];
    stats: { applications: number; savedJobs: number; matches: number };
};
export default function Dashboard({
    user,
    profile,
    profileCompletion,
    resume,
    skills,
    experience,
    education,
    stats,
}: Props) {
    const checklist = [
        {
            section: 'details',
            title: 'Introduce yourself',
            description: 'Headline, summary, location, phone, and industry',
            done: !!(
                profile?.headline &&
                profile.bio &&
                profile.location &&
                profile.phone &&
                profile.industry
            ),
        },
        {
            section: 'resume',
            title: 'Upload your resume',
            description: 'A text-based PDF for matching and applications',
            done: !!resume,
        },
        {
            section: 'experience',
            title: 'Add your experience',
            description: 'The roles and projects behind your skills',
            done: experience.length > 0,
        },
        {
            section: 'education',
            title: 'Add your education',
            description: 'Your qualifications and learning',
            done: education.length > 0,
        },
        {
            section: 'skills',
            title: 'List your skills',
            description: 'Show what you bring to your next role',
            done: skills.length > 0,
        },
    ];
    return (
        <Portal title="Your overview">
            <PageTitle
                title={`Welcome back, ${user.name.split(' ')[0]}`}
                description="Your search, your progress, and what to do next."
                action={
                    <Link className="kt-button" href="/jobs">
                        Find your next role
                    </Link>
                }
            />
            <div className="kt-stat-links">
                <Link href="/applications">
                    <strong>{stats.applications}</strong>
                    <span>Applications →</span>
                </Link>
                <Link href="/saved-jobs">
                    <strong>{stats.savedJobs}</strong>
                    <span>Saved jobs →</span>
                </Link>
                <Link href="/matches">
                    <strong>{stats.matches}</strong>
                    <span>Resume matches →</span>
                </Link>
            </div>
            <div className="kt-columns">
                <section>
                    <div className="kt-section-heading">
                        <h2 className="text-2xl font-semibold">
                            Make your profile ready
                        </h2>
                        <span className="kt-meta">
                            {profileCompletion}% complete
                        </span>
                    </div>
                    <progress
                        className="kt-progress"
                        max={100}
                        value={profileCompletion}
                        aria-label="Profile completion"
                    />
                    <div className="kt-checklist">
                        {checklist.map((item) => (
                            <Link
                                key={item.section}
                                href={`/profile?section=${item.section}`}
                            >
                                <div>
                                    <h3 className="font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="kt-meta mt-1">
                                        {item.description}
                                    </p>
                                </div>
                                <span>{item.done ? 'Review' : 'Add'} →</span>
                            </Link>
                        ))}
                    </div>
                </section>
                <aside className="kt-sidebar">
                    <h2>
                        {resume ? 'Your next step' : 'Start with your resume'}
                    </h2>
                    <p>
                        {resume
                            ? 'Check your matches for relevant opportunities, or explore all open roles at your own pace.'
                            : 'Upload your resume once to find keyword matches and attach it when you apply.'}
                    </p>
                    <Link
                        className="kt-button"
                        href={resume ? '/matches' : '/profile?section=resume'}
                    >
                        {resume ? 'Review matches' : 'Upload resume'}
                    </Link>
                    {resume && (
                        <p className="mt-5 break-words">
                            Current resume: {resume.name}
                        </p>
                    )}
                    <Link className="kt-text-link mt-3" href="/profile">
                        Edit your profile
                    </Link>
                </aside>
            </div>
        </Portal>
    );
}
