{{-- PSU Official Letterhead Footer --}}
{{-- Usage: @include('partials.print-footer') --}}
{{-- Automatically revealed in window.print() and in html2canvas PDF exports (body.pdf-export). --}}
<div class="psu-letterhead-footer">
    <div class="psu-letterhead-footer-divider"></div>
    <div class="psu-accreditation-strip">
        <img src="{{ asset('images/letterhead/image7.png') }}" alt="PSU Accreditation Badges" class="psu-footer-badges">
    </div>
    <div class="psu-footer-footnote">
        Pangasinan State University • Student Mental Health &amp; Wellness Portal • Official Document
    </div>
</div>

<style>
/* Default on screen: hidden */
.psu-letterhead-footer {
    display: none;
}

.psu-letterhead-footer-divider {
    height: 2px !important;
    background: linear-gradient(90deg, #0f2c59 0%, #d97706 50%, #0f2c59 100%) !important;
    margin-bottom: 8px !important;
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
    max-height: 28px !important;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto 6px !important;
}

.psu-footer-footnote {
    font-size: 7.5pt !important;
    color: #64748b !important;
    text-align: center !important;
    font-family: Arial, sans-serif !important;
    letter-spacing: 0.05em !important;
    text-transform: uppercase !important;
}

body.pdf-export .psu-letterhead-footer,
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
