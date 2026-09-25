@extends('layouts.app')

@push('styles')
<style>
    .student-row {
        background: var(--surface-solid);
        border-radius: 18px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .student-row:hover {
        background: var(--surface-2);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .risk-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        font-weight: 800;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-width: 1px;
        border-style: solid;
    }

    .filter-control {
        padding: 0.8rem 1.1rem;
        border-radius: 14px;
        border: 1.5px solid var(--border);
        background: var(--surface-solid);
        color: var(--text);
        font-weight: 700;
        font-size: 0.85rem;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1320px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-bold ph-users-three" style="font-size: 1.1rem;"></i> Clinical Directory
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Student Registry</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Comprehensive registry of student wellness nodes and clinical data.</p>
        </div>
        <div style="display: flex; gap: 1.25rem; align-items: center;">
            <div style="text-align: right; padding-right: 1.5rem; border-right: 1px solid var(--border);">
                <div style="font-size: 1.65rem; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif;">{{ count($students) }}</div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em;">Total Nodes</div>
            </div>
            <button onclick="exportCSV()" class="btn-secondary" style="padding: 0.85rem 1.5rem; border-radius: 14px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-bold ph-file-csv" style="font-size: 1.1rem;"></i> Export Dataset
            </button>
        </div>
    </header>

    <!-- Controls -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 1.5rem 1.75rem; margin-bottom: 2.5rem; display: flex; gap: 1.25rem; align-items: center; box-shadow: var(--shadow-sm); flex-wrap: wrap;">
        <div style="flex: 1; min-width: 280px; position: relative;">
            <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
            <input type="text" id="liveSearch" oninput="liveFilter()" placeholder="Identify student by name, ID, or clinical token..." style="width: 100%; padding: 0.85rem 1.25rem 0.85rem 3.25rem; border-radius: 14px; border: 1.5px solid var(--border); font-size: 0.92rem; font-weight: 500; background: var(--surface-2); color: var(--text); outline: none; transition: all 0.25s ease;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--surface-solid)'; this.style.boxShadow='0 0 0 4px var(--primary-glow)';" onblur="this.style.borderColor='var(--border)'; this.style.background='var(--surface-2)'; this.style.boxShadow='none';">
        </div>
        
        <form method="GET" id="filterForm" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--surface-2); padding: 0.4rem 0.85rem; border-radius: 14px; border: 1.5px solid var(--border);">
                <span style="font-size: 0.68rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase;">From</span>
                <input type="date" name="start_date" value="{{ $start_date }}" style="background: transparent; border: none; color: var(--text); font-weight: 700; font-size: 0.85rem; outline: none; cursor: pointer;">
                <span style="font-size: 0.68rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase;">To</span>
                <input type="date" name="end_date" value="{{ $end_date }}" style="background: transparent; border: none; color: var(--text); font-weight: 700; font-size: 0.85rem; outline: none; cursor: pointer;">
            </div>

            <select name="semester" class="filter-control">
                <option value="">All Semesters</option>
                @foreach ($semesters as $s)
                    <option value="{{ $s }}" {{ $semester === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <select name="course" class="filter-control">
                <option value="">All Courses</option>
                @foreach ($courses as $c)
                    <option value="{{ $c }}" {{ $course === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>

            <select name="filter" class="filter-control">
                <option value="">All Risk Tiers</option>
                @foreach (['Low', 'Moderate', 'High', 'Critical'] as $lvl)
                    <option value="{{ $lvl }}" {{ $filter === $lvl ? 'selected' : '' }}>{{ $lvl }} Priority</option>
                @endforeach
            </select>

            @if ($search || $filter || $course || $semester || $start_date || $end_date)
                <a href="{{ route('counselor.students.index') }}" style="padding: 0.8rem 1.25rem; border-radius: 14px; background: rgba(239, 68, 68, 0.08); color: #ef4444; font-weight: 800; text-decoration: none; display: flex; align-items: center; font-size: 0.8rem; border: 1px solid rgba(239, 68, 68, 0.2);">Reset All</a>
            @endif
        </form>
    </div>

    <!-- High Risk Alert Section -->
    @if($high_risk_by_course->isNotEmpty())
    <div class="staggered" style="margin-bottom: 3.5rem;">
        <div style="background: rgba(239, 68, 68, 0.04); border: 1.5px solid rgba(239, 68, 68, 0.2); border-radius: 32px; padding: 2.25rem; box-shadow: 0 15px 35px rgba(239, 68, 68, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 900; color: #ef4444; margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="ph-bold ph-warning-octagon" style="font-size: 1.5rem;"></i> High Risk Concentration
                    </h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 500; margin-top: 0.4rem;">Courses with students triggering critical wellness alerts during requested period.</p>
                </div>
                <div style="background: #ef4444; color: #ffffff; padding: 0.45rem 1rem; border-radius: 12px; font-weight: 900; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">Priority Oversight</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1.25rem;">
                @foreach($high_risk_by_course as $c_name => $count)
                <div style="background: var(--surface-solid); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; transition: all 0.25s ease;" onmouseover="this.style.transform='translateY(-4px)'; this.style.borderColor='rgba(239, 68, 68, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';" class="staggered-row">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.3rem;">{{ $c_name ?: 'General' }}</div>
                        <div style="font-size: 1.65rem; font-weight: 900; color: var(--text); display: flex; align-items: baseline; gap: 0.4rem; font-family: 'Outfit', sans-serif;">
                            {{ $count }}
                            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted); font-family: 'Inter', sans-serif;">Cases</span>
                        </div>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="ph-bold ph-trend-up"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Analytics Section -->
    <div class="staggered" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 3.5rem;">
        <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 900; color: var(--text); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="ph-bold ph-chart-pie-slice" style="color: var(--primary);"></i> Clinical Risk Distribution
            </h3>
            <div style="height: 250px; position: relative;">
                <canvas id="riskChart"></canvas>
            </div>
        </div>

        <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 900; color: var(--text); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="ph-bold ph-graduation-cap" style="color: #6366f1;"></i> Students by Course
            </h3>
            <div style="height: 250px; position: relative;">
                <canvas id="courseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: var(--shadow-md);">
        <div style="overflow-x: auto;">
            <table id="studentsTable" style="width: 100%; border-collapse: separate; border-spacing: 0 0.65rem;">
                <thead>
                    <tr style="text-transform: uppercase; letter-spacing: 0.12em; font-size: 0.68rem; color: var(--text-dim); font-weight: 900;">
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Student Identity</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Clinical ID</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Department</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Wellness Index</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Risk Classification</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($students as $student)
                    @php
                        $s_name = $student->full_name ?? 'Anonymous Node';
                        $initial = strtoupper(substr($s_name, 0, 1));
                    @endphp
                    <tr class="student-row"
                        style="opacity: 1;"
                        data-name="{{ strtolower($s_name) }}"
                        data-email="{{ strtolower($student->email ?? '') }}"
                        data-roll="{{ strtolower($student->roll_number ?? '') }}">
                        <td style="padding: 1.25rem 1.5rem; border-top-left-radius: 18px; border-bottom-left-radius: 18px;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--surface-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1.1rem;">
                                    {{ $initial }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 800; color: var(--text); font-size: 0.98rem;">{{ $s_name }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">{{ $student->email ?? '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <span style="font-weight: 800; background: var(--surface-2); padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.75rem; color: var(--text-muted); border: 1px solid var(--border);">
                                {{ $student->roll_number ?? '---' }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">
                            {{ $student->department ?? 'General' }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="flex: 1; min-width: 80px; height: 8px; background: var(--surface-2); border-radius: 100px; overflow: hidden; border: 1px solid var(--border);">
                                    @php $score = $student->latestAssessment?->overall_score ?? 0; @endphp
                                    <div style="width: {{ $score }}%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-dark)); border-radius: 100px;"></div>
                                </div>
                                <span style="font-weight: 900; color: var(--primary); font-size: 0.95rem;">{{ $score }}%</span>
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem;">
                            @php $risk = $student->latestAssessment?->risk_level; @endphp
                            @if ($risk)
                                @php
                                    $colors = [
                                        'Low' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => '#10b981', 'border' => 'rgba(16, 185, 129, 0.3)'],
                                        'Moderate' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => '#f59e0b', 'border' => 'rgba(245, 158, 11, 0.3)'],
                                        'High' => ['bg' => 'rgba(249, 115, 22, 0.1)', 'text' => '#f97316', 'border' => 'rgba(249, 115, 22, 0.3)'],
                                        'Critical' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'text' => '#ef4444', 'border' => 'rgba(239, 68, 68, 0.3)']
                                    ];
                                    $c = $colors[$risk] ?? ['bg' => 'var(--surface-2)', 'text' => 'var(--text-dim)', 'border' => 'var(--border)'];
                                @endphp
                                <span class="risk-pill" style="background: {{ $c['bg'] }}; color: {{ $c['text'] }}; border-color: {{ $c['border'] }};">
                                    {{ $risk }}
                                </span>
                            @else
                                <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); opacity: 0.6;">UNCATEGORIZED</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right; border-top-right-radius: 18px; border-bottom-right-radius: 18px;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <a href="{{ route('counselor.students.show', $student->user_id ?? 0) }}" class="btn-sm" style="color: var(--primary); background: var(--primary-glow); border: 1.5px solid var(--primary-light); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.5rem 0.85rem; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.2s ease;">
                                    <span>View</span>
                                    <i class="ph-bold ph-caret-right"></i>
                                </a>
                                <button type="button" onclick="openEditModal({{ json_encode($student) }})" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1.5px solid rgba(99, 102, 241, 0.25); font-weight: 800; font-size: 0.75rem; padding: 0.5rem 0.75rem; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;" title="Edit Student Profile">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                </button>
                                <button type="button" onclick="openDeleteModal('{{ $student->user_id }}', '{{ e($s_name) }}')" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1.5px solid rgba(239, 68, 68, 0.25); font-weight: 800; font-size: 0.75rem; padding: 0.5rem 0.75rem; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;" title="Delete Student Record">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; width: 100%; max-width: 600px; padding: 2.25rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative;">
        <button type="button" onclick="closeEditModal()" style="position: absolute; top: 1.25rem; right: 1.25rem; background: var(--surface-2); border: 1px solid var(--border); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-dim); cursor: pointer; transition: all 0.2s;">
            <i class="ph-bold ph-x" style="font-size: 1.1rem;"></i>
        </button>

        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 16px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                <i class="ph-bold ph-pencil-simple-line"></i>
            </div>
            <div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 900; color: var(--text); margin: 0;">Edit Student Profile</h3>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.2rem 0 0; font-weight: 500;">Update student clinical registry and personal information.</p>
            </div>
        </div>

        <form id="editStudentForm" onsubmit="submitEditStudent(event)" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_student_id" name="student_id">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Full Name *</label>
                    <input type="text" id="edit_full_name" name="full_name" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Email Address *</label>
                    <input type="email" id="edit_email" name="email" required style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Student ID / Roll No.</label>
                    <input type="text" id="edit_roll_number" name="roll_number" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Department</label>
                    <select id="edit_department" name="department" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                        <option value="">Select Department / College</option>
                        <option value="CHMBAC">CHMBAC</option>
                        <option value="COA">COA</option>
                        <option value="CTE">CTE</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Course</label>
                    <input type="text" id="edit_course" name="course" placeholder="e.g. BS Information Technology" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Year & Section</label>
                    <input type="text" id="edit_year_section" name="year_section" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">Semester</label>
                    <input type="text" id="edit_semester" name="semester" style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 600; font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                <button type="button" onclick="closeEditModal()" class="btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">Cancel</button>
                <button type="submit" id="btnEditSubmit" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Student Modal -->
<div id="deleteStudentModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; width: 100%; max-width: 480px; padding: 2.25rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; text-align: center;">
        <div style="width: 60px; height: 60px; border-radius: 20px; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.25rem;">
            <i class="ph-bold ph-warning-octagon"></i>
        </div>

        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 900; color: var(--text); margin: 0 0 0.5rem;">Delete Student Account?</h3>
        <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0 0 1.5rem; font-weight: 500; line-height: 1.5;">
            Are you sure you want to delete <strong id="deleteStudentName" style="color: var(--text);">Student</strong>? This will permanently remove their records, assessment history, and clinical notes. This action cannot be undone.
        </p>

        <form id="deleteStudentForm" onsubmit="submitDeleteStudent(event)" style="display: flex; gap: 0.75rem; justify-content: center;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_student_id" name="student_id">
            <button type="button" onclick="closeDeleteModal()" class="btn-secondary" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">Cancel</button>
            <button type="submit" id="btnDeleteSubmit" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; background: #ef4444; color: white; border: none; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc2626';" onmouseout="this.style.background='#ef4444';">Delete Permanently</button>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 35, opacity: 0, duration: 0.9, stagger: 0.12, ease: "expo.out", clearProps: "all" });
        // Only animate rows that are within the viewport to avoid invisible off-screen rows
        const rows = document.querySelectorAll('.student-row');
        rows.forEach((row, i) => {
            const rect = row.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                gsap.from(row, { x: -20, opacity: 0, duration: 0.5, delay: 0.3 + i * 0.04, ease: "expo.out", clearProps: "all" });
            }
        });
    }

    // Risk Distribution Chart
    const riskData = @json($risk_distribution);
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(riskData),
            datasets: [{
                data: Object.values(riskData),
                backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444', '#94a3b8'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: '800', size: 11 } } }
            }
        }
    });

    // Course Distribution Chart
    const courseData = @json($course_distribution);
    const courseCtx = document.getElementById('courseChart').getContext('2d');
    new Chart(courseCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(courseData).map(k => k || 'General'),
            datasets: [{
                label: 'Student Count',
                data: Object.values(courseData),
                backgroundColor: '#6366f1',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { weight: '700' } } },
                x: { grid: { display: false }, ticks: { font: { weight: '700' } } }
            }
        }
    });
});

