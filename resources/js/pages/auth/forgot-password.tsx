import { Link, useForm } from '@inertiajs/react';
import { Field, Portal } from '@/components/portal';

export default function ForgotPassword({ status }: { status: string | null }) {
    const form = useForm({ email: '' });
    return (
        <Portal title="Reset your password">
            <section className="kt-auth">
                <h1>Forgot your password?</h1>
                <p>
                    Enter your account email and we’ll send you a link to set a
                    new password.
                </p>
                {(status || form.recentlySuccessful) && (
                    <p className="kt-notice" role="status">
                        Check your email for a password-reset link.
                    </p>
                )}
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post('/forgot-password');
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
                    <button className="kt-button" disabled={form.processing}>
                        {form.processing ? 'Sending…' : 'Send reset link'}
                    </button>
                </form>
                <Link href="/login" className="kt-text-link mt-6">
                    Back to sign in
                </Link>
            </section>
        </Portal>
    );
}
