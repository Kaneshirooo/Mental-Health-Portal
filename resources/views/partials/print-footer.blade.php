{{-- PSU Official Letterhead Footer --}}
{{-- Usage: @include('partials.print-footer') --}}
{{-- Shown in window.print() and in html2canvas PDF exports (body.pdf-export). --}}
<div class="psu-letterhead-footer">
    <div class="psu-letterhead-footer-divider"></div>
    <div class="psu-accreditation-strip">
        <img src="{{ asset('images/letterhead/image7.png') }}" alt="PSU Accreditation Badges" class="psu-footer-badges">
    </div>
</div>

<style>
.psu-letterhead-footer {
    display: none;
}

.psu-letterhead-footer-divider {
    height: 2px !important;
    background: linear-gradient(90deg, #0f2c59 0%, #d97706 50%, #0f2c59 100%) !important;
    margin-bottom: 10px !important;
    border: none !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.psu-accreditation-strip {
    text-align: center !important;
    width: 100% !important;
}

.psu-footer-badges {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    max-height: 32px !important;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto !important;
}

.pdf-export .psu-letterhead-footer {
    display: block !important;
    width: 100% !important;
    margin-top: 2rem !important;
    page-break-inside: avoid !important;
    background: transparent !important;
}

@media print {
    .psu-letterhead-footer {
        display: block !important;
        width: 100% !important;
        margin-top: 2rem !important;
        page-break-inside: avoid !important;
        background: transparent !important;
    }
}
</style>