const refreshRegistry = async () => {
    const form = document.querySelector('#filterForm');
    const fd = new FormData(form);
    const params = new URLSearchParams(fd);
    
    // Add live search to params if exists
    const q = document.getElementById('liveSearch').value;
    if (q) params.set('search', q);
    
    const url = new URL(window.location.href);
    for (const [key, value] of params) {
        if (value) url.searchParams.set(key, value);
        else url.searchParams.delete(key);
    }
    
    history.pushState({}, '', url);
    document.body.style.opacity = '0.75';

    try {
        const res = await fetch(url);
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Update table body
        const newBody = doc.querySelector('#tableBody');
        const oldBody = document.querySelector('#tableBody');
        if (newBody && oldBody) {
            oldBody.innerHTML = newBody.innerHTML;
            if (window.gsap) {
                gsap.from('#tableBody tr', { x: -20, opacity: 0, duration: 0.5, stagger: 0.04, ease: "power2.out" });
            }
        }
    } catch (err) {
        console.error(err);
        if (window.App && window.App.toast) {
            App.toast({ type: 'error', title: 'Filter Failed', message: 'Could not sync clinical data.' });
        }
    } finally {
        document.body.style.opacity = '1';
    }
};

// Update filter form to use AJAX
document.querySelectorAll('#filterForm select, #filterForm input').forEach(el => {
    el.removeAttribute('onchange');
    el.addEventListener('change', refreshRegistry);
});

