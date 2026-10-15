import { Link, useForm } from '@inertiajs/react';
import { Field, Portal } from '@/components/portal';

export default function Register() {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    return (
        <Portal title="Create an account">
            <section className="kt-auth">
                <h1>Your next move starts here</h1>
                <p>
                    Create a job-seeker account to save roles, find resume
                    matches, and track applications.
                </p>
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post('/register', {
                            replace: true,
                            onFinish: () =>
                                form.reset('password', 'password_confirmation'),
                        });
                    }}
                >
                    <Field
                        id="name"
                        label="Full name"
                        autoComplete="name"
                        maxLength={255}
                        required
                        value={form.data.name}
                        error={form.errors.name}
                        onChange={(event) =>
                            form.setData('name', event.target.value)
                        }
                    />
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
                        label="Password (at least 8 characters)"
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
                        {form.processing
                            ? 'Creating account…'
                            : 'Create account'}
                    </button>
                </form>
                <p className="kt-meta mt-6">
                    Already registered?{' '}
                    <Link href="/login" className="kt-text-link">
                        Sign in
                    </Link>
                </p>
            </section>
        </Portal>
    );
}
