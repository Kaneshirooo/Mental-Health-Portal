/**
 * AjaxHelpers — Shared AJAX / Fetch utility for partial DOM updates.
 * Prevents full-page reloads, preserves scroll position, and provides
 * consistent visual feedback across all views.
 */
window.AjaxHelpers = {

    /**
     * Swap only a specific container on the page by selector.
     * Fetches the current URL (or a given URL), parses it, and replaces
     * only the matched element — preserving scroll position and all UI state.
     *
     * @param {string} url      — URL to fetch (defaults to window.location.href)
     * @param {string} selector — CSS selector of the element to swap
     */
    refreshSection: async function(url, selector) {
        const scrollY = window.scrollY;
        const target  = document.querySelector(selector);
        if (!target) return;

        // Show subtle shimmer on the container while loading
        target.style.opacity = '0.5';
        target.style.pointerEvents = 'none';

        try {
            const res  = await fetch(url || window.location.href);
            if (!res.ok) return;
            const html = await res.text();
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const fresh = doc.querySelector(selector);
            if (fresh) {
                target.innerHTML = fresh.innerHTML;
                // Restore scroll immediately — no jump
                window.scrollTo({ top: scrollY, behavior: 'instant' });
                // Animate fresh content in
                if (window.gsap) {
                    gsap.from(target.children, {
                        opacity: 0, y: 10,
                        duration: 0.45,
                        stagger: 0.04,
                        ease: 'power2.out',
                        clearProps: 'all'
                    });
                }
            }
        } catch (e) {
            console.warn('[AjaxHelpers] refreshSection failed:', e);
        } finally {
            target.style.opacity   = '';
            target.style.pointerEvents = '';
        }
    },

    /**
     * Flash a table row or card green to confirm a successful update.
     * @param {HTMLElement} el
     */
    flashRow: function(el) {
        if (!el) return;
        if (window.gsap) {
            gsap.to(el, {
                background: 'rgba(16, 185, 129, 0.12)',
                borderColor: '#10b981',
                duration: 0.25,
                yoyo: true,
                repeat: 3,
                ease: 'power1.inOut',
                clearProps: 'all'
            });
        } else {
            el.style.transition = 'background 0.3s';
            el.style.background = 'rgba(16, 185, 129, 0.12)';
            setTimeout(() => { el.style.background = ''; }, 1500);
        }
    },

    /**
     * Animate a removed row/card sliding out.
     * @param {HTMLElement} el
     * @param {Function}    onComplete
     */
    removeRow: function(el, onComplete) {
        if (!el) { if (onComplete) onComplete(); return; }
        if (window.gsap) {
            gsap.to(el, {
                x: 80, opacity: 0, height: 0,
                paddingTop: 0, paddingBottom: 0,
                marginBottom: 0, duration: 0.4, ease: 'expo.in',
                onComplete: () => { el.remove(); if (onComplete) onComplete(); }
            });
        } else {
            el.remove();
            if (onComplete) onComplete();
        }
    },

    /**
     * Put a button into loading / disabled state.
     * @param {HTMLElement} btn
     * @param {string}      label — optional loading label
     * @returns {string} original innerHTML (to pass back to stopBtn)
     */
    startBtn: function(btn, label) {
        if (!btn) return '';
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="ph ph-circle-notch" style="animation:spin 1s linear infinite; display:inline-block;"></i>${label ? ' ' + label : ''}`;
        return original;
    },

    /**
     * Restore a button from loading state.
     * @param {HTMLElement} btn
     * @param {string}      original — innerHTML returned from startBtn
     */
    stopBtn: function(btn, original) {
        if (!btn) return;
        btn.disabled = false;
        btn.innerHTML = original;
    },

    /**
     * Build a <tr> for the Admin User Management table from a user JSON object.
     */
    buildUserRow: function(user) {
        const roleConfig = {
            student:   { bg: 'rgba(16, 185, 129, 0.08)', color: '#10b981',  label: 'Student' },
            counselor: { bg: 'rgba(99, 102, 241, 0.08)', color: '#6366f1',  label: 'Counselor' },
            admin:     { bg: 'rgba(239, 68, 68, 0.08)',  color: '#ef4444',  label: 'Administrator' },
        };
        const s = roleConfig[user.user_type] || { bg: 'var(--surface-3)', color: 'var(--text-dim)', label: user.user_type };
        const initial = (user.full_name || '?').charAt(0).toUpperCase();

        const tr = document.createElement('tr');
        tr.className = 'user-row';
        tr.style.cssText = 'background: var(--surface-2); border-radius: 20px; transition: all 0.3s ease;';
        tr.dataset.name  = (user.full_name || '').toLowerCase();
        tr.dataset.email = (user.email || '').toLowerCase();

        tr.innerHTML = `
            <td style="padding: 1.5rem; border-top-left-radius: 20px; border-bottom-left-radius: 20px;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary);">${initial}</div>
                    <div style="font-weight: 800; color: var(--text);">${user.full_name}</div>
                </div>
            </td>
            <td style="padding: 1.5rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">${user.email}</td>
            <td style="padding: 1.5rem;">
                <span style="display: inline-flex; align-items: center; padding: 0.4rem 1rem; border-radius: 100px; background: ${s.bg}; color: ${s.color}; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; border: 1px solid currentColor;">
                    ${s.label}
                </span>
            </td>
            <td style="padding: 1.5rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">${user.created_at}</td>
            <td style="padding: 1.5rem; text-align: right; border-top-right-radius: 20px; border-bottom-right-radius: 20px;">
                <button class="btn-sm" style="background: rgba(239,68,68,0.08); color:#ef4444; border:1px solid rgba(239,68,68,0.2); font-weight:800; text-transform:uppercase; padding:0.5rem 1rem; border-radius:12px; cursor:pointer; transition:all 0.3s;"
                    onmouseover="this.style.background='#ef4444'; this.style.color='white'"
                    onmouseout="this.style.background='rgba(239,68,68,0.08)'; this.style.color='#ef4444'"
                    onclick="confirmDelete(${user.user_id}, this)">Remove</button>
                <span class="delete-confirm" id="dc-${user.user_id}">
                    <span style="font-size:0.75rem; font-weight:800; color:#ef4444; margin-right:0.5rem;">CONFIRM?</span>
                    <form method="POST" action="/admin/users/${user.user_id}" class="delete-form" style="display:inline;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn-sm" style="background:#ef4444; color:white; border:none; font-weight:800; padding:0.5rem 1rem; border-radius:12px;">YES</button>
                    </form>
                    <button class="btn-sm" style="background:var(--surface-3); border:1px solid var(--border); font-weight:800; padding:0.5rem 1rem; border-radius:12px;" onclick="cancelDelete(${user.user_id})">NO</button>
                </span>
            </td>`;
        return tr;
    },

    /**
     * Build a .counselor-card element from a counselor JSON object.
     */
    buildStaffCard: function(c) {
        const div = document.createElement('div');
        div.className = 'counselor-card staggered-row';
        div.dataset.name  = (c.full_name || '').toLowerCase();
        div.dataset.email = (c.email || '').toLowerCase();
        div.style.cssText = 'background:var(--surface-2); padding:1.75rem 2rem; border-radius:24px; display:flex; justify-content:space-between; align-items:center; border:1px solid transparent; transition:all 0.3s ease;';

        const initial = (c.full_name || '?').charAt(0).toUpperCase();
        const deptBadge = c.department
            ? `<span style="font-size:0.8rem;font-weight:700;color:#6366f1;background:rgba(99,102,241,0.08);padding:0.3rem 0.75rem;border-radius:100px;display:flex;align-items:center;gap:0.4rem;border:1px solid rgba(99,102,241,0.15);">
                   <i class="ph ph-buildings"></i> ${c.department}
               </span>` : '';

        div.innerHTML = `
            <div style="display:flex;align-items:center;gap:1.5rem;">
                <div style="width:56px;height:56px;border-radius:16px;background:var(--surface-solid);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--primary);font-size:1.25rem;">${initial}</div>
                <div>
                    <div style="font-family:'Outfit',sans-serif;font-size:1.25rem;font-weight:800;color:var(--text);margin-bottom:0.25rem;">${c.full_name}</div>
                    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                        <span style="font-size:0.8rem;font-weight:700;color:var(--text-muted);background:var(--surface-3);padding:0.3rem 0.75rem;border-radius:100px;display:flex;align-items:center;gap:0.4rem;border:1px solid var(--border);">
                            <i class="ph ph-envelope-simple"></i> ${c.email}
                        </span>
                        ${deptBadge}
                        <span style="font-size:0.8rem;font-weight:700;color:#10b981;background:rgba(16,185,129,0.08);padding:0.3rem 0.75rem;border-radius:100px;display:flex;align-items:center;gap:0.4rem;border:1px solid rgba(16,185,129,0.15);">
                            <i class="ph ph-calendar-check"></i> 0 Cases
                        </span>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:1rem;align-items:center;">
                <button class="btn-sm" style="background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);font-weight:800;text-transform:uppercase;padding:0.5rem 1.25rem;border-radius:12px;cursor:pointer;transition:all 0.3s;"
                    onmouseover="this.style.background='#ef4444';this.style.color='white'"
                    onmouseout="this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'"
                    onclick="confirmDel(${c.user_id}, this)">Remove</button>
                <span class="delete-confirm" id="dc-${c.user_id}">
                    <span style="font-size:0.75rem;font-weight:800;color:#ef4444;margin-right:0.5rem;">CONFIRM?</span>
                    <form method="POST" action="/admin/staff/${c.user_id}" class="delete-form" style="display:inline;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn-sm" style="background:#ef4444;color:white;border:none;font-weight:800;padding:0.5rem 1rem;border-radius:12px;">YES</button>
                    </form>
                    <button class="btn-sm" style="background:var(--surface-3);border:1px solid var(--border);font-weight:800;padding:0.5rem 1rem;border-radius:12px;" onclick="cancelDel(${c.user_id})">NO</button>
                </span>
            </div>`;
        return div;
    },

    /**
     * Build a student appointment card from a JSON appointment object.
     */
    buildAppointmentCard: function(a) {
        const icons = { requested: '⏳', confirmed: '✅', completed: '🎓', declined: '❌', cancelled: '🚫' };
        const icon  = icons[a.status] || '📅';
        const status = a.status || 'requested';

        const div = document.createElement('div');
        div.className = 'timeline-card';
        div.dataset.appointmentId = a.appointment_id;

        const cancelBtn = (status === 'requested' || status === 'confirmed')
            ? `<form method="POST" action="/student/appointments/${a.appointment_id}/cancel" class="cancel-form" style="margin-top:0.75rem;">
                   <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                   <button type="submit" style="background:transparent;border:1px solid #fee2e2;color:#dc2626;padding:0.35rem 0.75rem;border-radius:8px;font-size:0.7rem;font-weight:800;text-transform:uppercase;cursor:pointer;transition:var(--transition);"
                       onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                       Cancel Appointment
                   </button>
               </form>` : '';

        const reasonHtml = a.reason
            ? `<div style="font-size:0.78rem;color:var(--text-dim);margin-top:0.4rem;font-style:italic;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">"${a.reason}"</div>` : '';

        div.innerHTML = `
            <div style="width:44px;height:44px;border-radius:14px;background:var(--primary-glow);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">${icon}</div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.25rem;">
                    <div style="font-weight:800;color:var(--text);font-size:0.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${a.counselor_name || 'Counselor'}</div>
                    <span class="appt-status status-${status}">${status}</span>
                </div>
                <div style="font-size:0.8rem;color:var(--primary);font-weight:700;">${a.scheduled_at_formatted || a.scheduled_at}</div>
                ${reasonHtml}
                ${cancelBtn}
            </div>`;
        return div;
    }
};

/* Keyframe for spinner (injected once) */
if (!document.getElementById('ajax-helpers-spin-style')) {
    const style = document.createElement('style');
    style.id = 'ajax-helpers-spin-style';
    style.textContent = '@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
    document.head.appendChild(style);
}
