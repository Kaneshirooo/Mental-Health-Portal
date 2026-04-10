<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get all questions grouped by category
$questions_result = $conn->query(
    "SELECT question_id, category, question_text, question_number
     FROM assessment_questions ORDER BY question_number"
);
$questions = [];
$categories = [];

while ($row = $questions_result->fetch_assoc()) {
    $questions[] = $row;
    if (!in_array($row['category'], $categories)) {
        $categories[] = $row['category'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $depression_score = 0;
    $anxiety_score = 0;
    $stress_score = 0;
    $total_score_all = 0; // fallback: sum of ALL responses

    foreach ($questions as $question) {
        $key = 'q_' . $question['question_id'];
        if (isset($_POST[$key])) {
            $val = intval($_POST[$key]);
            $total_score_all += $val;

            $stmt = $conn->prepare(
                "INSERT INTO student_responses (user_id, question_id, response_value, assessment_date)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->bind_param("iii", $user_id, $question['question_id'], $val);
            $stmt->execute();

            // Case-insensitive category matching
            $cat = strtolower(trim($question['category']));
            if ($cat === 'depression')
                $depression_score += $val;
            elseif ($cat === 'anxiety')
                $anxiety_score += $val;
            elseif ($cat === 'stress')
                $stress_score += $val;
        }
    }

    // If all three dimension scores are 0 (category mismatch), distribute total evenly
    if ($depression_score === 0 && $anxiety_score === 0 && $stress_score === 0 && $total_score_all > 0) {
        $per = (int)round($total_score_all / 3);
        $depression_score = $per;
        $anxiety_score = $per;
        $stress_score = $total_score_all - ($per * 2);
    }

    // overall_score: average on 0-20 scale (consistent with calculateRiskLevel)
    $overall_score = round(($depression_score + $anxiety_score + $stress_score) / 3);
    $risk_level = calculateRiskLevel($depression_score, $anxiety_score, $stress_score);

    $score_stmt = $conn->prepare(
        "INSERT INTO assessment_scores (user_id, depression_score, anxiety_score, stress_score, overall_score, risk_level, assessment_date)
         VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );
    $score_stmt->bind_param("iiiiis", $user_id, $depression_score, $anxiety_score, $stress_score, $overall_score, $risk_level);

    if ($score_stmt->execute()) {
        $score_id = $conn->insert_id;
        logActivity($user_id, 'Completed assessment with score ' . $overall_score);
        redirect('assessment_complete.php?score_id=' . $score_id);
    }
    else {
        $error = 'Failed to save assessment. Please try again.';
    }
}

$total_steps = count($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Assessment — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
    <style>
        .assessment-header {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 64px;
            background: var(--surface);
            backdrop-filter: var(--glass-blur);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            border-bottom: 1px solid var(--border);
        }
        .progress-indicator {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .step-node {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            background: var(--surface-2);
            color: var(--text-dim);
            transition: var(--transition);
            border: 2px solid transparent;
        }
        .step-node.active {
            background: var(--primary-glow);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.1);
        }
        .step-node.completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: #10b981;
        }

        .choice-matrix {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }
        .choice-card {
            background: var(--surface-2);
            border: 2px solid transparent;
            border-radius: var(--radius);
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .choice-card .val {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dim);
            transition: var(--transition);
        }
        .choice-card .label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .choice-card:hover { 
            transform: translateY(-3px); 
            background: var(--surface-solid); 
            border-color: var(--primary-light); 
            box-shadow: var(--shadow-sm); 
        }
        
        .choice-card.selected {
            background: var(--surface-solid);
            border-color: var(--primary);
            box-shadow: 0 8px 20px var(--primary-glow);
            transform: translateY(-2px);
        }
        .choice-card.selected .val { color: var(--primary); transform: scale(1.05); }
        .choice-card.selected .label { color: var(--primary); }

        .question-strip {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .question-strip.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="assessment-header">
    <div class="progress-indicator">
        <?php foreach ($categories as $i => $cat): ?>
            <div class="step-node <?php echo $i === 0 ? 'active' : ''; ?>" id="node-<?php echo $i; ?>">
                <?php echo $i + 1; ?>
            </div>
        <?php
endforeach; ?>
    </div>
    <div style="text-align: right;">
        <div id="sectionTitle" style="font-weight: 600; font-size: 0.95rem; color: var(--primary-dark); margin-bottom: 0.15rem;"><?php echo $categories[0]; ?></div>
        <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">In Progress</div>
    </div>
</div>

<div class="container" style="max-width: 900px; padding-top: 6rem; padding-bottom: 6rem;">
    
    <div style="text-align: center; margin-bottom: 3rem;">
        <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem;">Pre-Assessment</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">Wellness Check-in</h1>
        <p style="color: var(--text-muted); font-size: 1rem; font-weight: 400; max-width: 600px; margin: 0 auto; line-height: 1.6;">Answer honestly — your responses help us understand how you're doing and provide better support.</p>
    </div>

    <form method="POST" id="assessmentForm">
        <?php foreach ($categories as $stepIdx => $category): ?>
        <div class="assessment-section" id="step-<?php echo $stepIdx; ?>" style="display: <?php echo $stepIdx === 0 ? 'block' : 'none'; ?>;">
            
            <div style="display: flex; flex-direction: column; gap: 6rem;">
                <?php
    foreach ($questions as $question):
        if ($question['category'] !== $category)
            continue;
?>
                <div class="question-strip" style="background: var(--surface-solid); padding: 2.5rem 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                    <div style="display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <div style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 700; color: var(--primary); opacity: 0.15; line-height: 1;">
                            <?php echo str_pad($question['question_number'], 2, '0', STR_PAD_LEFT); ?>
                        </div>
                        <label style="font-size: 1.1rem; font-weight: 600; color: var(--text); line-height: 1.4; flex: 1; padding-top: 0.25rem;">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </label>
                    </div>
                    
                    <div class="choice-matrix" style="gap: 0.75rem; margin-top: 1.5rem;">
                        <?php
        $opts = [
            0 => ['val' => '0', 'label' => 'Not at all'],
            1 => ['val' => '1', 'label' => 'A little'],
            2 => ['val' => '2', 'label' => 'Moderately'],
            3 => ['val' => '3', 'label' => 'Quite a bit'],
            4 => ['val' => '4', 'label' => 'Extremely']
        ];
        foreach ($opts as $val => $m):
            $uniqueId = "q{$question['question_id']}_{$val}";
?>
                            <div class="choice-card" onclick="selectChoice('q_<?php echo $question['question_id']; ?>', <?php echo $val; ?>, this)">
                                <input type="radio" name="q_<?php echo $question['question_id']; ?>" value="<?php echo $val; ?>" style="display: none;" <?php echo $stepIdx === 0 ? 'required' : ''; ?>>
                                <div class="val"><?php echo $m['val']; ?></div>
                                <div class="label"><?php echo $m['label']; ?></div>
                            </div>
                        <?php
        endforeach; ?>
                    </div>
                </div>
                <?php
    endforeach; ?>
            </div>

            <div style="margin-top: 3rem; display: flex; justify-content: space-between; align-items: center; background: var(--surface-2); padding: 1.5rem 2rem; border-radius: var(--radius); border: 1px solid var(--border);">
                <?php if ($stepIdx > 0): ?>
                    <button type="button" onclick="goStep(<?php echo $stepIdx - 1; ?>)" style="background: var(--surface-solid); border: 1px solid var(--border); padding: 0.75rem 1.75rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: var(--text-muted); transition: var(--transition); font-size: 0.9rem;">← Back</button>
                <?php
    else: ?>
                    <div style="width: 100px;"></div>
                <?php
    endif; ?>

                <div style="text-align: center;">
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--primary); margin-bottom: 0.25rem;">Section <?php echo $stepIdx + 1; ?></div>
                    <div style="font-size: 0.7rem; font-weight: 500; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;"><?php echo $category; ?></div>
                </div>

                <?php if ($stepIdx < $total_steps - 1): ?>
                    <button type="button" onclick="goStep(<?php echo $stepIdx + 1; ?>)" style="background: var(--primary); border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: white; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem;">Next →</button>
                <?php
    else: ?>
                    <button type="button" onclick="confirmSubmit()" style="background: #059669; border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: var(--transition); font-size: 0.9rem;">Submit Assessment</button>
                <?php
    endif; ?>
            </div>
        </div>
        <?php
endforeach; ?>
    </form>
</div>

<!-- Validation toast -->
<div id="validationToast" style="display:none; position:fixed; top:2rem; left:50%; transform:translateX(-50%); z-index:9999; background:#fef2f2; border:2px solid #fca5a5; color:#991b1b; padding:1.25rem 2.5rem; border-radius:20px; font-weight:800; font-size:1rem; box-shadow:0 20px 40px rgba(0,0,0,0.15); text-align:center; animation: toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1);">
    ⚠️ Please answer <strong id="toastCount"></strong> before continuing.
</div>

<!-- Confirm Submit Modal -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.55); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
    <div style="background:var(--surface-solid); border: 1px solid var(--border); border-radius:var(--radius-lg); padding:2.5rem; max-width:400px; width:90%; text-align:center; box-shadow:var(--shadow-lg); animation:modalPop 0.25s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="font-size:2.5rem; margin-bottom:1rem;">📋</div>
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:700; color:var(--text); margin-bottom:0.5rem;">Submit Your Assessment?</h3>
        <p style="color:var(--text-muted); font-size:0.9rem; font-weight:400; line-height:1.5; margin-bottom:1.75rem;">Make sure all your answers reflect how you truly feel before submitting.</p>
        <div style="display:flex; gap:0.75rem;">
            <button onclick="closeConfirmModal()" style="flex:1; background:var(--surface-2); border:none; padding:0.75rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; color:var(--text-muted); cursor:pointer;">Go Back</button>
            <button onclick="doSubmit()" style="flex:2; background:#059669; border:none; padding:0.75rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; color:white; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.2);">Yes, Submit →</button>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>

<script>
const totalSteps = <?php echo $total_steps; ?>;
const categories = <?php echo json_encode($categories); ?>;
let currentStep = 0;
let formSubmitting = false;

// ── Named beforeunload handler (must be named to be removable) ──
function warnBeforeLeave(e) {
    if (formSubmitting) return;
    e.preventDefault();
    e.returnValue = '';
}
window.addEventListener('beforeunload', warnBeforeLeave);

// ── Select choice ──
function selectChoice(name, val, el) {
    const parent = el.parentElement;
    parent.querySelectorAll('.choice-card').forEach(card => card.classList.remove('selected'));
    el.classList.add('selected');
    const input = el.querySelector('input[type="radio"]');
    if (input) {
        input.checked = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

// ── Validation toast ──
let toastTimer = null;
function showToast(msg) {
    const toast = document.getElementById('validationToast');
    document.getElementById('toastCount').textContent = msg;
    toast.style.display = 'block';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 3500);
    // Shake animation on first unanswered question
    const strip = document.querySelector('#step-' + currentStep + ' .question-strip:not(:has(input:checked))');
    if (strip) {
        strip.scrollIntoView({ behavior: 'smooth', block: 'center' });
        strip.style.transition = 'all 0.15s';
        strip.style.outline = '3px solid #fca5a5';
        strip.style.outlineOffset = '6px';
        setTimeout(() => { strip.style.outline = ''; strip.style.outlineOffset = ''; }, 2500);
    }
}

// ── Go to next/prev step (with validation) ──
function goStep(next) {
    // Only validate when going forward
    if (next > currentStep) {
        const section = document.getElementById('step-' + currentStep);
        const strips   = section.querySelectorAll('.question-strip');
        let unanswered = 0;
        for (let q of strips) {
            if (!q.querySelector('input[type="radio"]:checked')) unanswered++;
        }
        if (unanswered > 0) {
            showToast(unanswered + ' unanswered question' + (unanswered > 1 ? 's' : ''));
            return; // Block navigation
        }
    }

    document.getElementById('step-' + currentStep).style.display = 'none';
    document.getElementById('node-' + currentStep).classList.remove('active');
    if (next > currentStep) document.getElementById('node-' + currentStep).classList.add('completed');
    
    currentStep = next;
    document.getElementById('step-' + currentStep).style.display = 'block';
    document.getElementById('node-' + currentStep).classList.add('active');
    document.getElementById('sectionTitle').textContent = categories[currentStep];

    window.scrollTo({ top: 0, behavior: 'instant' });
    initObserver();
}

// ── Complete Diagnosis: validate last section, then show confirm modal ──
function confirmSubmit() {
    const section = document.getElementById('step-' + currentStep);
    const strips   = section.querySelectorAll('.question-strip');
    let unanswered = 0;
    for (let q of strips) {
        if (!q.querySelector('input[type="radio"]:checked')) unanswered++;
    }
    if (unanswered > 0) {
        showToast(unanswered + ' unanswered question' + (unanswered > 1 ? 's' : ''));
        return;
    }
    // Show confirmation modal
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function doSubmit() {
    formSubmitting = true;
    window.removeEventListener('beforeunload', warnBeforeLeave);
    document.getElementById('assessmentForm').submit();
}

// Close confirm modal on backdrop click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

// ── Reveal animations on scroll ──
function initObserver() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.question-strip').forEach(strip => observer.observe(strip));
}

document.addEventListener('DOMContentLoaded', initObserver);
</script>

<style>
@keyframes toastIn {
    from { opacity:0; transform:translateX(-50%) translateY(-12px); }
    to   { opacity:1; transform:translateX(-50%) translateY(0); }
}
@keyframes modalPop {
    from { transform:scale(0.85); opacity:0; }
    to   { transform:scale(1); opacity:1; }
}
</style>

</body>
</html>
