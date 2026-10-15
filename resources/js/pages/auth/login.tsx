import { Head, Link, useForm } from '@inertiajs/react';

export default function Login() {
    const form = useForm({ email: '', password: '', remember: false });

    return (
        <>
            <Head title="Sign in" />
            <main className="flex min-h-screen items-center justify-center bg-[#f5f6f0] px-6 py-12 text-[#17251e]">
                <section className="w-full max-w-md">
                    <Link
                        className="text-2xl font-semibold"
                        href="/"
                        aria-label="KoneKT home"
                    >
                        Kone<span className="text-[#e45e42]">K</span>T
                    </Link>
                    <h1 className="mt-10 text-3xl font-semibold tracking-tight">
                        Welcome back
                    </h1>
                    <p className="mt-2 text-sm text-[#52635a]">
                        Sign in to manage your profile and resume.
                    </p>

                    <form
                        className="mt-8 space-y-5"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.post('/login', {
                                onFinish: () => form.reset('password'),
                            });
                        }}
                    >
                        <div>
                            <label
                                className="mb-2 block text-sm font-medium"
                                htmlFor="email"
                            >
                                Email address
                            </label>
                            <input
                                id="email"
                                type="email"
                                autoComplete="email"
                                required
                                value={form.data.email}
                                onChange={(event) =>
                                    form.setData('email', event.target.value)
                                }
                                className="min-h-12 w-full border border-[#cbd5cb] bg-white px-4 outline-none focus:border-[#24543f]"
                            />
                            {form.errors.email && (
                                <p className="mt-2 text-sm text-[#a6382a]">
                                    {form.errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                className="mb-2 block text-sm font-medium"
                                htmlFor="password"
                            >
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                required
                                value={form.data.password}
                                onChange={(event) =>
                                    form.setData('password', event.target.value)
                                }
                                className="min-h-12 w-full border border-[#cbd5cb] bg-white px-4 outline-none focus:border-[#24543f]"
                            />
                            {form.errors.password && (
                                <p className="mt-2 text-sm text-[#a6382a]">
                                    {form.errors.password}
                                </p>
                            )}
                        </div>

                        <label className="flex items-center gap-2 text-sm text-[#52635a]">
                            <input
                                type="checkbox"
                                checked={form.data.remember}
                                onChange={(event) =>
                                    form.setData('remember', event.target.checked)
                                }
                                className="h-4 w-4 accent-[#183b2d]"
                            />
                            Remember me
                        </label>

                        <button
                            className="min-h-12 w-full bg-[#183b2d] px-5 font-semibold text-white transition-colors hover:bg-[#24543f] disabled:opacity-60"
                            type="submit"
                            disabled={form.processing}
                        >
                            {form.processing ? 'Signing in...' : 'Sign in'}
                        </button>
                    </form>

                    <p className="mt-6 text-sm text-[#52635a]">
                        New to KoneKT?{' '}
                        <Link
                            className="font-semibold text-[#183b2d] underline underline-offset-2"
                            href="/register"
                        >
                            Create an account
                        </Link>
                    </p>
                </section>
            </main>
        </>
    );
}