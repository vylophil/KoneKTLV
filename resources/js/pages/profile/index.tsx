import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Field, PageTitle, Portal } from '@/components/portal';

type Details = {
    headline: string;
    bio: string;
    location: string;
    phone: string;
    website: string;
    linkedin_url: string;
    github_url: string;
    industry: string;
    years_of_experience: number;
};
type Career = {
    id: number;
    company_name?: string;
    job_title?: string;
    location?: string;
    institution?: string;
    degree?: string;
    field_of_study?: string;
    grade?: string;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    description: string | null;
};
type Skill = { id: number; name: string; proficiency_level: string };
type ProfileProps = {
    section: string;
    profile: Partial<Details> | null;
    resume: { name: string; downloadUrl: string } | null;
    experience: Career[];
    education: Career[];
    skills: Skill[];
    skillOptions: { id: number; name: string }[];
};
const sections = [
    ['details', 'Personal details'],
    ['experience', 'Experience'],
    ['education', 'Education'],
    ['skills', 'Skills'],
    ['resume', 'Resume'],
] as const;

function DetailsForm({ profile }: { profile: ProfileProps['profile'] }) {
    const form = useForm<Details>({
        headline: profile?.headline ?? '',
        bio: profile?.bio ?? '',
        location: profile?.location ?? '',
        phone: profile?.phone ?? '',
        website: profile?.website ?? '',
        linkedin_url: profile?.linkedin_url ?? '',
        github_url: profile?.github_url ?? '',
        industry: profile?.industry ?? '',
        years_of_experience: profile?.years_of_experience ?? 0,
    });
    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();
                form.put('/profile', { preserveScroll: true });
            }}
        >
            <div className="kt-form-grid">
                {(
                    [
                        {
                            key: 'headline',
                            label: 'Professional headline',
                            max: 255,
                        },
                        { key: 'location', label: 'Location', max: 255 },
                        { key: 'industry', label: 'Industry', max: 255 },
                        { key: 'phone', label: 'Phone number', max: 20 },
                        { key: 'website', label: 'Website URL', max: 500 },
                        {
                            key: 'linkedin_url',
                            label: 'LinkedIn URL',
                            max: 500,
                        },
                        { key: 'github_url', label: 'GitHub URL', max: 500 },
                    ] as const
                ).map(({ key, label, max }) => (
                    <Field
                        key={key}
                        id={key}
                        label={label}
                        type={
                            key.endsWith('url') || key === 'website'
                                ? 'url'
                                : key === 'phone'
                                  ? 'tel'
                                  : 'text'
                        }
                        maxLength={max}
                        value={form.data[key]}
                        error={form.errors[key]}
                        onChange={(event) =>
                            form.setData(key, event.target.value)
                        }
                    />
                ))}
                <Field
                    id="years"
                    label="Years of experience"
                    type="number"
                    min={0}
                    max={80}
                    value={form.data.years_of_experience}
                    error={form.errors.years_of_experience}
                    onChange={(event) =>
                        form.setData(
                            'years_of_experience',
                            Number(event.target.value),
                        )
                    }
                />
            </div>
            <div className="kt-field mt-6">
                <label htmlFor="bio">Professional summary</label>
                <textarea
                    id="bio"
                    value={form.data.bio}
                    maxLength={10000}
                    aria-describedby={form.errors.bio ? 'bio-error' : undefined}
                    onChange={(event) =>
                        form.setData('bio', event.target.value)
                    }
                />
                {form.errors.bio && (
                    <p className="kt-error" id="bio-error" role="alert">
                        {form.errors.bio}
                    </p>
                )}
            </div>
            <div className="kt-form-actions">
                <button className="kt-button" disabled={form.processing}>
                    {form.processing ? 'Saving…' : 'Save profile details'}
                </button>
                {form.recentlySuccessful && <span role="status">Saved</span>}
            </div>
        </form>
    );
}

