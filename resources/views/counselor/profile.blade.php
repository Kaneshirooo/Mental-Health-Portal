@extends('layouts.app')

@section('title', 'My Profile — Clinical Portal')

@section('content')
<div class="profile-container" style="max-width: 1000px; margin: 0 auto; padding-bottom: 3rem;">
    
    {{-- Header Banner --}}
    <div class="glass profile-header" style="border-radius: 28px; padding: 2.5rem; margin-bottom: 2rem; border: 1px solid var(--glass-border); box-shadow: var(--shadow-lg); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.03) 100%); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.75rem;">
            <div style="position: relative;">
                <div id="profileAvatarContainer" style="width: 90px; height: 90px; border-radius: 24px; overflow: hidden; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; font-weight: 900; box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3); border: 3px solid var(--surface-solid);">
                    @if($user->profile_picture)
                        <img id="avatarPreview" src="{{ asset('storage/' . $user->profile_picture) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span id="avatarInitials">
                            @php
                                $nameParts = explode(' ', $user->full_name);
                                echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            @endphp
                        </span>
                        <img id="avatarPreview" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    @endif
                </div>
                <label for="profile_picture_input" style="position: absolute; bottom: -4px; right: -4px; width: 34px; height: 34px; background: var(--primary); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); border: 2px solid var(--surface-solid); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="ph-bold ph-camera" style="font-size: 1.1rem;"></i>
                </label>
            </div>
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.85rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.03em; line-height: 1.2;">
                    {{ $user->full_name }}
                </h1>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem; flex-wrap: wrap;">
                    <span style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.75rem; font-weight: 800; padding: 0.3rem 0.8rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <i class="ph-bold ph-shield-check" style="margin-right: 0.25rem;"></i>
                        {{ strtolower($user->user_type->value ?? (string)$user->user_type) === 'admin' ? 'Head Counselor (Admin)' : 'Clinical Counselor' }}
                    </span>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ph-bold ph-envelope-simple"></i> {{ $user->email }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="glass" style="border-radius: 20px; padding: 1.25rem 1.75rem; margin-bottom: 2rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #059669; display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-md);">
            <i class="ph-bold ph-check-circle" style="font-size: 1.6rem; flex-shrink: 0;"></i>
            <span style="font-weight: 700; font-size: 0.95rem;">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Alert --}}
    @if($errors->any())
        <div class="glass" style="border-radius: 20px; padding: 1.25rem 1.75rem; margin-bottom: 2rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; display: flex; align-items: flex-start; gap: 1rem; box-shadow: var(--shadow-md);">
            <i class="ph-bold ph-warning-circle" style="font-size: 1.6rem; flex-shrink: 0; margin-top: 0.1rem;"></i>
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span style="font-weight: 800; font-size: 0.95rem;">Please review the following errors:</span>
                @foreach($errors->all() as $error)
                    <span style="font-size: 0.88rem; font-weight: 600;">• {{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">

        {{-- 1. Personal Information Card --}}
        <div class="glass" style="border-radius: 28px; padding: 2.25rem; border: 1px solid var(--glass-border); background: var(--surface-solid); box-shadow: var(--shadow-md);">
            <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.75rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    <i class="ph-bold ph-user"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: var(--text); margin: 0;">Personal Details</h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Manage your basic identification and contact credentials.</p>
                </div>
            </div>

            <form action="{{ route('counselor.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*" style="display: none;" onchange="handleImagePreview(this)">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g. 09123456789" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Department / Specialization</label>
                        <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g. Guidance & Counseling / Psychology" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.85rem 2rem; border-radius: 14px; font-weight: 800; font-size: 0.9rem; cursor: pointer; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25); display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="ph-bold ph-floppy-disk"></i> Save Profile Details
                    </button>
                </div>
            </form>
        </div>

        {{-- 2. Security Password Card --}}
        <div class="glass" style="border-radius: 28px; padding: 2.25rem; border: 1px solid var(--glass-border); background: var(--surface-solid); box-shadow: var(--shadow-md);">
            <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.75rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    <i class="ph-bold ph-lock-key"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: var(--text); margin: 0;">Change Password</h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Update your account password to maintain security integrity.</p>
                </div>
            </div>

            <form action="{{ route('counselor.profile.password') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Current Password</label>
                        <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='var(--border)'">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">New Password</label>
                        <input type="password" name="new_password" required placeholder="••••••••" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='var(--border)'">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required placeholder="••••••••" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: #6366f1; color: white; border: none; padding: 0.85rem 2rem; border-radius: 14px; font-weight: 800; font-size: 0.9rem; cursor: pointer; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25); display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="ph-bold ph-key"></i> Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Change Email Card --}}
        <div class="glass" style="border-radius: 28px; padding: 2.25rem; border: 1px solid var(--glass-border); background: var(--surface-solid); box-shadow: var(--shadow-md);">
            <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.75rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    <i class="ph-bold ph-envelope"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: var(--text); margin: 0;">Change Email Address</h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Update the email address associated with your counselor account.</p>
                </div>
            </div>

            <form action="{{ route('counselor.profile.email') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Current Email</label>
                        <input type="email" value="{{ $user->email }}" disabled style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text-dim); font-weight: 600; font-size: 0.95rem; cursor: not-allowed;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">New Email Address</label>
                        <input type="email" name="new_email" required placeholder="counselor@psu.edu.ph" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#f59e0b'" onblur="this.style.borderColor='var(--border)'">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Confirm with Current Password</label>
                        <input type="password" name="confirm_password_email" required placeholder="••••••••" style="width: 100%; padding: 0.85rem 1.15rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface); color: var(--text); font-weight: 600; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#f59e0b'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: #f59e0b; color: white; border: none; padding: 0.85rem 2rem; border-radius: 14px; font-weight: 800; font-size: 0.9rem; cursor: pointer; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25); display: flex; align-items: center; gap: 0.5rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="ph-bold ph-envelope-simple"></i> Update Email Address
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function handleImagePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const initials = document.getElementById('avatarInitials');
                const preview = document.getElementById('avatarPreview');
                if (initials) initials.style.display = 'none';
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
