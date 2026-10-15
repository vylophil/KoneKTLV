import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

const appName = 'KoneKT';
const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const loadPage = pages[`./pages/${name}.tsx`];

        if (!loadPage) {
            throw new Error(`Page not found: ${name}`);
        }

        return (await loadPage()).default;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