let searchTimeout;
document.getElementById('liveSearch').addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(refreshRegistry, 400);
});

function exportCSV() {
    const table = document.getElementById('studentsTable');
    const rows = [...table.querySelectorAll('tr')];
    const csv = rows.map(r =>
        [...r.cells].slice(0, 5).map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(',')
    ).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'students_registry_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

// Edit & Delete Modal Functions
function openEditModal(student) {
    document.getElementById('edit_student_id').value = student.user_id;
    document.getElementById('edit_full_name').value = student.full_name || '';
    document.getElementById('edit_email').value = student.email || '';
    document.getElementById('edit_roll_number').value = student.roll_number || '';
    document.getElementById('edit_department').value = student.department || '';
    document.getElementById('edit_course').value = student.course || '';
    document.getElementById('edit_year_section').value = student.year_section || '';
    document.getElementById('edit_semester').value = student.semester || '';
    
    const modal = document.getElementById('editStudentModal');
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editStudentModal').style.display = 'none';
}

async function submitEditStudent(e) {
    e.preventDefault();
    const studentId = document.getElementById('edit_student_id').value;
    const form = document.getElementById('editStudentForm');
    const btn = document.getElementById('btnEditSubmit');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = 'Saving...';
    
    const formData = new FormData(form);

    try {
        const res = await fetch(`/counselor/students/${studentId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await res.json();
        if (data.success) {
            if (window.App && window.App.toast) {
                App.toast({ type: 'success', title: 'Success', message: data.message });
            } else {
                alert(data.message);
            }
            closeEditModal();
            refreshRegistry();
        } else {
            alert(data.error || 'Failed to update student.');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while updating the student.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function openDeleteModal(id, name) {
    document.getElementById('delete_student_id').value = id;
    document.getElementById('deleteStudentName').innerText = name;
    document.getElementById('deleteStudentModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteStudentModal').style.display = 'none';
}

async function submitDeleteStudent(e) {
    e.preventDefault();
    const studentId = document.getElementById('delete_student_id').value;
    const btn = document.getElementById('btnDeleteSubmit');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = 'Deleting...';

    const formData = new FormData();
    formData.append('_method', 'DELETE');

    try {
        const res = await fetch(`/counselor/students/${studentId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await res.json();
        if (data.success) {
            if (window.App && window.App.toast) {
                App.toast({ type: 'success', title: 'Deleted', message: data.message });
            } else {
                alert(data.message);
            }
            closeDeleteModal();
            refreshRegistry();
        } else {
            alert(data.error || 'Failed to delete student.');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while deleting student.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
@endpush
@endsection

