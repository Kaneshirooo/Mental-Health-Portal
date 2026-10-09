{{-- PSU system header: full-width banner strip shown on every page (all roles).
     Original artwork recreated in code (no "Student Portal" text). --}}
<div class="psu-header">
    <svg class="psu-waves" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
        <g fill="none" stroke="rgba(255,255,255,0.16)" stroke-width="1.4">
            <path d="M0,95 C200,40 350,110 550,60 C750,10 950,90 1200,45" />
            <path d="M0,105 C220,55 380,120 580,72 C780,25 970,100 1200,58" />
            <path d="M0,85 C180,30 340,100 540,52 C740,5 960,80 1200,35" />
            <path d="M0,115 C240,70 400,125 600,85 C800,45 990,108 1200,72" />
            <path d="M0,70 C160,20 330,90 530,42 C730,-5 970,70 1200,25" />
        </g>
    </svg>
    <div class="psu-header-inner">
        <img src="{{ asset('logo/system_logo.jpg') }}" alt="Pangasinan State University seal" class="psu-seal">
        <div class="psu-names">
            <span class="psu-uni">Pangasinan State University</span>
            <span class="psu-sub">Mental Health Portal</span>
        </div>
    </div>
</div>

<style>
    .psu-header {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(100deg, #141b5c 0%, #232f86 45%, #31409f 75%, #3d4fb4 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: var(--shadow-sm, 0 4px 12px rgba(0,0,0,0.06));
        margin-bottom: 1.75rem;
    }
    .psu-waves {
        position: absolute;
        inset: auto 0 0 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .psu-header-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.85rem 1.5rem;
    }
    .psu-seal {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.55);
        background: white;
        flex-shrink: 0;
    }
    .psu-names { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }
    .psu-uni {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 1.35rem;
        letter-spacing: 0.06em;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .psu-sub {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.28em;
        color: #ffd54f;
    }
    .psu-header::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: linear-gradient(90deg, #ffc107, #ffca28 60%, #ffe082);
    }
    @media (max-width: 640px) {
        .psu-uni { font-size: 1rem; }
        .psu-seal { width: 44px; height: 44px; }
        .psu-header-inner { padding: 0.7rem 1rem; }
        .psu-sub { letter-spacing: 0.18em; }
    }
</style>
