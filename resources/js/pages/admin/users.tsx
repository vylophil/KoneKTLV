import { useForm } from '@inertiajs/react';
import { Field, PageTitle, Pagination, Portal } from '@/components/portal';
import type { Paginated } from '@/components/portal';

type Member = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    isCurrentUser: boolean;
};

export default function UserAccess({
    users,
    search,
    roleOptions,
    rolePermissions,
}: {
    users: Paginated<Member>;
    search: string;
    roleOptions: string[];
    rolePermissions: Record<string, string[]>;
}) {
    return (
        <Portal title="User access">
            <PageTitle
                title="User access"
                description="Assign staff access while keeping member accounts private."
            />
            <section
                aria-label="Role permissions"
                className="kt-form-grid mb-8"
            >
                {roleOptions.map((role) => (
                    <div key={role} className="kt-access-card">
                        <h2>{role}</h2>
                        <p>
                            {rolePermissions[role].length
                                ? rolePermissions[role].join(', ')
                                : 'Member features only; no staff permissions.'}
                        </p>
                    </div>
                ))}
            </section>
            <form
                action="/admin/users"
                method="get"
                className="kt-home-search mb-8"
            >
                <div className="min-w-0 flex-1">
                    <Field
                        id="user-search"
                        name="search"
                        label="Search members"
                        defaultValue={search}
                        placeholder="Name or email"
                        maxLength={100}
                        type="search"
                    />
                </div>
                <button className="kt-button self-end" type="submit">
                    Search
                </button>
            </form>
            <p className="kt-meta mb-4">{users.total} members</p>
            {users.data.length === 0 && (
                <p className="kt-empty">No members match your search.</p>
            )}
            {users.data.map((user) => (
                <MemberAccess
                    key={user.id}
                    user={user}
                    roleOptions={roleOptions}
                />
            ))}
            <Pagination page={users} />
        </Portal>
    );
}

function MemberAccess({
    user,
    roleOptions,
}: {
    user: Member;
    roleOptions: string[];
}) {
    const form = useForm({
        role:
            user.roles.length === 1 && roleOptions.includes(user.roles[0])
                ? user.roles[0]
                : '',
    });
    const id = `user-role-${user.id}`;

    return (
        <article className="kt-access-row">
            <div className="min-w-0">
                <h2 className="font-semibold">{user.name}</h2>
                <p className="kt-meta break-words">{user.email}</p>
                <p className="kt-meta mt-2">
                    Current access:{' '}
                    {user.roles.join(', ') || 'No assigned role'}
                </p>
            </div>
            {user.isCurrentUser ? (
                <p className="kt-meta max-w-xs">
                    Your account. Ask another administrator to change your
                    access.
                </p>
            ) : (
                <form
                    className="kt-access-controls"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(`/admin/users/${user.id}/role`, {
                            preserveScroll: true,
                        });
                    }}
                >
                    <div className="kt-field min-w-0">
                        <label htmlFor={id}>Role for {user.name}</label>
                        <select
                            id={id}
                            value={form.data.role}
                            required
                            disabled={form.processing}
                            aria-invalid={!!form.errors.role}
                            aria-describedby={
                                form.errors.role ? `${id}-error` : undefined
                            }
                            onChange={(event) =>
                                form.setData('role', event.target.value)
                            }
                        >
                            <option value="" disabled>
                                Choose a role
                            </option>
                            {roleOptions.map((role) => (
                                <option key={role}>{role}</option>
                            ))}
                        </select>
                        {form.errors.role && (
                            <p
                                className="kt-error"
                                id={`${id}-error`}
                                role="alert"
                            >
                                {form.errors.role}
                            </p>
                        )}
                    </div>
                    <button
                        className="kt-button self-end"
                        disabled={
                            form.processing ||
                            form.data.role === user.roles.join(',')
                        }
                    >
                        {form.processing ? 'Saving...' : 'Save access'}
                    </button>
                </form>
            )}
        </article>
    );
}
