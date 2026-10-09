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
        border-radius: 28px;
        background: linear-gradient(100deg, #10175a 0%, #232f86 42%, #33419f 72%, #4555c2 100%);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 18px 45px rgba(28, 37, 110, 0.28);
        margin-bottom: 2rem;
    }
    .psu-waves {
        position: absolute;
        inset: auto 0 0 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        animation: psu-drift 14s ease-in-out infinite alternate;
    }
    @keyframes psu-drift {
        from { transform: translateX(-1.5%); }
        to { transform: translateX(1.5%); }
    }
    .psu-header-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 1.35rem;
        padding: 1.4rem 2rem;
    }
    .psu-seal {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.55), 0 10px 25px rgba(0, 0, 0, 0.35);
        background: white;
        flex-shrink: 0;
    }
    .psu-names { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }
    .psu-uni {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 2rem;
        letter-spacing: 0.08em;
        color: #ffffff;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .psu-sub {
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.34em;
        color: #ffd54f;
        margin-top: 0.25rem;
    }
    .psu-header::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 5px;
        background: linear-gradient(90deg, #ffc107, #ffca28 60%, #ffe082);
    }
    @media (max-width: 640px) {
        .psu-uni { font-size: 1.15rem; white-space: normal; }
        .psu-seal { width: 58px; height: 58px; }
        .psu-header-inner { padding: 1rem 1.25rem; gap: 0.9rem; }
        .psu-sub { letter-spacing: 0.2em; font-size: 0.65rem; }
    }
</style>
