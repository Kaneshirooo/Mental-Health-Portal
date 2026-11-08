@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 768px) {
        .container { padding: 0 0 3rem !important; }
        .res-header { flex-direction: column !important; align-items: flex-start !important; gap: 1rem !important; margin-bottom: 2rem !important; }
        .res-header h1 { font-size: 1.85rem !important; }
        .hotline-row { grid-template-columns: 1fr !important; gap: 1rem !important; }
        .hotline-row > div { padding: 1.75rem 1.25rem !important; border-radius: 24px !important; }
        .module-row { grid-template-columns: 1fr !important; gap: 1.5rem !important; }
        .module-row > div { border-radius: 28px !important; }
        .module-row > div > div:first-child { height: auto !important; min-height: 160px !important; padding: 2rem 1.5rem !important; }
        .module-row > div > div:first-child h3 { font-size: 1.65rem !important; }
        .module-row > div > div:last-child { padding: 1.75rem 1.25rem !important; }
        .guides-section { padding: 2rem 1.25rem !important; border-radius: 24px !important; margin-bottom: 3rem !important; }
        .guides-section > div:last-child { grid-template-columns: 1fr !important; gap: 1rem !important; }
        .cta-section { padding: 2.5rem 1.5rem !important; border-radius: 24px !important; }
        .cta-section h2 { font-size: 1.85rem !important; }
        .cta-section .cta-btns { flex-direction: column !important; gap: 0.75rem !important; }
        .cta-section .cta-btns a { width: 100% !important; text-align: center !important; padding: 1rem 1.5rem !important; }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: var(--container-max, 1320px); margin: 0 auto; padding-bottom: 3.5rem;">
    
    <div class="res-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1.25rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;">Wellness Library</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: var(--text); margin: 0 0 0.5rem; letter-spacing: -0.04em;">Wellness Resources</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin: 0;">Simple guides and calming activities made for students.</p>
        </div>
        <div style="padding: 0.75rem 1.5rem; border-radius: 50px; background: var(--surface-2); color: var(--primary); font-weight: 800; font-size: 0.82rem; display: flex; align-items: center; gap: 0.6rem; border: 1px solid var(--border);">
            <span>📖</span> Comprehensive Library
        </div>
    </div>

    <!-- Hotlines -->
    <div style="margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem;">
            <div style="width: 6px; height: 26px; background: #dc2626; border-radius: 4px;"></div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: var(--text); margin: 0;">Need Help Right Now?</h2>
        </div>
        <div class="hotline-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div style="background: var(--surface-solid); border-radius: 40px; padding: 4rem 3rem; border: 1.5px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: rgba(220, 38, 38, 0.03); border-radius: 0 0 0 100%;"></div>
                <div style="width: 60px; height: 60px; border-radius: 18px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 2.5rem; box-shadow: 0 10px 20px rgba(220, 38, 38, 0.1);">📞</div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 1rem;">Campus Crisis Hotline</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; margin-bottom: 2.5rem; font-weight: 600; line-height: 1.6;">Right-away help on campus when feelings get too heavy.</p>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: #dc2626; letter-spacing: -0.02em;">(02) 8888-1234</div>
            </div>
            <div style="background: var(--surface-solid); border-radius: 40px; padding: 4rem 3rem; border: 1.5px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: rgba(22, 163, 74, 0.03); border-radius: 0 0 0 100%;"></div>
                <div style="width: 60px; height: 60px; border-radius: 18px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 2.5rem; box-shadow: 0 10px 20px rgba(22, 163, 74, 0.1);">💬</div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 1rem;">Counselor Chatline</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; margin-bottom: 2.5rem; font-weight: 600; line-height: 1.6;">Message us about everyday worries and small questions.</p>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: #16a34a; letter-spacing: -0.02em;">+63 917 123 4567</div>
            </div>
            <div style="background: var(--surface-solid); border-radius: 40px; padding: 4rem 3rem; border: 1.5px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: rgba(79, 70, 229, 0.03); border-radius: 0 0 0 100%;"></div>
                <div style="width: 60px; height: 60px; border-radius: 18px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 2.5rem; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.1);">🌐</div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 1rem;">Youth Help Network</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; margin-bottom: 2.5rem; font-weight: 600; line-height: 1.6;">A 24/7 list of youth helplines around the country.</p>
                <div style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--primary); letter-spacing: -0.02em;">0917-558-4673</div>
            </div>
        </div>
    </div>

    <!-- Educational Resource Matrix -->
    <div style="margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.75rem;">
            <div style="width: 6px; height: 26px; background: var(--primary); border-radius: 4px;"></div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; color: var(--text); margin: 0;">Resilience Training Modules</h2>
        </div>
        <div class="module-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Module 01 -->
            <div style="background: var(--surface-solid); border-radius: 48px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow); cursor: pointer;">
                <div style="height: 240px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 4rem; color: white; position: relative;">
                    <div style="position: absolute; bottom: -30px; right: 20px; font-size: 10rem; opacity: 0.1; font-weight: 900; pointer-events: none;">01</div>
                    <div style="font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.15em; opacity: 0.7; margin-bottom: 1.5rem;">Calm Thinking — Part 1</div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; line-height: 1.1;">Clearer Thoughts for Heavy Days</h3>
                </div>
                <div style="padding: 4rem;">
                    <p style="color: var(--text-dim); font-weight: 600; line-height: 1.8; margin-bottom: 3rem; font-size: 1.1rem;">Easy steps to slow down worried thoughts during busy school days — breathing breaks and splitting big tasks into tiny ones.</p>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 0.75rem;">Start Learning <span style="font-size: 1.25rem;">→</span></span>
                        <span style="padding: 0.5rem 1rem; border-radius: 12px; background: var(--surface-2); font-size: 0.8rem; font-weight: 700; color: var(--text-dim);">12m Est.</span>
                    </div>
                </div>
            </div>
            <!-- Module 02 -->
            <div style="background: var(--surface-solid); border-radius: 48px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow); cursor: pointer;">
                <div style="height: 240px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 4rem; color: white; position: relative;">
                    <div style="position: absolute; bottom: -30px; right: 20px; font-size: 10rem; opacity: 0.1; font-weight: 900; pointer-events: none;">02</div>
                    <div style="font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.15em; opacity: 0.7; margin-bottom: 1.5rem;">Feel Better — Part 2</div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; line-height: 1.1;">Steadier Feelings, Kinder Days</h3>
                </div>
                <div style="padding: 4rem;">
                    <p style="color: var(--text-dim); font-weight: 600; line-height: 1.8; margin-bottom: 3rem; font-size: 1.1rem;">Gentle ways to ride out big feelings — kindness to yourself, good people around you, and small daily resets.</p>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 800; color: #10b981; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 0.75rem;">Start Learning <span style="font-size: 1.25rem;">→</span></span>
                        <span style="padding: 0.5rem 1rem; border-radius: 12px; background: var(--surface-2); font-size: 0.8rem; font-weight: 700; color: var(--text-dim);">18m Est.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Institutional Directives -->
    <div class="guides-section" style="background: var(--surface-2); border-radius: 36px; padding: 3rem 2.5rem; border: 1px solid var(--border); margin-bottom: 3.5rem;">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 2.5rem auto;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.85rem; font-weight: 800; color: var(--text); margin-bottom: 0.75rem;">Guides & How-Tos</h2>
            <p style="color: var(--text-dim); font-weight: 500; font-size: 1.05rem; margin: 0;">Simple guides to help you take care of yourself and know where to go for help.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
            <div style="background: var(--surface-solid); padding: 2.5rem; border-radius: 32px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 1.5rem;">
                <div style="width: 50px; height: 50px; border-radius: 15px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📄</div>
                <div>
                    <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Wellness Manual</h4>
                    <p style="font-size: 0.85rem; color: var(--text-dim); font-weight: 600; margin-bottom: 1rem;">Full institutional guide</p>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">Download PDF</span>
                </div>
            </div>
            <div style="background: var(--surface-solid); padding: 2.5rem; border-radius: 32px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 1.5rem;">
                <div style="width: 50px; height: 50px; border-radius: 15px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📜</div>
                <div>
                    <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Crisis Steps</h4>
                    <p style="font-size: 0.85rem; color: var(--text-dim); font-weight: 600; margin-bottom: 1rem;">What to do right away in an emergency</p>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">View Guide</span>
                </div>
            </div>
            <div style="background: var(--surface-solid); padding: 2.5rem; border-radius: 32px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 1.5rem;">
                <div style="width: 50px; height: 50px; border-radius: 15px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📊</div>
                <div>
                    <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Annual Insight</h4>
                    <p style="font-size: 0.85rem; color: var(--text-dim); font-weight: 600; margin-bottom: 1rem;">Community wellness stats</p>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">View Archive</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA: Diagnostic Check-in -->
    <div class="cta-section" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 36px; padding: 4rem 2.5rem; text-align: center; color: white; box-shadow: 0 25px 50px rgba(13, 148, 136, 0.2); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -80px; left: -80px; width: 300px; height: 300px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 1;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: clamp(1.85rem, 3.5vw, 2.75rem); font-weight: 800; margin-bottom: 1rem;">Ready to Check In With Yourself?</h2>
            <p style="font-size: 1.1rem; font-weight: 500; color: rgba(255,255,255,0.85); margin-bottom: 2.5rem; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">Answer a few short questions to see how you're doing. It only takes a few minutes — and it's the first step to feeling better.</p>
            <div class="cta-btns" style="display: flex; gap: 1.25rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('student.assessment') }}" style="padding: 1rem 2.5rem; border-radius: 50px; background: white; color: var(--primary); font-weight: 800; text-decoration: none; font-size: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">START CHECK-IN</a>
                <a href="{{ route('student.notes.index') }}" style="padding: 1rem 2.5rem; border-radius: 50px; background: rgba(255,255,255,0.15); color: white; font-weight: 800; text-decoration: none; font-size: 1rem; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">MESSAGE A COUNSELOR</a>
            </div>
        </div>
    </div>
</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© {{ date('Y') }} PSU Student Wellness • Here for You, Every Day.</p>
</footer>
@endsection
