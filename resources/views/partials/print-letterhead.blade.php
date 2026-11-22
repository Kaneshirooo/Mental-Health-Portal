{{-- PSU Official Letterhead Header --}}
{{-- Usage: @include('partials.print-letterhead') --}}
<div class="psu-letterhead-header">
    <div class="psu-letterhead-row">
        <div class="psu-letterhead-logo-left">
            <img src="{{ asset('images/letterhead/image3.png') }}" alt="PSU Seal" class="psu-seal">
        </div>
        <div class="psu-letterhead-text">
            <div class="psu-lh-line republic">Republic of the Philippines</div>
            <div class="psu-lh-line university">PANGASINAN STATE UNIVERSITY</div>
            <div class="psu-lh-line campus">Lingayen, Pangasinan</div>
            <div class="psu-lh-line links">
                <span>www.psu.edu.ph</span>
                <span class="psu-sep">•</span>
                <span>president@psu.edu.ph</span>
                <span class="psu-sep">•</span>
                <span>www.facebook.com/PSUroars</span>
            </div>
        </div>
        <div class="psu-letterhead-logo-right">
            <img src="{{ asset('images/letterhead/image6.png') }}" alt="Bagong Pilipinas" class="psu-bagong">
        </div>
    </div>
    <div class="psu-letterhead-divider"></div>
</div>

<style>
.psu-letterhead-header {
    display: none;
}

@media print {
    .psu-letterhead-header {
        display: block !important;
        width: 100% !important;
        margin: 0 0 1.25rem 0 !important;
        padding: 0 !important;
        page-break-inside: avoid !important;
        color: #000 !important;
        background: transparent !important;
    }

    .psu-letterhead-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 15px !important;
        width: 100% !important;
    }

    .psu-letterhead-logo-left,
    .psu-letterhead-logo-right {
        flex-shrink: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .psu-seal {
        width: 78px !important;
        height: 78px !important;
        object-fit: contain !important;
    }

    .psu-bagong {
        width: 82px !important;
        height: auto !important;
        object-fit: contain !important;
    }

    .psu-letterhead-text {
        text-align: center !important;
        flex: 1 !important;
        font-family: 'Times New Roman', Times, serif !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
    }

    .psu-lh-line.republic {
        font-size: 10.5pt !important;
        font-style: italic !important;
        letter-spacing: 0.02em !important;
        color: #1e293b !important;
    }

    .psu-lh-line.university {
        font-size: 14.5pt !important;
        font-weight: 800 !important;
        letter-spacing: 0.04em !important;
        color: #0f2c59 !important;
        margin: 2px 0 !important;
        text-transform: uppercase !important;
    }

    .psu-lh-line.campus {
        font-size: 10pt !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin-bottom: 2px !important;
    }

    .psu-lh-line.links {
        font-size: 8pt !important;
        color: #0284c7 !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 6px !important;
        font-family: Arial, sans-serif !important;
    }

    .psu-sep {
        color: #94a3b8 !important;
    }

    .psu-letterhead-divider {
        height: 3px !important;
        background: linear-gradient(90deg, #0f2c59 0%, #d97706 50%, #0f2c59 100%) !important;
        margin-top: 10px !important;
        border: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
