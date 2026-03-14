<?php
/**
 * toast.php — Global Toast Notification Engine
 * Include this once per page (before </body>).
 * PHP usage: showToast($message, $type, $title) helper
 * JS usage:  showToast('message', 'success'|'error'|'warning'|'info', 'optional title')
 *
 * PHP server-side: call renderToasts($toasts) or use the $__toasts global array.
 */

// Accept server-side toast triggers via a global array
if (!isset($__toasts)) $__toasts = [];

if (!function_exists('queueToast')) {
    function queueToast(string $message, string $type = 'success', string $title = '') {
        global $__toasts;
        if (empty($title)) {
            $titles = ['success' => 'Success', 'error' => 'Error', 'warning' => 'Warning', 'info' => 'Info'];
            $title = $titles[$type] ?? 'Notice';
        }
        $__toasts[] = ['msg' => $message, 'type' => $type, 'title' => $title];
    }
}
?>
<!-- ═══ Toast Container ═══ -->
<div id="toast-container" aria-live="polite" aria-atomic="false"></div>

<script>
/* ─── Toast Engine ─── */
(function () {
    const icons = {
        success: '✅',
        error:   '❌',
        warning: '⚠️',
        info:    'ℹ️',
    };

    window.showToast = function (msg, type = 'success', title = '', duration = 4000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        if (!title) {
            const defaults = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };
            title = defaults[type] || 'Notice';
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.setProperty('--toast-duration', duration + 'ms');
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-body">
                <div class="toast-title">${title}</div>
                <div class="toast-msg">${msg}</div>
            </div>
            <button class="toast-close" aria-label="Dismiss">✕</button>
            <div class="toast-bar"></div>
        `;

        container.appendChild(toast);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => dismiss(toast));

        // Auto-dismiss
        const timer = setTimeout(() => dismiss(toast), duration);

        // Pause on hover
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timer);
            toast.querySelector('.toast-bar').style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            toast.querySelector('.toast-bar').style.animationPlayState = 'running';
            setTimeout(() => dismiss(toast), 1000);
        });
    };

    function dismiss(toast) {
        toast.classList.add('toast-hide');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }

    /* ─── Fire server-side queued toasts on DOM ready ─── */
    const queued = <?php echo json_encode($__toasts); ?>;
    document.addEventListener('DOMContentLoaded', () => {
        queued.forEach(t => showToast(t.msg, t.type, t.title));
    });
})();
</script>
