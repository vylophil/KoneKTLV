import { useForm } from '@inertiajs/react';
import { Field, PageTitle, Portal } from '@/components/portal';

export default function Settings({ email }: { email: string }) {
    const form = useForm({ username: '' });
    return (
        <Portal title="Account settings">
            <PageTitle
                title="Account settings"
                description="Manage the account behind your profile."
            />
            <section className="kt-section max-w-2xl">
                <h2>Account email</h2>
                <p>{email}</p>
            </section>
            <section className="kt-section max-w-2xl">
                <h2>Delete your account</h2>
                <p>
                    This permanently removes your profile, resume, applications,
                    and related activity. This cannot be undone.
                </p>
                <details className="mt-5">
                    <summary className="kt-text-link">
                        Show account deletion
                    </summary>
                    <form
                        className="mt-5"
                        onSubmit={(event) => {
                            event.preventDefault();
                            if (
                                window.confirm(
                                    'Permanently delete your account and all related data?',
                                )
                            )
                                form.delete('/account');
                        }}
                    >
                        <Field
                            id="delete-confirmation"
                            label={`Type ${email} to confirm`}
                            type="email"
                            autoComplete="off"
                            value={form.data.username}
                            error={form.errors.username}
                            onChange={(event) =>
                                form.setData('username', event.target.value)
                            }
                        />
                        <div className="kt-form-actions">
                            <button
                                className="kt-button kt-button-danger"
                                disabled={
                                    form.processing ||
                                    form.data.username !== email
                                }
                            >
                                Permanently delete account
                            </button>
                        </div>
                    </form>
                </details>
            </section>
        </Portal>
    );
}
