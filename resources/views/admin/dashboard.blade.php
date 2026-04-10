@extends('layouts.app')

@push('styles')
<style>
    .admin-stats-matrix {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .admin-card-glass {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 2rem;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    
    .admin-card-glass:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary-light);
    }

    /* System Health Glow */
    .health-glow {
        position: relative;
    }
    .health-glow::after {
        content: '';
        position: absolute;
        inset: -1px;
        background: linear-gradient(135deg, var(--primary), transparent);
        opacity: 0.1;
        border-radius: inherit;
        z-index: -1;
    }

    .activity-feed-item {
        padding: 1.25rem;
        border-radius: 20px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1rem;
    }

    .activity-feed-item:hover {
        background: var(--surface-solid);
        border-color: var(--primary-glow);
        transform: translateX(10px);
    }

    @media (max-width: 1024px) {
        .admin-stats-matrix { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1300px; padding: 2rem 1.5rem 5rem;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;" class="staggered">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.75rem;">System Governance & Analytics</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.05em; line-height: 1;">Head Counselor Dashboard</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.75rem;">Global oversight for PSU San Carlos Mental Health Ecosystem.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <div class="glass" style="padding: 1rem 1.5rem; border-radius: 20px; text-align: right;">
                <div style="color: var(--text-muted); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">Connected Node</div>
                <div style="color: var(--primary); font-weight: 800; display: flex; align-items: center; gap: 0.5rem; justify-content: flex-end;">
                    <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981;"></span>
                    Clinical Mainframe
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Matrix -->
    <div class="admin-stats-matrix">
        <div class="admin-card-glass staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i class="ph-bold ph-users" style="font-size: 1.5rem;"></i>
                </div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Total Registry</div>
            </div>
            <div style="font-size: 2.75rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $stats['total_users'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 500;">Active accounts across all roles</div>
        </div>

        <div class="admin-card-glass staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="ph-bold ph-student" style="font-size: 1.5rem;"></i>
                </div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Student Base</div>
            </div>
            <div style="font-size: 2.75rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $stats['students_count'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 500;">Enrolled student profiles</div>
        </div>

        <div class="admin-card-glass staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="ph-bold ph-chalkboard-teacher" style="font-size: 1.5rem;"></i>
                </div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Counselor Staff</div>
            </div>
            <div style="font-size: 2.75rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $stats['counselors_count'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 500;">Active clinical responders</div>
        </div>

        <div class="admin-card-glass staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div style="width: 48px; height: 48px; background: rgba(244, 63, 94, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="ph-bold ph-calendar-check" style="font-size: 1.5rem;"></i>
                </div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Interventions</div>
            </div>
            <div style="font-size: 2.75rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $stats['total_appointments'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 500;">Cumulative appointments</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem;" class="staggered">
        <!-- Recent Security Logs -->
        <section class="admin-card-glass" style="padding: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em;">Registry Sentinel</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Recent system access and authentication logs.</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="btn-secondary" style="padding: 0.6rem 1.25rem; font-size: 0.8rem;">Full Audit Log</a>
            </div>

            <div class="space-y-3">
                @forelse($stats['recent_logins'] as $log)
                    <div class="activity-feed-item staggered-row">
                        <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-dim);">
                            <i class="ph ph-user-circle" style="font-size: 1.25rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <span style="font-weight: 800; color: var(--text); font-size: 0.95rem;">{{ $log->user->full_name }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">{{ $log->login_time->diffForHumans() }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.2rem;">
                                {{ $log->user->user_type }} login from {{ $log->ip_address }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 4rem 2rem;">
                        <i class="ph ph-shield-check" style="font-size: 3rem; color: var(--text-muted); opacity: 0.2; margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-muted); font-weight: 600;">No recent security events detected.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Management Protocols -->
        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            <!-- Quick Management -->
            <section class="admin-card-glass health-glow" style="background: linear-gradient(135deg, var(--surface-solid) 0%, rgba(99, 102, 241, 0.05) 100%);">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 2rem; letter-spacing: -0.01em;">Execution Protocols</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="{{ route('admin.users.index') }}" class="activity-feed-item" style="margin-bottom: 0; text-decoration: none;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <i class="ph ph-user-list" style="font-size: 1.1rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; color: var(--text); font-size: 0.9rem;">Manage User Registry</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">Edit student profiles and accounts</div>
                        </div>
                        <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
                    </a>

                    <a href="{{ route('admin.staff.index') }}" class="activity-feed-item" style="margin-bottom: 0; text-decoration: none;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                            <i class="ph ph-identification-card" style="font-size: 1.1rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; color: var(--text); font-size: 0.9rem;">Counselor Staffing</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">Manage response personnel</div>
                        </div>
                        <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
                    </a>

                    <a href="{{ route('counselor.availability') }}" class="activity-feed-item" style="margin-bottom: 0; text-decoration: none;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                            <i class="ph ph-clock" style="font-size: 1.1rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; color: var(--text); font-size: 0.9rem;">Global Availability</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600;">Configure clinic operational hours</div>
                        </div>
                        <i class="ph ph-caret-right" style="color: var(--text-muted);"></i>
                    </a>
                </div>
            </section>

            <!-- System Integrity -->
            <section class="admin-card-glass">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem;">Core Vitality</h3>
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="ph-bold ph-database" style="color: var(--primary);"></i>
                            <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-dim);">Mainframe Sync</span>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px;">NOMINAL</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="ph-bold ph-shield-check" style="color: var(--primary);"></i>
                            <span style="font-size: 0.9rem; font-weight: 700; color: var(--text-dim);">Authentication</span>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 0.25rem 0.6rem; border-radius: 6px;">ENFORCED</span>
                    </div>
                </div>
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Kernel Load</span>
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text);">OPTIMAL</span>
                    </div>
                    <div style="height: 6px; background: var(--surface-2); border-radius: 3px; overflow: hidden;">
                        <div style="width: 14%; height: 100%; background: var(--primary); border-radius: 3px;"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
