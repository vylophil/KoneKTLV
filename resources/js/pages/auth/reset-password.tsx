import { useForm } from '@inertiajs/react';
import { Field, Portal } from '@/components/portal';

export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const form = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });
    return (
        <Portal title="Choose a new password">
            <section className="kt-auth">
                <h1>Choose a new password</h1>
                <p>
                    Use at least eight characters and a password you haven’t
                    used elsewhere.
                </p>
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post('/reset-password', {
                            onFinish: () =>
                                form.reset('password', 'password_confirmation'),
                        });
                    }}
                >
                    <Field
                        id="email"
                        label="Email address"
                        type="email"
                        autoComplete="email"
                        required
                        value={form.data.email}
                        error={form.errors.email}
                        onChange={(event) =>
                            form.setData('email', event.target.value)
                        }
                    />
                    <Field
                        id="password"
                        label="New password"
                        type="password"
                        autoComplete="new-password"
                        required
                        minLength={8}
                        value={form.data.password}
                        error={form.errors.password}
                        onChange={(event) =>
                            form.setData('password', event.target.value)
                        }
                    />
                    <Field
                        id="password-confirmation"
                        label="Confirm password"
                        type="password"
                        autoComplete="new-password"
                        required
                        minLength={8}
                        value={form.data.password_confirmation}
                        error={form.errors.password_confirmation}
                        onChange={(event) =>
                            form.setData(
                                'password_confirmation',
                                event.target.value,
                            )
                        }
                    />
                    <button className="kt-button" disabled={form.processing}>
                        {form.processing ? 'Saving…' : 'Reset password'}
                    </button>
                </form>
            </section>
        </Portal>
    );
}
