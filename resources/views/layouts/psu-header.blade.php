{{-- PSU system header: university banner strip shown on authenticated pages.
     Aligned with standard container width to eliminate awkward side whitespace. --}}
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
        border-radius: 24px;
        background: linear-gradient(100deg, #10175a 0%, #232f86 42%, #33419f 72%, #4555c2 100%);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 16px 40px rgba(28, 37, 110, 0.22);
        max-width: var(--container-max, 1320px);
        width: 100%;
        margin: 0 auto 2rem;
        box-sizing: border-box;
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
        padding: 1.35rem 2rem;
    }
    .psu-seal {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, 0.95);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.55), 0 8px 22px rgba(0, 0, 0, 0.35);
        background: white;
        flex-shrink: 0;
    }
    .psu-names { 
        display: flex; 
        flex-direction: column; 
        line-height: 1.2; 
        min-width: 0; 
    }
    .psu-uni {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: clamp(1.35rem, 2.5vw, 1.95rem);
        letter-spacing: 0.05em;
        color: #ffffff;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .psu-sub {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.28em;
        color: #ffd54f;
        margin-top: 0.25rem;
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
    @media (max-width: 1024px) {
        .psu-header {
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .psu-header-inner {
            padding: 1.15rem 1.5rem;
            gap: 1.1rem;
        }
        .psu-seal {
            width: 64px;
            height: 64px;
        }
    }
    @media (max-width: 640px) {
        .psu-header {
            border-radius: 18px;
            margin-bottom: 1.25rem;
        }
        .psu-header-inner { 
            padding: 0.9rem 1.1rem; 
            gap: 0.85rem; 
        }
        .psu-seal { 
            width: 52px; 
            height: 52px; 
            border-width: 2px;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.55), 0 6px 14px rgba(0, 0, 0, 0.3);
        }
        .psu-uni { 
            font-size: 1.15rem; 
            white-space: normal;
            line-height: 1.2;
        }
        .psu-sub { 
            letter-spacing: 0.16em; 
            font-size: 0.65rem; 
            margin-top: 0.15rem;
        }
    }
    @media (max-width: 380px) {
        .psu-seal { 
            width: 44px; 
            height: 44px; 
        }
        .psu-uni { 
            font-size: 1rem; 
        }
    }
</style>
