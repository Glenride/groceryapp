import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';
import type { SharedData } from '@/types';
import { Heart, Leaf, Store, Truck, Users, ArrowRight, CheckCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Good Grid - A Smarter Grid for Good Food">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-white dark:bg-[#0a0a0a]">
                {/* Navigation */}
                <header className="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur-md dark:border-gray-800 dark:bg-[#0a0a0a]/80">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 lg:px-8">
                        <div className="flex items-center gap-2">
                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600 text-white">
                                <Leaf className="h-5 w-5" />
                            </div>
                            <span className="text-xl font-semibold text-gray-900 dark:text-white">
                                Good Grid
                            </span>
                        </div>
                        <div className="flex items-center gap-4">
                            {auth.user ? (
                                <Link href={dashboard()}>
                                    <Button>Dashboard</Button>
                                </Link>
                            ) : (
                                <>
                                    <Link href={login()}>
                                        <Button variant="ghost">Log in</Button>
                                    </Link>
                                    {canRegister && (
                                        <Link href={register()}>
                                            <Button>Get Started</Button>
                                        </Link>
                                    )}
                                </>
                            )}
                        </div>
                    </nav>
                </header>

                {/* Hero Section */}
                <section className="relative overflow-hidden bg-gradient-to-b from-green-50 to-white px-4 py-20 dark:from-green-950/20 dark:to-[#0a0a0a] lg:py-32">
                    <div className="mx-auto max-w-7xl text-center">
                        <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-4 py-2 dark:border-green-800 dark:bg-green-900/20">
                            <span className="flex h-2 w-2 rounded-full bg-green-600"></span>
                            <span className="text-sm font-medium text-green-800 dark:text-green-300">
                                Now delivering in Cincinnati
                            </span>
                        </div>
                        <h1 className="mx-auto max-w-4xl text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                            A smarter grid for{' '}
                            <span className="text-green-600">good food</span>,{' '}
                            <span className="text-green-600">fair work</span>, and{' '}
                            <span className="text-green-600">healthier lives</span>
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                            Good Grid connects customers with local merchants and shoppers on a fair, 
                            transparent marketplace where everyone benefits—better food, fair pay, 
                            and healthier choices.
                        </p>
                        <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            {auth.user ? (
                                <Link href={dashboard()}>
                                    <Button size="lg" className="h-12 px-8">
                                        Go to Dashboard
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </Link>
                            ) : (
                                <>
                                    <Link href={canRegister ? register() : login()}>
                                        <Button size="lg" className="h-12 px-8">
                                            Start Shopping
                                            <ArrowRight className="ml-2 h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Link href={login()}>
                                        <Button size="lg" variant="outline" className="h-12 px-8">
                                            Become a Shopper
                                        </Button>
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </section>

                {/* Three Pillars */}
                <section className="px-4 py-20 lg:py-32">
                    <div className="mx-auto max-w-7xl">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                                Built for everyone in the food system
                            </h2>
                            <p className="mt-4 text-lg text-gray-600 dark:text-gray-300">
                                Three sides of the marketplace, all aligned for good
                            </p>
                        </div>
                        <div className="mt-16 grid gap-8 md:grid-cols-3">
                            {/* Customers */}
                            <div className="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-lg dark:border-gray-800 dark:bg-[#161615]">
                                <div className="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30">
                                    <Heart className="h-6 w-6" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    For Customers
                                </h3>
                                <p className="mb-6 text-gray-600 dark:text-gray-300">
                                    Shop with health intelligence. Get "Good Swap" suggestions for 
                                    healthier alternatives, filter by dietary needs, and support 
                                    local vendors.
                                </p>
                                <ul className="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-green-600" />
                                        <span>Health-focused product filtering</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-green-600" />
                                        <span>"Good Swap" recommendations</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-green-600" />
                                        <span>Local merchant discovery</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-green-600" />
                                        <span>Real-time order tracking</span>
                                    </li>
                                </ul>
                            </div>

                            {/* Shoppers */}
                            <div className="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-lg dark:border-gray-800 dark:bg-[#161615]">
                                <div className="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30">
                                    <Truck className="h-6 w-6" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    For Shoppers
                                </h3>
                                <p className="mb-6 text-gray-600 dark:text-gray-300">
                                    Fair, transparent work. See complete pay breakdowns before 
                                    accepting batches, with earnings funded by subscription and 
                                    analytics revenue.
                                </p>
                                <ul className="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-blue-600" />
                                        <span>Transparent pay formulas</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-blue-600" />
                                        <span>Base + distance + time pay</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-blue-600" />
                                        <span>In-store picking optimization</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-blue-600" />
                                        <span>Weekly earnings bonuses</span>
                                    </li>
                                </ul>
                            </div>

                            {/* Merchants */}
                            <div className="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-lg dark:border-gray-800 dark:bg-[#161615]">
                                <div className="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-900/30">
                                    <Store className="h-6 w-6" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    For Merchants
                                </h3>
                                <p className="mb-6 text-gray-600 dark:text-gray-300">
                                    Modern digital infrastructure for local grocers and farms. 
                                    Maintain your brand identity while accessing demand insights 
                                    and new customers.
                                </p>
                                <ul className="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-orange-600" />
                                        <span>Branded storefront in-app</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-orange-600" />
                                        <span>Simple catalog management</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-orange-600" />
                                        <span>Anonymized demand analytics</span>
                                    </li>
                                    <li className="flex items-start gap-2">
                                        <CheckCircle className="mt-0.5 h-4 w-4 text-orange-600" />
                                        <span>Fair commission structure</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Stats Section */}
                <section className="border-y border-gray-200 bg-gray-50 px-4 py-16 dark:border-gray-800 dark:bg-[#161615]/50">
                    <div className="mx-auto max-w-7xl">
                        <div className="grid gap-8 text-center sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <div className="text-3xl font-bold text-green-600">5+</div>
                                <div className="mt-1 text-sm text-gray-600 dark:text-gray-400">Local Merchants</div>
                            </div>
                            <div>
                                <div className="text-3xl font-bold text-green-600">200+</div>
                                <div className="mt-1 text-sm text-gray-600 dark:text-gray-400">Products Available</div>
                            </div>
                            <div>
                                <div className="text-3xl font-bold text-green-600">10+</div>
                                <div className="mt-1 text-sm text-gray-600 dark:text-gray-400">Health Filters</div>
                            </div>
                            <div>
                                <div className="text-3xl font-bold text-green-600">1</div>
                                <div className="mt-1 text-sm text-gray-600 dark:text-gray-400">City (Cincinnati)</div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* How It Works */}
                <section className="px-4 py-20 lg:py-32">
                    <div className="mx-auto max-w-7xl">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                                How Good Grid works
                            </h2>
                            <p className="mt-4 text-lg text-gray-600 dark:text-gray-300">
                                Simple, transparent, and designed for your health
                            </p>
                        </div>
                        <div className="mt-16 grid gap-8 md:grid-cols-4">
                            {[
                                {
                                    step: '1',
                                    title: 'Browse',
                                    desc: 'Discover local merchants and filter products by your health goals and dietary needs.',
                                },
                                {
                                    step: '2',
                                    title: 'Add to Cart',
                                    desc: 'Get "Good Swap" suggestions for healthier alternatives as you shop.',
                                },
                                {
                                    step: '3',
                                    title: 'We Shop',
                                    desc: 'A trained shopper picks your order with care, communicating any substitutions.',
                                },
                                {
                                    step: '4',
                                    title: 'Delivery',
                                    desc: 'Your groceries arrive fresh, with real-time tracking from store to door.',
                                },
                            ].map((item, index) => (
                                <div key={index} className="text-center">
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-600 text-lg font-bold text-white">
                                        {item.step}
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {item.desc}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="px-4 py-20 lg:py-32">
                    <div className="mx-auto max-w-4xl rounded-3xl bg-green-600 px-4 py-16 text-center dark:bg-green-700 sm:px-8 lg:px-16">
                        <h2 className="text-3xl font-bold text-white sm:text-4xl">
                            Ready to join the grid?
                        </h2>
                        <p className="mx-auto mt-4 max-w-xl text-lg text-green-100">
                            Whether you're shopping for your family, delivering with purpose, 
                            or growing your local business—Good Grid is built for you.
                        </p>
                        <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            {canRegister && (
                                <Link href={register()}>
                                    <Button size="lg" variant="secondary" className="h-12 px-8">
                                        <Users className="mr-2 h-4 w-4" />
                                        Sign Up as Customer
                                    </Button>
                                </Link>
                            )}
                            <Link href={login()}>
                                <Button size="lg" variant="outline" className="h-12 border-white bg-transparent px-8 text-white hover:bg-white hover:text-green-600">
                                    <Truck className="mr-2 h-4 w-4" />
                                    Become a Shopper
                                </Button>
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-gray-200 bg-white px-4 py-12 dark:border-gray-800 dark:bg-[#0a0a0a]">
                    <div className="mx-auto max-w-7xl">
                        <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div className="flex items-center gap-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-green-600 text-white">
                                    <Leaf className="h-5 w-5" />
                                </div>
                                <span className="text-lg font-semibold text-gray-900 dark:text-white">
                                    Good Grid
                                </span>
                            </div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                © {new Date().getFullYear()} Good Grid. A smarter grid for good food, fair work, and healthier lives.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
