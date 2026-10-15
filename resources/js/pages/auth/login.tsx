import { Link, useForm } from '@inertiajs/react';
import { Field, Portal } from '@/components/portal';

export default function Login({ status }: { status?: string }) {
    const form = useForm({ email: '', password: '', remember: false });
    return (
        <Portal title="Sign in">
            <section className="kt-auth">
                <h1>Welcome back</h1>
                <p>
                    Sign in to continue your search and manage your
                    applications.
                </p>
                {status && (
                    <p className="kt-notice" role="status">
                        {status}
                    </p>
                )}
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post('/login', {
                            onFinish: () => form.reset('password'),
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
                        label="Password"
                        type="password"
                        autoComplete="current-password"
                        required
                        value={form.data.password}
                        error={form.errors.password}
                        onChange={(event) =>
                            form.setData('password', event.target.value)
                        }
                    />
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <label className="flex min-h-11 items-center gap-3 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.remember}
                                onChange={(event) =>
                                    form.setData(
                                        'remember',
                                        event.target.checked,
                                    )
                                }
                            />
                            Remember me
                        </label>
                        <Link href="/forgot-password" className="kt-text-link">
                            Forgot password?
                        </Link>
                    </div>
                    <button className="kt-button" disabled={form.processing}>
                        {form.processing ? 'Signing in…' : 'Sign in'}
                    </button>
                </form>
                <p className="kt-meta mt-6">
                    New to KoneKT?{' '}
                    <Link className="kt-text-link" href="/register">
                        Create an account
                    </Link>
                </p>
            </section>
        </Portal>
    );
}
