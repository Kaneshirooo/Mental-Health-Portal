@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <h1 class="profile-title text-3xl font-extrabold flex items-center gap-3">
                <i class="ph-fill ph-user-circle text-emerald-500"></i>
                Account Profile
            </h1>
            <p class="profile-subtitle mt-2">Manage your academic information and contact details for clinical records.</p>
        </div>

        <div class="profile-card rounded-3xl overflow-hidden p-8 md:p-12">
            <form id="profileForm" action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Profile Picture Upload Section -->
                <div class="flex flex-col items-center mb-12 pb-10 divider-line">
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 avatar-border shadow-2xl relative">
                            @if($user->profile_picture)
                                <img id="avatar-preview" src="{{ asset('storage/' . $user->profile_picture) }}" class="w-full h-full object-cover">
                            @else
                                <div id="avatar-placeholder" class="w-full h-full bg-emerald-500 flex items-center justify-center text-white text-4xl font-black">
                                    @php
                                        $nameParts = explode(' ', $user->full_name);
                                        echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                    @endphp
                                </div>
                                <img id="avatar-preview" src="#" class="w-full h-full object-cover hidden">
                            @endif
                            
                            <label for="profile_picture" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                                <i class="ph-bold ph-camera text-white text-2xl"></i>
                            </label>
                        </div>
                        <input type="file" id="profile_picture" name="profile_picture" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="mt-4 text-center">
                        <p class="section-label text-sm font-bold">Profile Picture</p>
                        <p class="text-xs text-slate-500">JPG, PNG up to 2MB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="input-label text-[10px] font-black uppercase tracking-widest">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" 
                            class="profile-input w-full px-5 py-4 rounded-2xl font-bold transition-all outline-none"
                            placeholder="Your full name">
                    </div>

                    <!-- Student ID (Read Only) -->
                    <div class="space-y-2">
                        <label class="input-label text-[10px] font-black uppercase tracking-widest">Student ID (Locked)</label>
                        <input type="text" value="{{ $user->roll_number }}" readonly 
                            class="profile-input readonly w-full px-5 py-4 rounded-2xl font-bold outline-none cursor-not-allowed">
                    </div>

                    <!-- Email (Read Only) -->
                    <div class="space-y-2">
                        <label class="input-label text-[10px] font-black uppercase tracking-widest">Email Address (Locked)</label>
                        <input type="text" value="{{ $user->email }}" readonly 
                            class="profile-input readonly w-full px-5 py-4 rounded-2xl font-bold outline-none cursor-not-allowed">
                    </div>

                    <!-- Contact Number -->
                    <div class="space-y-2">
                        <label class="input-label text-[10px] font-black uppercase tracking-widest">Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" 
                            class="profile-input w-full px-5 py-4 rounded-2xl font-bold transition-all outline-none"
                            placeholder="09xxxxxxxxx">
                    </div>
                </div>

                <div class="pt-10 border-t divider-line">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-emerald-600 mb-8">Academic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Course -->
                        <div class="space-y-2">
                            <label class="input-label text-[10px] font-black uppercase tracking-widest">Academic Course</label>
                            <select name="course" 
                                class="profile-input w-full px-5 py-4 rounded-2xl font-bold transition-all outline-none appearance-none">
                                <option value="BS Information Technology" {{ old('course', $user->course) === 'BS Information Technology' ? 'selected' : '' }}>BS Information Technology</option>
                                <option value="BS Computer Science" {{ old('course', $user->course) === 'BS Computer Science' ? 'selected' : '' }}>BS Computer Science</option>
                                <option value="BS Business Administration" {{ old('course', $user->course) === 'BS Business Administration' ? 'selected' : '' }}>BS Business Administration</option>
                                <option value="BS Psychology" {{ old('course', $user->course) === 'BS Psychology' ? 'selected' : '' }}>BS Psychology</option>
                                <option value="BS Education" {{ old('course', $user->course) === 'BS Education' ? 'selected' : '' }}>BS Education</option>
                                <option value="Other" {{ old('course', $user->course) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Semester -->
                        <div class="space-y-2">
                            <label class="input-label text-[10px] font-black uppercase tracking-widest">Current Semester</label>
                            <select name="semester" 
                                class="profile-input w-full px-5 py-4 rounded-2xl font-bold transition-all outline-none appearance-none">
                                <option value="1st Semester" {{ old('semester', $user->semester) === '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                                <option value="2nd Semester" {{ old('semester', $user->semester) === '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                                <option value="Midyear" {{ old('semester', $user->semester) === 'Midyear' ? 'selected' : '' }}>Midyear</option>
                            </select>
                        </div>

                        <!-- Department -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="input-label text-[10px] font-black uppercase tracking-widest">Department / College</label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}" 
                                class="profile-input w-full px-5 py-4 rounded-2xl font-bold transition-all outline-none"
                                placeholder="e.g. College of Computing">
                        </div>
                    </div>
                </div>

                <div class="pt-12 flex justify-end">
                    <button type="submit" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-emerald-500/20 transition-all hover:-translate-y-1 active:translate-y-0 flex items-center gap-3 uppercase tracking-widest text-[10px]">
                        <i class="ph-bold ph-floppy-disk text-lg"></i>
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* =============================================
   LIGHT MODE (default)
   ============================================= */
.profile-title    { color: #0f172a; }
.profile-subtitle { color: #64748b; }
.profile-card     { background: #ffffff; border: 2px solid #e2e8f0; box-shadow: 0 4px 32px rgba(0,0,0,0.06); }
.divider-line     { border-color: #e2e8f0; }
.avatar-border    { border-color: #ffffff; }
.section-label    { color: #1e293b; }
.input-label      { color: #64748b; }
.profile-input    { background: #f8fafc; border: 2px solid #e2e8f0; color: #0f172a; }
.profile-input:focus { border-color: #10b981; background: #ffffff; }
.profile-input.readonly { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }

/* =============================================
   DARK MODE (.dark-mode class on <html>)
   ============================================= */
.dark-mode .profile-title    { color: #f1f5f9; }
.dark-mode .profile-subtitle { color: #94a3b8; }
.dark-mode .profile-card     { background: rgba(30, 41, 59, 0.5); border-color: rgba(255,255,255,0.08); backdrop-filter: blur(20px); }
.dark-mode .divider-line     { border-color: rgba(255,255,255,0.08); }
.dark-mode .avatar-border    { border-color: #1e293b; }
.dark-mode .section-label    { color: #f1f5f9; }
.dark-mode .input-label      { color: #94a3b8; }
.dark-mode .profile-input    { background: rgba(15, 23, 42, 0.6); border-color: rgba(255,255,255,0.1); color: #f8fafc; }
.dark-mode .profile-input:focus { border-color: #10b981; background: rgba(15, 23, 42, 0.8); }
.dark-mode .profile-input.readonly { background: rgba(15, 23, 42, 0.4); color: #94a3b8; border-color: rgba(255,255,255,0.05); }

</style>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const placeholder = document.getElementById('avatar-placeholder');
            
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const originalContent = btn.innerHTML;
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Saving...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        if (data.success) {
            App.toast({ type: 'success', title: 'Updated', message: data.message });

            // Update avatar with server-confirmed URL so refresh shows the saved pic
            if (data.profile_picture_url) {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                if (preview) {
                    preview.src = data.profile_picture_url;
                    preview.classList.remove('hidden');
                }
                if (placeholder) placeholder.classList.add('hidden');
            }

            // Clear the file input so a re-submit doesn't re-upload the same file
            const fileInput = document.getElementById('profile_picture');
            if (fileInput) fileInput.value = '';
        } else {
            App.toast({ type: 'error', title: 'Update Failed', message: data.error || 'Check your information and try again.' });
        }
    } catch (error) {
        App.toast({ type: 'error', title: 'Connection Error', message: 'Failed to reach server. Please try again.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
});
</script>
@endpush