function CareerForm({
    kind,
    item,
    onClose,
}: {
    kind: 'experience' | 'education';
    item?: Career;
    onClose: () => void;
}) {
    const form = useForm({
        company_name: item?.company_name ?? '',
        job_title: item?.job_title ?? '',
        location: item?.location ?? '',
        institution: item?.institution ?? '',
        degree: item?.degree ?? 'bachelors',
        field_of_study: item?.field_of_study ?? '',
        grade: item?.grade ?? '',
        start_date: item?.start_date ?? '',
        end_date: item?.end_date ?? '',
        is_current: item?.is_current ?? false,
        description: item?.description ?? '',
    });
    const submit = () => {
        const url = `/profile/career/${kind}`;
        const options = { preserveScroll: true, onSuccess: onClose };
        if (item) form.put(`${url}/${item.id}`, options);
        else form.post(url, options);
    };
    return (
        <form
            className="kt-section"
            onSubmit={(event) => {
                event.preventDefault();
                submit();
            }}
        >
            <h2>
                {item ? 'Edit' : 'Add'} {kind}
            </h2>
            <div className="kt-form-grid">
                {kind === 'experience' ? (
                    <>
                        <Field
                            label="Company"
                            id="company_name"
                            value={form.data.company_name}
                            maxLength={255}
                            required
                            error={form.errors.company_name}
                            onChange={(event) =>
                                form.setData('company_name', event.target.value)
                            }
                        />
                        <Field
                            label="Job title"
                            id="job_title"
                            value={form.data.job_title}
                            maxLength={255}
                            required
                            error={form.errors.job_title}
                            onChange={(event) =>
                                form.setData('job_title', event.target.value)
                            }
                        />
                        <Field
                            label="Location"
                            id="career_location"
                            value={form.data.location}
                            maxLength={255}
                            error={form.errors.location}
                            onChange={(event) =>
                                form.setData('location', event.target.value)
                            }
                        />
                    </>
                ) : (
                    <>
                        <Field
                            label="Institution"
                            id="institution"
                            value={form.data.institution}
                            maxLength={255}
                            required
                            error={form.errors.institution}
                            onChange={(event) =>
                                form.setData('institution', event.target.value)
                            }
                        />
                        <div className="kt-field">
                            <label htmlFor="degree">Qualification</label>
                            <select
                                id="degree"
                                value={form.data.degree}
                                onChange={(event) =>
                                    form.setData('degree', event.target.value)
                                }
                            >
                                {[
                                    ['high_school', 'High school'],
                                    ['associate', 'Associate'],
                                    ['bachelors', 'Bachelor’s degree'],
                                    ['masters', 'Master’s degree'],
                                    ['doctorate', 'Doctorate'],
                                    ['certification', 'Certification'],
                                    ['other', 'Other'],
                                ].map(([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                            {form.errors.degree && (
                                <p className="kt-error" role="alert">
                                    {form.errors.degree}
                                </p>
                            )}
                        </div>
                        <Field
                            label="Field of study"
                            id="field_of_study"
                            value={form.data.field_of_study}
                            maxLength={255}
                            error={form.errors.field_of_study}
                            onChange={(event) =>
                                form.setData(
                                    'field_of_study',
                                    event.target.value,
                                )
                            }
                        />
                        <Field
                            label="Grade (optional)"
                            id="grade"
                            value={form.data.grade}
                            maxLength={50}
                            error={form.errors.grade}
                            onChange={(event) =>
                                form.setData('grade', event.target.value)
                            }
                        />
                    </>
                )}
                <Field
                    id="start_date"
                    label="Start date"
                    type="date"
                    value={form.data.start_date}
                    required
                    error={form.errors.start_date}
                    onChange={(event) =>
                        form.setData('start_date', event.target.value)
                    }
                />
                <Field
                    id="end_date"
                    label="End date (optional)"
                    type="date"
                    value={form.data.end_date}
                    disabled={form.data.is_current}
                    error={form.errors.end_date}
                    onChange={(event) =>
                        form.setData('end_date', event.target.value)
                    }
                />
            </div>
            <label className="mt-5 flex min-h-11 items-center gap-3 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.is_current}
                    onChange={(event) => {
                        form.setData('is_current', event.target.checked);
                        if (event.target.checked) form.setData('end_date', '');
                    }}
                />
                {kind === 'experience'
                    ? 'I currently work here'
                    : 'I am currently studying here'}
            </label>
            <div className="kt-field mt-5">
                <label htmlFor="career-description">
                    Description (optional)
                </label>
                <textarea
                    id="career-description"
                    maxLength={10000}
                    value={form.data.description}
                    onChange={(event) =>
                        form.setData('description', event.target.value)
                    }
                />
                {form.errors.description && (
                    <p className="kt-error" role="alert">
                        {form.errors.description}
                    </p>
                )}
            </div>
            <div className="kt-form-actions">
                <button className="kt-button" disabled={form.processing}>
                    {form.processing ? 'Saving…' : 'Save entry'}
                </button>
                <button
                    type="button"
                    className="kt-text-button"
                    onClick={onClose}
                >
                    Cancel
                </button>
            </div>
        </form>
    );
}

function CareerSection({
    kind,
    items,
}: {
    kind: 'experience' | 'education';
    items: Career[];
}) {
    const [editing, setEditing] = useState<number | 'new' | null>(null);
    const remove = useForm({});
    return (
        <>
            <div className="kt-section-heading">
                <h2 className="text-2xl font-semibold capitalize">{kind}</h2>
                <button
                    type="button"
                    className="kt-button kt-button-secondary"
                    onClick={() => setEditing('new')}
                >
                    Add {kind}
                </button>
            </div>
            {items.length === 0 && editing === null && (
                <p className="kt-meta mt-5">
                    Add your {kind} to show the experience behind your resume.
                </p>
            )}
            {items.map((item) => (
                <div className="kt-entry" key={item.id}>
                    <h3 className="font-semibold">
                        {kind === 'experience'
                            ? item.job_title
                            : item.institution}
                    </h3>
                    <p className="kt-meta">
                        {kind === 'experience'
                            ? item.company_name
                            : item.degree?.replaceAll('_', ' ')}{' '}
                        · {item.start_date} –{' '}
                        {item.is_current
                            ? 'Present'
                            : item.end_date || 'End date not added'}
                    </p>
                    {item.description && (
                        <p className="kt-prose mt-3">{item.description}</p>
                    )}
                    <div className="kt-form-actions">
                        <button
                            className="kt-text-button"
                            type="button"
                            onClick={() => setEditing(item.id)}
                        >
                            Edit entry
                        </button>
                        <button
                            className="kt-text-button"
                            type="button"
                            disabled={remove.processing}
                            onClick={() => {
                                if (
                                    window.confirm(
                                        'Remove this entry from your profile?',
                                    )
                                )
                                    remove.delete(
                                        `/profile/career/${kind}/${item.id}`,
                                        { preserveScroll: true },
                                    );
                            }}
                        >
                            Remove entry
                        </button>
                    </div>
                </div>
            ))}
            {editing !== null && (
                <CareerForm
                    key={editing}
                    kind={kind}
                    item={items.find((item) => item.id === editing)}
                    onClose={() => setEditing(null)}
                />
            )}
        </>
    );
}

function SkillsSection({
    skills,
    options,
}: {
    skills: Skill[];
    options: ProfileProps['skillOptions'];
}) {
    const form = useForm({ skill_id: '', proficiency_level: 'intermediate' });
    const remove = useForm({});
    const existing = new Set(skills.map((skill) => skill.name));
    return (
        <>
            <form
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post('/profile/skills', {
                        preserveScroll: true,
                        onSuccess: () => form.reset('skill_id'),
                    });
                }}
            >
                <div className="kt-form-grid">
                    <div className="kt-field">
                        <label htmlFor="skill">Add a skill</label>
                        <select
                            id="skill"
                            required
                            value={form.data.skill_id}
                            onChange={(event) =>
                                form.setData('skill_id', event.target.value)
                            }
                        >
                            <option value="">Choose a skill</option>
                            {options
                                .filter((skill) => !existing.has(skill.name))
                                .map((skill) => (
                                    <option key={skill.id} value={skill.id}>
                                        {skill.name}
                                    </option>
                                ))}
                        </select>
                        {form.errors.skill_id && (
                            <p className="kt-error" role="alert">
                                {form.errors.skill_id}
                            </p>
                        )}
                    </div>
                    <div className="kt-field">
                        <label htmlFor="proficiency">Your proficiency</label>
                        <select
                            id="proficiency"
                            value={form.data.proficiency_level}
                            onChange={(event) =>
                                form.setData(
                                    'proficiency_level',
                                    event.target.value,
                                )
                            }
                        >
                            {[
                                'beginner',
                                'intermediate',
                                'advanced',
                                'expert',
                            ].map((level) => (
                                <option key={level} value={level}>
                                    {level}
                                </option>
                            ))}
                        </select>
                        {form.errors.proficiency_level && (
                            <p className="kt-error" role="alert">
                                {form.errors.proficiency_level}
                            </p>
                        )}
                    </div>
                </div>
                <div className="kt-form-actions">
                    <button className="kt-button" disabled={form.processing}>
                        Add skill
                    </button>
                </div>
            </form>
            <div className="mt-8">
                {skills.map((skill) => (
                    <div
                        className="kt-entry flex items-center justify-between gap-4"
                        key={skill.id}
                    >
                        <div>
                            <h3 className="font-semibold">{skill.name}</h3>
                            <p className="kt-meta capitalize">
                                {skill.proficiency_level}
                            </p>
                        </div>
                        <button
                            className="kt-text-button"
                            disabled={remove.processing}
                            onClick={() =>
                                remove.delete(`/profile/skills/${skill.id}`, {
                                    preserveScroll: true,
                                })
                            }
                        >
                            Remove {skill.name}
                        </button>
                    </div>
                ))}
            </div>
        </>
    );
}

function ResumeSection({ resume }: { resume: ProfileProps['resume'] }) {
    const form = useForm<{ resume: File | null }>({ resume: null });
    return (
        <section className="max-w-2xl">
            <h2 className="text-2xl font-semibold">Your resume</h2>
            <p className="kt-prose mt-3">
                Upload a text-based PDF, up to 5 MB. It stays private and is
                used for keyword matching inside KoneKT. Applications happen on
                the source website; KoneKT does not send your resume there.
            </p>
            {resume && (
                <div className="kt-section mt-6">
                    <p className="font-semibold break-words">{resume.name}</p>
                    <a className="kt-text-link" href={resume.downloadUrl}>
                        Download current resume
                    </a>
                </div>
            )}
            <form
                className="mt-6"
                encType="multipart/form-data"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post('/profile/resume', { forceFormData: true });
                }}
            >
                <Field
                    id="resume-file"
                    label={
                        resume ? 'Replace your resume' : 'Choose your resume'
                    }
                    type="file"
                    accept="application/pdf,.pdf"
                    required
                    error={form.errors.resume}
                    onChange={(event) =>
                        form.setData('resume', event.target.files?.[0] ?? null)
                    }
                />
                <p className="kt-meta mt-3">
                    Scanned image-only PDFs cannot be matched. Use a PDF with
                    selectable text.
                </p>
                {form.progress && (
                    <p className="kt-meta mt-3" role="status">
                        Uploading {form.progress.percentage}%
                    </p>
                )}
                <div className="kt-form-actions">
                    <button
                        className="kt-button"
                        disabled={form.processing || !form.data.resume}
                    >
                        {form.processing
                            ? 'Uploading…'
                            : 'Upload and find matches'}
                    </button>
                    <Link className="kt-text-link" href="/matches">
                        View matches
                    </Link>
                </div>
            </form>
        </section>
    );
}

export default function Profile(props: ProfileProps) {
    return (
        <Portal title="Your profile">
            <PageTitle
                title="Your profile"
                description="Keep your experience up to date and make your next application easier."
            />
            <nav className="kt-tabs" aria-label="Profile sections">
                {sections.map(([key, label]) => (
                    <Link
                        key={key}
                        href={`/profile?section=${key}`}
                        aria-current={
                            props.section === key ? 'page' : undefined
                        }
                    >
                        {label}
                    </Link>
                ))}
            </nav>
            <div className="max-w-3xl">
                {props.section === 'details' && (
                    <DetailsForm profile={props.profile} />
                )}
                {props.section === 'experience' && (
                    <CareerSection kind="experience" items={props.experience} />
                )}
                {props.section === 'education' && (
                    <CareerSection kind="education" items={props.education} />
                )}
                {props.section === 'skills' && (
                    <SkillsSection
                        skills={props.skills}
                        options={props.skillOptions}
                    />
                )}
                {props.section === 'resume' && (
                    <ResumeSection resume={props.resume} />
                )}
            </div>
        </Portal>
    );
}
