<style>
    /* ── Shared message cards (counselor + admin dashboards) ── */
    .note-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 32px;
        padding: 2.25rem;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.35s ease, transform 0.35s ease;
        position: relative;
        overflow: hidden;
    }
    .note-card:hover { box-shadow: 0 20px 48px rgba(0,0,0,0.1); transform: translateY(-4px); }
    .bubble-student {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text);
        border-bottom-left-radius: 6px;
        padding: 1rem 1.25rem;
        border-radius: 20px;
        font-size: 0.95rem;
        line-height: 1.6;
        max-width: 90%;
        font-weight: 500;
    }
    .bubble-counselor {
        background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
        color: white;
        border-bottom-right-radius: 6px;
        padding: 1rem 1.25rem;
        border-radius: 20px;
        font-size: 0.95rem;
        line-height: 1.6;
        max-width: 90%;
        font-weight: 500;
        box-shadow: 0 4px 16px rgba(5,150,105,0.25);
    }
    .btn-ai-assist {
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        border: none !important;
        border-radius: 100px !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.72rem !important;
        padding: 0.5rem 1.1rem !important;
        cursor: pointer;
        display: flex; align-items: center; gap: 0.4rem;
        box-shadow: 0 4px 14px rgba(99,102,241,0.35);
        transition: all 0.25s ease;
    }
    .btn-ai-assist:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }
    @media (max-width: 768px) {
        .note-card { padding: 1.25rem 1rem !important; border-radius: 20px !important; }
    }
</style>
