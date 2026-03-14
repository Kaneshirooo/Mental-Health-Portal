<?php
require_once 'config.php';
// This page is for developers/admins to view and download the system ERD
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERD Viewer — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        :root {
            --bg-canvas: #f0f2f5;
        }
        body {
            background-color: var(--bg-canvas);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .viewer-header {
            background: white;
            padding: 1.5rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }
        .viewer-title h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0;
        }
        .viewer-title p {
            color: var(--text-dim);
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0;
        }
        .controls {
            display: flex;
            gap: 1rem;
        }
        .btn-download {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        .erd-container {
            padding: 4rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            overflow: auto;
        }
        #mermaid-diagram {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: var(--shadow);
            min-width: fit-content;
        }
        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            color: var(--text-dim);
        }
        .dot-pulse {
            width: 10px;
            height: 10px;
            border-radius: 5px;
            background-color: var(--primary);
            animation: pulse 1.5s infinite ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(0.5); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
        }
    </style>
</head>
<body>

<header class="viewer-header">
    <div class="viewer-title">
        <h1>Entity Relationship Diagram</h1>
        <p>Mental Health Pre-Assessment System v1.0</p>
    </div>
    <div class="controls">
        <button onclick="downloadSVG()" class="btn-download">
            <span>🖼️</span> Download SVG
        </button>
        <button onclick="downloadPNG()" class="btn-download" style="background:#059669;">
            <span>📸</span> Download PNG
        </button>
        <button onclick="window.print()" class="btn-download" style="background: white; color: var(--text); border: 1px solid var(--border);">
            <span>🖨️</span> Print to PDF
        </button>
    </div>
</header>

<main class="erd-container">
    <div id="mermaid-diagram" class="mermaid">
