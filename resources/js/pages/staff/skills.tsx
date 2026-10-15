import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Field, PageTitle, Pagination, Portal } from '@/components/portal';
import type { Paginated } from '@/components/portal';

type Skill = { id: number; name: string; category: string | null };

export default function SkillCatalog({
    skills,
    search,
}: {
    skills: Paginated<Skill>;
    search: string;
}) {
    const create = useForm({ name: '', category: '' });

    return (
        <Portal title="Skill catalog">
            <PageTitle
                title="Skill catalog"
                description="Maintain the shared skills members use in their profiles and job matches."
            />
            <section
                className="kt-access-card mb-8"
                aria-labelledby="add-skill"
            >
                <h2 id="add-skill">Add a skill</h2>
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        create.post('/staff/skills', {
                            preserveScroll: true,
                            onSuccess: () => create.reset(),
                        });
                    }}
                >
                    <div className="kt-form-grid mt-5">
                        <Field
                            id="skill-name"
                            label="Skill name"
                            required
                            maxLength={100}
                            value={create.data.name}
                            error={create.errors.name}
                            onChange={(event) =>
                                create.setData('name', event.target.value)
                            }
                        />
                        <Field
                            id="skill-category"
                            label="Category (optional)"
                            maxLength={100}
                            value={create.data.category}
                            error={create.errors.category}
                            onChange={(event) =>
                                create.setData('category', event.target.value)
                            }
                        />
                    </div>
                    <button
                        className="kt-button mt-5"
                        disabled={create.processing}
                    >
                        {create.processing ? 'Adding...' : 'Add skill'}
                    </button>
                </form>
            </section>
            <form
                action="/staff/skills"
                method="get"
                className="kt-home-search mb-8"
            >
                <div className="min-w-0 flex-1">
                    <Field
                        id="skill-search"
                        name="search"
                        label="Search the catalog"
                        defaultValue={search}
                        placeholder="Skill name or category"
                        maxLength={100}
                        type="search"
                    />
                </div>
                <button className="kt-button self-end" type="submit">
                    Search
                </button>
            </form>
            <p className="kt-meta mb-4">{skills.total} skills</p>
            {skills.data.length === 0 && (
                <p className="kt-empty">No skills match your search.</p>
            )}
            {skills.data.map((skill) => (
                <SkillEntry key={skill.id} skill={skill} />
            ))}
            <Pagination page={skills} />
        </Portal>
    );
}

function SkillEntry({ skill }: { skill: Skill }) {
    const [editing, setEditing] = useState(false);
    const [confirming, setConfirming] = useState(false);
    const form = useForm({ name: skill.name, category: skill.category ?? '' });
    const remove = useForm({});

    if (editing) {
        return (
            <form
                className="kt-access-card mb-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.put(`/staff/skills/${skill.id}`, {
                        preserveScroll: true,
                        onSuccess: () => setEditing(false),
                    });
                }}
            >
                <h2>Edit {skill.name}</h2>
                <div className="kt-form-grid mt-5">
                    <Field
                        id={`skill-name-${skill.id}`}
                        label="Skill name"
                        required
                        maxLength={100}
                        value={form.data.name}
                        error={form.errors.name}
                        onChange={(event) =>
                            form.setData('name', event.target.value)
                        }
                    />
                    <Field
                        id={`skill-category-${skill.id}`}
                        label="Category (optional)"
                        maxLength={100}
                        value={form.data.category}
                        error={form.errors.category}
                        onChange={(event) =>
                            form.setData('category', event.target.value)
                        }
                    />
                </div>
                <div className="kt-form-actions">
                    <button className="kt-button" disabled={form.processing}>
                        Save skill
                    </button>
                    <button
                        type="button"
                        className="kt-text-button"
                        disabled={form.processing}
                        onClick={() => {
                            form.setData({
                                name: skill.name,
                                category: skill.category ?? '',
                            });
                            form.clearErrors();
                            setEditing(false);
                        }}
                    >
                        Cancel
                    </button>
                </div>
            </form>
        );
    }

    return (
        <article className="kt-access-row">
            <div className="min-w-0">
                <h2 className="font-semibold">{skill.name}</h2>
                <p className="kt-meta">{skill.category || 'No category'}</p>
                {confirming && (
                    <p className="kt-error mt-3" role="status">
                        Remove {skill.name}? This also removes it from member
                        profiles and job requirements.
                    </p>
                )}
            </div>
            <div className="flex flex-wrap gap-4">
                {confirming ? (
                    <>
                        <button
                            className="kt-button kt-button-danger"
                            disabled={remove.processing}
                            onClick={() =>
                                remove.delete(`/staff/skills/${skill.id}`, {
                                    preserveScroll: true,
                                })
                            }
                        >
                            Confirm removal
                        </button>
                        <button
                            className="kt-text-button"
                            disabled={remove.processing}
                            onClick={() => setConfirming(false)}
                        >
                            Cancel
                        </button>
                    </>
                ) : (
                    <>
                        <button
                            className="kt-text-button"
                            onClick={() => setEditing(true)}
                            aria-label={`Edit ${skill.name}`}
                        >
                            Edit
                        </button>
                        <button
                            className="kt-text-button"
                            onClick={() => setConfirming(true)}
                            aria-label={`Remove ${skill.name}`}
                        >
                            Remove
                        </button>
                    </>
                )}
            </div>
        </article>
    );
}
