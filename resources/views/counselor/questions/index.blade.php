@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    <header style="margin-bottom: 2.5rem;">
        <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem;">Pre-Assessment Builder</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin: 0;">Check-in Questions</h1>
        <p style="color: var(--text-muted); font-size: 1rem; margin-top: 0.5rem;">Add, edit, or remove the questions students answer. Changes apply to the next check-in immediately.</p>
    </header>

    @if(session('success'))
        <div style="background: rgba(16,185,129,0.1); border: 1.5px solid rgba(16,185,129,0.35); color: #059669; padding: 1rem 1.25rem; border-radius: 14px; font-weight: 700; margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.5rem; font-size: 0.78rem; font-weight: 800;">
        <a href="{{ route('counselor.questions.index') }}" style="padding: 0.5rem 1rem; border-radius: 100px; text-decoration: none; background: {{ !$category ? 'var(--primary)' : 'var(--surface-2)' }}; color: {{ !$category ? 'white' : 'var(--text-dim)' }}; border: 1px solid var(--border);">All ({{ $questions->count() }})</a>
        @foreach($counts as $cat => $total)
            <a href="{{ route('counselor.questions.index', ['category' => $cat]) }}" style="padding: 0.5rem 1rem; border-radius: 100px; text-decoration: none; background: {{ $category === $cat ? 'var(--primary)' : 'var(--surface-2)' }}; color: {{ $category === $cat ? 'white' : 'var(--text-dim)' }}; border: 1px solid var(--border); text-transform: capitalize;">{{ $cat }} ({{ $total }})</a>
        @endforeach
    </div>

    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 800; color: var(--text); margin-bottom: 1.25rem;">+ Add a question</h2>
        <form method="POST" action="{{ route('counselor.questions.store') }}" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 0.75rem; align-items: end;">
            @csrf
            <div>
                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem;">Area</label>
                <select name="category" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600;">
                    <option value="depression">Low mood</option>
                    <option value="anxiety">Worries</option>
                    <option value="stress">Pressure</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem;">Question (0–3 scale)</label>
                <input type="text" name="question_text" required maxlength="2000" placeholder="e.g. How often did you have trouble sleeping?" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text);">
            </div>
            <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800;">Add</button>
        </form>
    </div>

    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 24px; padding: 2rem; box-shadow: var(--shadow-sm);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 800; color: var(--text); margin-bottom: 1.25rem;">Questions ({{ $questions->count() }})</h2>
        @forelse($questions as $q)
            <form method="POST" action="{{ route('counselor.questions.update', $q->question_id) }}" style="display: grid; grid-template-columns: 50px 140px 1fr auto; gap: 0.75rem; align-items: center; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                @csrf
                @method('PUT')
                <input type="number" name="question_number" value="{{ $q->question_number }}" min="1" title="Order" style="padding: 0.6rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 800; text-align: center;">
                <select name="category" style="padding: 0.6rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; text-transform: capitalize;">
                    @foreach(['depression' => 'Low mood', 'anxiety' => 'Worries', 'stress' => 'Pressure'] as $val => $label)
                        <option value="{{ $val }}" {{ strtolower($q->category) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="question_text" value="{{ $q->question_text }}" required maxlength="2000" style="padding: 0.6rem 0.9rem; border-radius: 10px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text);">
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" title="Save changes" style="background: rgba(16,185,129,0.1); color: #059669; border: 1.5px solid rgba(16,185,129,0.3); padding: 0.55rem 0.9rem; border-radius: 10px; font-weight: 800; cursor: pointer;">Save</button>
                    <button type="button" title="Remove question" onclick="removeQuestion({{ $q->question_id }})" style="background: rgba(239,68,68,0.08); color: #ef4444; border: 1.5px solid rgba(239,68,68,0.25); padding: 0.55rem 0.9rem; border-radius: 10px; font-weight: 800; cursor: pointer;">Remove</button>
                </div>
            </form>
            <form id="del-q-{{ $q->question_id }}" method="POST" action="{{ route('counselor.questions.destroy', $q->question_id) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @empty
            <p style="color: var(--text-dim); font-weight: 600; text-align: center; padding: 2rem;">No questions here yet. Add the first one above.</p>
        @endforelse
        <p style="font-size: 0.78rem; color: var(--text-dim); margin-top: 1.25rem;">Note: removing questions changes future scoring ranges. Past check-ins keep their saved scores.</p>
    </div>
</div>

<script>
async function removeQuestion(id) {
    const ok = await App.confirm({ title: 'Remove this question?', message: 'Students will no longer answer it in new check-ins. Past results keep their scores.', confirmText: 'Yes, Remove', danger: true });
    if (ok) document.getElementById('del-q-' + id).submit();
}
</script>
@endsection