erDiagram
    USERS ||--o{ STUDENT_RESPONSES : "provides"
    USERS ||--o{ ASSESSMENT_SCORES : "receives"
    USERS ||--o{ COUNSELOR_NOTES : "writes/about"
    USERS ||--o{ SESSION_LOGS : "generates"
    USERS ||--o{ APPOINTMENTS : "books/manages"
    USERS ||--o{ AI_PREASSESSMENTS : "participates_in"
    USERS ||--o{ COUNSELOR_AVAILABILITY : "sets"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ MOOD_LOGS : "logs"
    USERS ||--o{ ANONYMOUS_NOTES : "submits"

    ASSESSMENT_QUESTIONS ||--o{ STUDENT_RESPONSES : "subject_of"
    ANONYMOUS_NOTES ||--o{ ANONYMOUS_NOTE_MESSAGES : "contains"

    USERS {
        int user_id PK
        string email UK
        string password
        string full_name
        string roll_number UK
        enum user_type
        date date_of_birth
        enum gender
        string contact_number
        string department
        int semester
        timestamp created_at
        timestamp updated_at
    }

    ASSESSMENT_QUESTIONS {
        int question_id PK
        string category
        text question_text
        int question_number
        timestamp created_at
    }

    STUDENT_RESPONSES {
        int response_id PK
        int user_id FK
        int question_id FK
        int response_value
        timestamp assessment_date
    }

    ASSESSMENT_SCORES {
        int score_id PK
        int user_id FK
        int depression_score
        int anxiety_score
        int stress_score
        int overall_score
        enum risk_level
        timestamp assessment_date
        timestamp report_generated_at
        text counselor_notes
    }

    COUNSELOR_NOTES {
        int note_id PK
        int counselor_id FK
        int student_id FK
        text note_text
        string recommendation
        date follow_up_date
        timestamp created_at
        timestamp updated_at
    }

    SESSION_LOGS {
        int log_id PK
        int user_id FK
        timestamp login_time
        timestamp logout_time
        text activity
    }

    APPOINTMENTS {
        int appointment_id PK
        int student_id FK
        int counselor_id FK
        datetime scheduled_at
        int duration_min
        enum status
        text reason
        text counselor_message
        timestamp created_at
        timestamp updated_at
    }

    AI_PREASSESSMENTS {
        int pre_id PK
        int student_id FK
        mediumtext conversation_transcript
        text form_answers
        text ai_report
        timestamp created_at
    }

    COUNSELOR_AVAILABILITY {
        int availability_id PK
        int counselor_id FK
        tinyint day_of_week
        time start_time
        time end_time
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    NOTIFICATIONS {
        int notification_id PK
        int user_id FK
        string title
        text message
        string type
        boolean is_read
        timestamp created_at
    }

    MOOD_LOGS {
        int mood_id PK
        int student_id FK
        int mood_score
        string mood_emoji
        text note
        timestamp logged_at
    }

    ANONYMOUS_NOTES {
        int note_id PK
        int student_id FK
        text message
        text reply
        timestamp replied_at
        int counselor_id FK
        enum status
        timestamp created_at
    }

    ANONYMOUS_NOTE_MESSAGES {
        int message_id PK
        int note_id FK
        string sender_type
        text message_text
        timestamp created_at
    }
    </div>
</main>

<script>
    mermaid.initialize({ 
        startOnLoad: true,
        securityLevel: 'loose',
        theme: 'neutral',
        er: {
            useMaxWidth: false
        }
    });

    /**
     * getSerializedSvg - Extracts the SVG and embeds all CSS styles that
     * Mermaid has injected into the page to ensure the download looks
     * exactly like the on-screen version.
     */
    function getSerializedSvg() {
        const svgElement = document.querySelector('#mermaid-diagram svg');
        if (!svgElement) return null;

        // Clone the SVG so we don't mess with the UI
        const clonedSvg = svgElement.cloneNode(true);
        
        // Find all mermaid-related styles in the document head
        const styles = document.querySelectorAll('style[id^="mermaid-"]');
        let cssText = '';
        styles.forEach(s => cssText += s.textContent);

        // Create a style element inside the SVG
        const styleElement = document.createElementNS('http://www.w3.org/2000/svg', 'style');
        styleElement.textContent = cssText;
        clonedSvg.insertBefore(styleElement, clonedSvg.firstChild);

        // Ensure width/height are handled for better compatibility
        const bbox = svgElement.getBBox();
        clonedSvg.setAttribute('width', bbox.width + 100);
        clonedSvg.setAttribute('height', bbox.height + 100);
        clonedSvg.setAttribute('viewBox', `${bbox.x - 50} ${bbox.y - 50} ${bbox.width + 100} ${bbox.height + 100}`);

        return new XMLSerializer().serializeToString(clonedSvg);
    }

    function downloadSVG() {
        const svgData = getSerializedSvg();
        if (!svgData) return;

        const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
        const svgUrl = URL.createObjectURL(svgBlob);
        
        const downloadLink = document.createElement('a');
        downloadLink.href = svgUrl;
        downloadLink.download = 'mental_health_system_erd.svg';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    /**
     * downloadPNG - Renders the SVG to a high-density canvas and
     * downloads it as a pixel-perfect image.
     */
    function downloadPNG() {
        const svgData = getSerializedSvg();
        if (!svgData) return;

        const canvas = document.createElement('canvas');
        const svgElement = document.querySelector('#mermaid-diagram svg');
        const bbox = svgElement.getBBox();
        
        // Scale for high resolution (2x)
        const scale = 2;
        canvas.width = (bbox.width + 100) * scale;
        canvas.height = (bbox.height + 100) * scale;
        
        const ctx = canvas.getContext('2d');
        const img = new Image();
        const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
        const url = URL.createObjectURL(svgBlob);

        img.onload = function() {
            // Fill background
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            
            const pngLink = document.createElement('a');
            pngLink.href = canvas.toDataURL('image/png');
            pngLink.download = 'mental_health_system_erd.png';
            document.body.appendChild(pngLink);
            pngLink.click();
            document.body.removeChild(pngLink);
            URL.revokeObjectURL(url);
        };
        img.src = url;
    }
</script>

</body>
</html>
