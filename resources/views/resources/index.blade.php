@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Wellness Library</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary); margin-bottom: 0.75rem;">Wellness Resources</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Simple guides and calming activities made for students.</p>
        </div>
        <div style="padding: 1rem 2.5rem; border-radius: 50px; background: var(--surface-2); color: var(--primary); font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--border);">
            <span>📖</span> Comprehensive Library
        </div>
    </div>

    <!-- Hotlines -->
    <div style="margin-bottom: 6rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem;">
            <div style="width: 12px; height: 32px; background: #dc2626; border-radius: 4px;"></div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text);">Need Help Right Now?</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
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
    <div style="margin-bottom: 6rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 3rem;">
            <div style="width: 12px; height: 32px; background: var(--primary); border-radius: 4px;"></div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text);">Resilience Training Modules</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 3rem;">
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
    <div style="background: var(--surface-2); border-radius: 48px; padding: 5rem; border: 1px solid var(--border); margin-bottom: 6rem;">
        <div style="text-align: center; max-width: 800px; margin: 0 auto 5rem auto;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 1.5rem;">Guides & How-Tos</h2>
            <p style="color: var(--text-dim); font-weight: 600; font-size: 1.1rem;">Simple guides to help you take care of yourself and know where to go for help.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
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
    <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 48px; padding: 6rem; text-align: center; color: white; box-shadow: 0 40px 80px rgba(67, 56, 202, 0.25); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -100px; left: -100px; width: 400px; height: 400px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        
        <div style="position: relative; z-index: 1;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 3rem; font-weight: 800; margin-bottom: 1.5rem;">Ready to Check In With Yourself?</h2>
            <p style="font-size: 1.25rem; font-weight: 500; color: rgba(255,255,255,0.8); margin-bottom: 4rem; max-width: 700px; margin-left: auto; margin-right: auto;">Answer a few short questions to see how you're doing. It only takes a few minutes — and it's the first step to feeling better.</p>
            <div style="display: flex; gap: 2rem; justify-content: center;">
                <a href="{{ route('student.assessment') }}" style="padding: 1.5rem 4rem; border-radius: 50px; background: white; color: var(--primary); font-weight: 800; text-decoration: none; font-size: 1.1rem; box-shadow: 0 15px 30px rgba(0,0,0,0.1);">START CHECK-IN</a>
                <a href="{{ route('student.notes.index') }}" style="padding: 1.5rem 4rem; border-radius: 50px; background: rgba(255,255,255,0.15); color: white; font-weight: 800; text-decoration: none; font-size: 1.1rem; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">MESSAGE A COUNSELOR</a>
            </div>
        </div>
    </div>
</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© {{ date('Y') }} PSU Student Wellness • Here for You, Every Day.</p>
</footer>
@endsection
