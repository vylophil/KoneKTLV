export function Brand() {
    return (
        <span className="brand">
            <span className="brand-symbol" aria-hidden="true">
                <svg viewBox="0 0 28 28" fill="none">
                    <path
                        d="M8 6v16m2-8L21 6M10 14l11 8"
                        stroke="currentColor"
                        strokeWidth="3"
                        strokeLinecap="round"
                    />
                    <circle cx="21" cy="6" r="2.5" fill="currentColor" />
                    <circle cx="21" cy="22" r="2.5" fill="currentColor" />
                </svg>
            </span>
            <span>
                KoneKT<span className="brand-dot">.</span>
            </span>
        </span>
    );
}

export function ArrowIcon() {
    return (
        <svg
            aria-hidden="true"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.7"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <path d="M5 12h14m-6-6 6 6-6 6" />
        </svg>
    );
}
