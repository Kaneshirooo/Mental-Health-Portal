<?php
/**
 * PSU Mental Health Portal - Complete User Manual Generator
 * Generates:
 * 1. user manual/PSU_MENTAL_HEALTH_PORTAL_USER_MANUAL.md
 * 2. user manual/PSU-MENTAL-HEALTH-PORTAL-USER-MANUAL.docx
 */

$baseDir = __DIR__ . '/..';
$manualDir = $baseDir . '/user manual';

$sections = [
    // PART 1
    [
        'part' => 'Part 1: How to Register Accounts and Sign In for Students and Counselors',
        'code' => 'A',
        'title' => 'Accessing the Portal and Sign In',
        'desc' => 'This section provides a step-by-step guide on how students and guidance counselors can navigate to the portal login interface and sign in using registered credentials or Google Single Sign-On (SSO).',
        'steps' => [
            'Step 1: Open your preferred web browser (e.g., Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari) and navigate to the PSU Mental Health Portal URL.',
            'Step 2: On the Welcome Back sign-in page, enter your registered Email Address and Password into the designated fields.',
            'Step 3: Click the Secure Sign In button to authenticate. The system automatically detects your account role and routes you to your corresponding workspace (Student Dashboard or Counselor Clinical Workspace).',
            'Step 4: Alternatively, users registered with their institutional Google accounts can click Login with Google for single-click authentication.',
            'Step 5: If you do not have an account yet, locate the "Register here" box and click either "I\'m a Student" or "I\'m a Counselor" to navigate to the appropriate onboarding form.'
        ],
        'note' => 'Always ensure you keep your login credentials secure. Guidance counselors and students utilize distinct access privileges to safeguard clinical and personal privacy.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/03_login_page.jpg',
            'caption' => 'Figure 1: Dual Sign In Interface for Students and Counselors with Google OAuth'
        ]
    ],
    [
        'part' => 'Part 1: How to Register Accounts and Sign In for Students and Counselors',
        'code' => 'B',
        'title' => 'Student Account Registration',
        'desc' => 'This section provides step-by-step instructions for university students on registering a new personal account to access mental health services, mood tracking, and counseling.',
        'steps' => [
            'Step 1: On the sign-in page, click the "I\'m a Student" button under "Register here" or navigate directly to the registration page (/register).',
            'Step 2: Under the Personal Identity section, enter your Full Name (e.g., Juan Dela Cruz), official Student ID / Faculty ID (e.g., 2024-XXXXX), active Email Address, and personal Mobile Number.',
            'Step 3: Under the Demographics section, input your Date of Birth using the calendar picker and choose your Gender Identity from the dropdown options.',
            'Step 4: Under Academic Context, specify your Academic Course (e.g., BS Information Technology), select your Current Semester, and choose your Department / College from the dropdown menu.',
            'Step 5: Enter a secure password, re-enter it to confirm, and click Create Account to complete registration.'
        ],
        'note' => 'Account verification is required. All personal demographic and clinical information entered is 100% confidential under university data privacy guidelines.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/01_student_register.jpg',
            'caption' => 'Figure 2: Student Account Registration and Academic Onboarding Form'
        ]
    ],
    [
        'part' => 'Part 1: How to Register Accounts and Sign In for Students and Counselors',
        'code' => 'C',
        'title' => 'Guidance Counselor and Clinical Staff Registration',
        'desc' => 'This section outlines how authorized university guidance counselors and clinical responders register their credentials to manage student triage, appointments, and therapy sessions.',
        'steps' => [
            'Step 1: On the sign-in page, click the "I\'m a Counselor" button or navigate to the counselor registration form (/register/counselor).',
            'Step 2: Enter your Full Name and assigned Staff / Faculty ID (e.g., EMP-2024-001).',
            'Step 3: Select your assigned Department / College from the administrative dropdown menu.',
            'Step 4: Provide your institutional Email Address and active Contact Number.',
            'Step 5: Create a strong password, confirm the password in the next field, and click Register as Counselor to submit your registration.'
        ],
        'note' => 'Counselor accounts gain direct access to clinical triage tools, emergency call monitors, and student records upon successful registration.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/02_counselor_register.jpg',
            'caption' => 'Figure 3: Guidance Counselor and Clinical Staff Registration Form'
        ]
    ],

    // PART 2: STUDENT PORTAL
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'A',
        'title' => 'Navigating the Student Dashboard',
        'desc' => 'This section guides students through the primary features, wellness reminders, and quick action cards available upon signing into the Student Dashboard.',
        'steps' => [
            'Step 1: Upon signing in, review the Student Dashboard homepage to view your account greeting, wellness reminders, and current wellbeing statistics.',
            'Step 2: Read the Gentle Reminders card at the top of the screen for daily self-care suggestions such as taking deep breaths, staying hydrated, or stepping outdoors.',
            'Step 3: Use the Quick Actions cards to immediately take a wellness check-in, begin an AI chat session with Aria, or book a counseling appointment.',
            'Step 4: Review your Check-in Streak counter and weekly wellness overview to observe your consistency.',
            'Step 5: Navigate between different modules using the left navigation sidebar: Dashboard, Pre-Assessment, Quick Note, Talk to Assistant, Mood Journal, Appointments, Mindfulness, My Progress, and My Profile.'
        ],
        'note' => 'You can access the high-visibility red Emergency Help button from any screen in the student portal.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213541.png',
            'caption' => 'Figure 4: Student Wellness Dashboard Overview with Reminders and Quick Actions'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'B',
        'title' => 'Completing the Mental Health Pre-Assessment Check-in',
        'desc' => 'This section explains how students complete the standardized psychometric wellness check-in questionnaire based on the DASS-21 scale.',
        'steps' => [
            'Step 1: From the left navigation menu, click Pre-Assessment or click the "Take Check-in" card from the dashboard.',
            'Step 2: Read the questionnaire instructions. The pre-assessment consists of standardized items measuring how frequently you experienced specific emotional and physical states over the past week.',
            'Step 3: For each statement, choose the response option that best describes your experience: (0) Did not apply to me at all, (1) Applied to me to some degree, (2) Applied to me to a considerable degree, or (3) Applied to me very much.',
            'Step 4: (Optional) If you prefer reading questions in Filipino/Tagalog, use the multilingual translation toggle at the top of the questionnaire.',
            'Step 5: After answering all questions, click the Submit Assessment button to process your responses and generate your clinical evaluation.'
        ],
        'note' => 'Please answer honestly. Your responses are strictly confidential and enable counselors to provide prompt, tailored support.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213552.png',
            'caption' => 'Figure 5: Standardized Mental Health Pre-Assessment Check-in Questionnaire'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'C',
        'title' => 'Reviewing Assessment Results and Risk Level Breakdown',
        'desc' => 'This section guides students on understanding their pre-assessment results, sub-domain severity levels, and AI clinical insights.',
        'steps' => [
            'Step 1: Following questionnaire submission, review your personalized Assessment Results screen.',
            'Step 2: Examine the three psychometric subscales: Low Mood (Depression), Worries (Anxiety), and Pressure (Stress).',
            'Step 3: Check your assigned severity badges: Normal, Mild, Moderate, Severe, or Extremely Severe for each domain.',
            'Step 4: Read the AI Clinical Insight breakdown explaining your scores, recognizing emotional patterns, and recommending coping techniques.',
            'Step 5: Review recommended next steps, such as booking an appointment with a counselor or practicing guided mindfulness.'
        ],
        'note' => 'High or severe risk level scores trigger an automated alert on the counselor triage matrix so on-duty guidance staff can reach out.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 215058.png',
            'caption' => 'Figure 6: Assessment Results Display with Domain Sub-scores and Severity Badges'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'D',
        'title' => 'Exporting and Printing Official Assessment Score Reports',
        'desc' => 'This section details how students can download and print an official PDF report of their pre-assessment results for personal records or external consultation.',
        'steps' => [
            'Step 1: From the Assessment Results page or the My Progress archive, click the "Download PDF Summary" button.',
            'Step 2: The system compiles an official university document entitled "Pangasinan State University Mental Health Portal - Pre-Assessment Summary Score Report".',
            'Step 3: Review the generated PDF document, verifying student identification, evaluation date, subscale scores, and official institutional watermark.',
            'Step 4: Save the PDF file to your local computer or mobile device, or click Print to generate a physical paper copy.'
        ],
        'note' => 'Exported PDF reports serve as official records of psychometric evaluation within the Pangasinan State University counseling system.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 215129.png',
            'caption' => 'Figure 7: Official Exported Pre-Assessment Summary PDF Score Report'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'E',
        'title' => 'Sending Confidential Counselor Messages via Quick Notes',
        'desc' => 'This section explains how students can reach guidance counselors asynchronously for private questions, advice, or concerns without scheduling an in-person session.',
        'steps' => [
            'Step 1: From the left sidebar, click Quick Note.',
            'Step 2: Type your concern, question, or emotional burden into the secure message composer.',
            'Step 3: (Optional) Toggle the "Anonymous Mode" switch if you prefer to submit your query anonymously while still receiving counselor responses.',
            'Step 4: Click the Send Note button to dispatch your message directly to the counselor triage queue.',
            'Step 5: Review past notes and counselor responses in the threaded conversation history displayed below the composer.'
        ],
        'note' => 'Quick Notes are monitored during campus office hours. For immediate emergencies, use the red Emergency Help button.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213604.png',
            'caption' => 'Figure 8: Confidential Counselor Messaging Thread via Quick Notes'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'F',
        'title' => 'Conversing with Aria - The AI Mental Health Companion',
        'desc' => 'This section instructs students on how to engage with Aria, the 24/7 AI-driven conversational assistant designed for compassionate emotional support and grounding.',
        'steps' => [
            'Step 1: Click "Talk to Assistant" on the navigation menu to open the Aria interactive chat interface.',
            'Step 2: Type a message into the input field or click one of the suggested conversation starter prompts.',
            'Step 3: Click the Send icon to receive instant empathetic feedback, cognitive reframing suggestions, and coping exercises.',
            'Step 4: Click the "Start New Chat" button whenever you wish to clear context and begin an entirely new topic.',
            'Step 5: Review AI-generated session takeaways, insights, and key reminders displayed in the right conversation panel.'
        ],
        'note' => 'Aria is an artificial intelligence support tool and does not replace human clinical diagnosis. In crisis situations, Aria immediately displays emergency hotline contacts.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213613.png',
            'caption' => 'Figure 9: Aria - 24/7 AI Mental Health Conversational Companion'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'G',
        'title' => 'Daily Mood Journaling and Emotional Reflection',
        'desc' => 'This section details how students record daily moods, reflect on daily events, and track emotional patterns over time.',
        'steps' => [
            'Step 1: Select "Mood Journal" from the left navigation menu.',
            'Step 2: Select the emoji that best represents how you feel today (Great, Good, Okay, Down, or Rough).',
            'Step 3: Enter your daily reflection in the text area, noting what factors (e.g., classes, family, sleep, exams) influenced your feelings.',
            'Step 4: Click the Save Journal Entry button to record your mood into your personal wellness calendar.',
            'Step 5: Review your previous journal reflections in the timeline history below to observe changes in emotional patterns.'
        ],
        'note' => 'Regular journaling builds emotional self-awareness and helps you identify specific triggers impacting your mental health.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213625.png',
            'caption' => 'Figure 10: Daily Mood Journaling Interface with Emotional Reflection Timeline'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'H',
        'title' => 'Booking and Managing Counseling Appointments',
        'desc' => 'This section guides students through scheduling one-on-one counseling consultations with guidance counselors and tracking appointment statuses.',
        'steps' => [
            'Step 1: From the left sidebar, click Appointments.',
            'Step 2: Click the "Book an Appointment" button to open the appointment booking modal.',
            'Step 3: Select your preferred counselor or choose "Any Available Counselor".',
            'Step 4: Select an available date and preferred time slot from the counselor\'s published schedule.',
            'Step 5: Choose your consultation format: "In-Person" (guidance office) or "Online Video Consultation".',
            'Step 6: Briefly specify the reason for your visit or concerns you would like to discuss, then click Submit Request.',
            'Step 7: Monitor your appointment list to view the current status: Pending, Confirmed, Completed, or Cancelled.'
        ],
        'note' => 'Once an appointment is completed by your counselor, a "Leave Feedback" button becomes available to rate your consultation experience.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213634.png',
            'caption' => 'Figure 11: Counseling Appointment Booking, Schedule Calendar, and Status Tracking'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'I',
        'title' => 'Engaging in Guided Mindfulness and Breathing Exercises',
        'desc' => 'This section explains how to use the interactive mindfulness suite and diaphragmatic breathing tools to ease acute stress and calm the nervous system.',
        'steps' => [
            'Step 1: Click "Mindfulness" on the navigation menu.',
            'Step 2: Locate the 4-7-8 Breathing Exercise module.',
            'Step 3: Click Start Exercise to initiate the paced visual sphere timer: Inhale quietly through the nose for 4 seconds as the sphere expands; Hold your breath for 7 seconds; Exhale slowly through the mouth for 8 seconds as the sphere contracts.',
            'Step 4: Complete the recommended four breathing cycles to restore calm and steady your heart rate.',
            'Step 5: Explore complementary grounding resources, such as the 5-4-3-2-1 Sensory Grounding exercise provided on the page.'
        ],
        'note' => 'The 4-7-8 breathing method acts as a natural tranquilizer for the nervous system and can be practiced anytime you feel overwhelmed.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213649.png',
            'caption' => 'Figure 12: Interactive 4-7-8 Guided Diaphragmatic Breathing and Mindfulness Tools'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'J',
        'title' => 'Tracking Personal Progress and Session Takeaways',
        'desc' => 'This section guides students on reviewing their longitudinal wellness trajectory, AI chat takeaways, and therapeutic progress.',
        'steps' => [
            'Step 1: Select "My Progress" from the left navigation menu.',
            'Step 2: Inspect the Key Takeaways section summarizing important insights, coping strategies, and action steps generated from your counseling sessions and Aria chats.',
            'Step 3: Review your cumulative check-in score trajectory graph to observe progress over weeks and months.',
            'Step 4: Read your personalized motivational affirmations and view your completed check-in milestones.'
        ],
        'note' => 'Tracking your progress over time reinforces resilience and provides objective feedback on your mental wellness journey.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213700.png',
            'caption' => 'Figure 13: My Progress Analytics with AI-Synthesized Session Takeaways'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'K',
        'title' => 'Accessing Emergency Help and Crisis Hotlines',
        'desc' => 'This section provides instructions for students in acute crisis on how to access immediate emergency campus assistance and national 24/7 hotlines.',
        'steps' => [
            'Step 1: Click the red Emergency Help button positioned at the top of the left sidebar from any page.',
            'Step 2: Review verified crisis contacts: PSU Lingayen Campus Security & Medical Clinic, National Center for Mental Health (NCMH) Hotline (1553 / 0917-899-USAP), Hopeline Philippines (0917-558-4673), and In-Touch Community Services ((02) 8893-7603).',
            'Step 3: Click the prominent "Call Counselor Now" button to initiate an instant encrypted video or audio crisis call with the on-duty guidance counselor.',
            'Step 4: If you or someone you know is in immediate life-threatening danger, dial 911 or proceed to the nearest hospital emergency room immediately.'
        ],
        'note' => 'Emergency services are available 24/7. Never hesitate to reach out for immediate professional intervention during a crisis.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213753.png',
            'caption' => 'Figure 14: Emergency Crisis Response Center, Campus Contacts, and National Hotlines'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'L',
        'title' => 'Participating in Live Emergency WebRTC Video Calls',
        'desc' => 'This section explains how students interact with counselors during a live encrypted video consultation session.',
        'steps' => [
            'Step 1: Upon clicking "Call Counselor Now" or joining an online appointment, grant browser permissions for Camera and Microphone.',
            'Step 2: Wait briefly as the peer-to-peer WebRTC connection establishes securely with the guidance counselor.',
            'Step 3: View the counselor in the main video stream while monitoring your local preview in the corner overlay.',
            'Step 4: Use the bottom toolbar to toggle video feed on/off, mute/unmute audio, or exchange text messages in the side chat panel.',
            'Step 5: When the session concludes, click End Call to safely terminate the video transmission.'
        ],
        'note' => 'Video calls are end-to-end encrypted to guarantee strict medical confidentiality during remote consultations.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 215216.png',
            'caption' => 'Figure 15: Secure WebRTC Live Emergency Video Consultation Interface'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'M',
        'title' => 'Completing the Post-Consultation Service Quality Evaluation (CSAT Survey)',
        'desc' => 'This section details how students evaluate counseling services after completed consultations to support institutional quality assurance.',
        'steps' => [
            'Step 1: Following the conclusion of a counseling appointment, open the survey notification or navigate to the survey link (/student/survey/{id}).',
            'Step 2: On the "How Was Your Talk?" evaluation form, read each evaluation statement.',
            'Step 3: Rate each criterion on a 1-to-5 Likert scale (1 = Strongly Disagree to 5 = Strongly Agree, or N/A): (a) Overall, I felt cared for and supported in my session; (b) My session lasted a good amount of time for me; (c) My counselor guided our talk well step by step; (d) It was easy to know what to do for my session.',
            'Step 4: (Optional) Enter qualitative feedback or comments in the provided text area.',
            'Step 5: Click Submit Evaluation to send your responses anonymously to university administration.'
        ],
        'note' => 'Student satisfaction surveys are entirely anonymous and used exclusively to maintain high institutional counseling standards.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/06_student_survey.jpg',
            'caption' => 'Figure 16: Post-Consultation Service Quality Evaluation (CSAT Likert Survey)'
        ]
    ],
    [
        'part' => 'Part 2: Student Portal',
        'code' => 'N',
        'title' => 'Managing Student Profile and Reviewing System Notifications',
        'desc' => 'This section instructs students on how to maintain accurate profile records and check real-time system alerts.',
        'steps' => [
            'Step 1: Click "My Profile" at the bottom of the navigation sidebar.',
            'Step 2: Review and update your demographic details, contact number, emergency parent contacts, and academic degree information, then click Save Profile.',
            'Step 3: Click "Notifications" on the sidebar or click the notification bell icon to view updates on appointment approvals, counselor replies, and reminders.',
            'Step 4: Click on any notification to jump directly to the relevant activity or click "Mark All as Read" to clear the notification badge.'
        ],
        'note' => 'Keep your contact and emergency details up to date so university guidance staff can reach your family if urgent need arises.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213713.png',
            'caption' => 'Figure 17: Student Profile Management and Academic Information Form'
        ]
    ],

    // PART 3: COUNSELOR & ADMIN PORTAL
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'A',
        'title' => 'Clinical Dashboard and Priority Triage Matrix',
        'desc' => 'This section provides guidance counselors and clinical supervisors with a walkthrough of the Counselor Dashboard, triage queue, and active caseload indicators.',
        'steps' => [
            'Step 1: Log in with counselor credentials to open the primary Counselor Clinical Dashboard.',
            'Step 2: Review the statistical overview cards: Total Registered Students, Pending Assessments, High-Risk Triage Cases, and Today\'s Appointments.',
            'Step 3: Inspect the Priority Triage Matrix displaying students whose recent DASS-21 pre-assessments indicate severe depression, anxiety, or stress.',
            'Step 4: Toggle your Emergency Status switch at the top of the screen to signal whether you are currently available to receive incoming emergency crisis calls.',
            'Step 5: Use the left navigation sidebar to access: Admin Panel, Manage Counselors, Student Records, Archived Students, Check-In Questions, My Appointments, My Availability, System Reports, Call History, and My Profile.'
        ],
        'note' => 'The Priority Triage Matrix automatically ranks cases by clinical risk level, ensuring students experiencing acute distress receive immediate follow-up.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213035.png',
            'caption' => 'Figure 18: Counselor Clinical Dashboard and Priority Distress Triage Matrix'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'B',
        'title' => 'Managing Guidance Counselors and Support Staff (Admin Privilege)',
        'desc' => 'This section explains how administrators register new counseling staff, update departmental assignments, and maintain staff rosters.',
        'steps' => [
            'Step 1: From the left administrative sidebar, click Manage Counselors.',
            'Step 2: Review the staff roster displaying Counselor Names, Institutional Emails, Staff IDs, Assigned Departments, and Active Statuses.',
            'Step 3: Click the "Add Counselor" button to register a new guidance staff member, entering their full name, staff ID, department, and contact information.',
            'Step 4: Edit permissions, update office room assignments, or deactivate accounts when staff are on leave.',
            'Step 5: Save changes to commit updates to the institutional user directory.'
        ],
        'note' => 'Staff management privileges are restricted to designated Head Counselors and System Administrators.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213052.png',
            'caption' => 'Figure 19: Guidance Counselor Staff Management and Department Assignment (Admin)'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'C',
        'title' => 'Student Records Directory and AI High-Risk Concentrations',
        'desc' => 'This section covers searching student records, filtering by college department, and reviewing AI-identified psychological risk distributions.',
        'steps' => [
            'Step 1: Click "Student Records" from the left navigation menu.',
            'Step 2: Use the search field to quickly locate students by full name, student ID number, or academic course.',
            'Step 3: Apply filter tabs to view students by College Department or Risk Category (High Risk, Moderate, Mild, or Normal).',
            'Step 4: Identify students flagged with AI High-Risk badges indicating recent self-harm markers or critical assessment subscale spikes.',
            'Step 5: Click on any student row to open their comprehensive clinical profile and case history.'
        ],
        'note' => 'All student clinical records are protected under FERPA and Philippine Data Privacy Act standards.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213103.png',
            'caption' => 'Figure 20: Student Records Registry with Departmental Filters and AI Risk Tags'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'D',
        'title' => 'Individual Student Clinical File, Wellness Trajectory, and Case Notes',
        'desc' => 'This section instructs counselors on inspecting complete student clinical files (case study: Joshua Rulloda), evaluating wellness trajectories, and logging formal session notes.',
        'steps' => [
            'Step 1: Open a student\'s clinical file from the Student Records registry to view their comprehensive clinical profile.',
            'Step 2: Review the Student Contact card (name, student ID, department, semester, email, phone) and Parent / Guardian card (emergency contact person, guardian phone, and email).',
            'Step 3: Analyze the Wellness Trajectory Graph displaying the student\'s longitudinal wellness score history across multiple check-ins to evaluate recovery progress or regression.',
            'Step 4: Under Archive Session, enter clinical observations, therapeutic modalities used, and action plans into the Session Notes box.',
            'Step 5: Click "Save Session Notes" to record the entry into the student\'s permanent clinical history.',
            'Step 6: Click "Export File" to generate an official confidential clinical dossier PDF for university case archiving.'
        ],
        'note' => 'Case notes are time-stamped and encrypted. Parent contacts should only be utilized in verified emergency circumstances.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/04_counselor_student_file.jpg',
            'caption' => 'Figure 21: Individual Student Clinical File, Longitudinal Wellness Trajectory, and Notes'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'E',
        'title' => 'Archived Student Records Management',
        'desc' => 'This section details how counselors search historical files, review inactive student cases, and restore profiles when students return.',
        'steps' => [
            'Step 1: From the left sidebar, click "Archived Students".',
            'Step 2: Search for past student cases by name, student ID, or semester of archive.',
            'Step 3: Click "View Case File" to review past counseling interventions, assessment histories, and clinical dispositions.',
            'Step 4: If a student resumes active counseling or re-enrolls, click "Restore Student" to return the profile to the active registry.'
        ],
        'note' => 'Archiving preserves complete historical data without cluttering active daily triage queues.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213115.png',
            'caption' => 'Figure 22: Archived Student Case Records and Historical Search Directory'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'F',
        'title' => 'Pre-Assessment Question Builder and Custom Scale Configuration',
        'desc' => 'This section guides counselors on configuring psychological check-in questions, modifying scoring weights, and maintaining psychometric scale standards.',
        'steps' => [
            'Step 1: Click "Check-In Questions" from the left navigation menu.',
            'Step 2: Review the table of active assessment items with their assigned domains (Depression, Anxiety, Stress), scoring weights, and status.',
            'Step 3: Click "Add Question" to introduce a new psychometric question prompt, select the corresponding domain category, and assign score weights.',
            'Step 4: Edit or re-order existing questions, or update Filipino/Tagalog translations to enhance clarity for students.',
            'Step 5: Click Save Changes to immediately deploy the updated assessment questionnaire to the student portal.'
        ],
        'note' => 'Modifications to questionnaire scales should align with standard institutional psychometric guidelines (e.g., DASS-21).',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213129.png',
            'caption' => 'Figure 23: Pre-Assessment Question Builder and Psychometric Scale Configuration'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'G',
        'title' => 'Managing Counselor Appointments and Session Archive',
        'desc' => 'This section explains how counselors review consultation requests, manage booking queues, confirm or reschedule sessions, and record outcomes.',
        'steps' => [
            'Step 1: From the left navigation menu, click "My Appointments".',
            'Step 2: Review appointments filtered by tabs: Upcoming, Pending Approval, Completed, and Cancelled.',
            'Step 3: For pending bookings, check the student name, date/time, selected mode (In-Person or Video Call), and stated consultation reason.',
            'Step 4: Click "Approve" to confirm the consultation or "Reschedule" to offer alternative time slots.',
            'Step 5: Once a consultation concludes, click "Mark Completed" to record post-session notes and trigger the automated student CSAT feedback survey.'
        ],
        'note' => 'Timely confirmation of student appointment requests reduces anxiety and fosters an encouraging support environment.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213143.png',
            'caption' => 'Figure 24: Counselor Appointment Queue, Booking Approvals, and Session Archive'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'H',
        'title' => 'Configuring Counselor Availability and Office Hours',
        'desc' => 'This section guides counselors on setting their recurring consultation schedule, active days, session durations, and break buffers.',
        'steps' => [
            'Step 1: Select "My Availability" from the left navigation menu.',
            'Step 2: Toggle the days of the week you are available for student consultations (e.g., Monday through Friday).',
            'Step 3: For each active day, configure your consultation start time, end time, and break/lunch buffer periods.',
            'Step 4: Specify appointment slot duration (e.g., 30, 45, or 60 minutes) and maximum daily consultation limits.',
            'Step 5: Click Save Availability to publish your updated schedule slots on the student booking calendar.'
        ],
        'note' => 'Configuring buffer periods between sessions prevents consultation overruns and provides adequate time for clinical note entry.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213157.png',
            'caption' => 'Figure 25: Counselor Clinical Availability and Weekly Consultation Windows'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'I',
        'title' => 'Generating Institutional Mental Health Reports and Analytics',
        'desc' => 'This section instructs administrators and counselors on generating institutional mental health metrics, DASS distributions, and compliance reports.',
        'steps' => [
            'Step 1: Click "System Reports" from the left administrative menu.',
            'Step 2: Select the reporting timeframe (e.g., Monthly, Semester Midterms, Final Term, or Custom Date Range).',
            'Step 3: Review the statistical charts: Campus-Wide DASS-21 Severity Distribution, Departmental Stress Indices, Consultation Volume, and Average CSAT Ratings.',
            'Step 4: Click "Export to PDF" or "Export to Excel" to download formalized statistical reports for university executives and academic boards.'
        ],
        'note' => 'Institutional reports aggregate data anonymously, providing high-level trends without exposing individual student identities.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213217.png',
            'caption' => 'Figure 26: Institutional System Reports, Psychological Analytics, and Data Export'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'J',
        'title' => 'Emergency Call Center, Video Consultations, and Transcript Logs',
        'desc' => 'This section details managing emergency crisis calls, connecting through encrypted WebRTC video, and auditing historical call logs and transcripts.',
        'steps' => [
            'Step 1: Click "Call History" from the navigation sidebar to open the Emergency Communication Center.',
            'Step 2: Review the Past Call Sessions table showing Call ID, Student Name, Assigned Officer, Time & Duration, and Call Status (Ended, Missed, In-Progress).',
            'Step 3: Click the "Transcript" button beside any completed call to view the comprehensive audio transcript and text chat logs.',
            'Step 4: When an incoming emergency call alert triggers on screen, click "Accept Call" to open the live encrypted video consultation terminal.',
            'Step 5: Communicate with the student using live video/audio, apply de-escalation protocols, review their file in real-time, and click End Call to finalize the session.'
        ],
        'note' => 'Call transcripts are stored in encrypted form and provide critical audit trails for institutional accountability and clinical supervision.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/05_call_transcript_logs.jpg',
            'caption' => 'Figure 27: Emergency Call Transcripts and Detailed Communication Audit Logs'
        ]
    ],
    [
        'part' => 'Part 3: Counselor and Administrator Portal',
        'code' => 'K',
        'title' => 'Counselor Professional Profile and System Notification Feed',
        'desc' => 'This section explains how counselors maintain their professional bio, office location, and monitor real-time clinical alerts.',
        'steps' => [
            'Step 1: Click "My Profile" at the bottom of the navigation sidebar.',
            'Step 2: Update your professional title, clinical credentials, office room location, email address, and account password, then save updates.',
            'Step 3: Click "Notifications" from the sidebar to review system alerts: emergency call notifications, high DASS-21 assessment submissions, and new student appointments.',
            'Step 4: Click any alert to navigate directly to the relevant student file or appointment details.'
        ],
        'note' => 'Maintaining accurate office hours and room information ensures students can easily locate in-person consultations.',
        'image' => [
            'file' => $manualDir . '/Counselor_Extracted/Counselo/Screenshot 2026-11-12 213245.png',
            'caption' => 'Figure 28: Counselor Professional Profile and Credentials Management'
        ]
    ],

    // PART 4: MOBILE VIEW
    [
        'part' => 'Part 4: Mobile Application and Progressive Web App (PWA) View',
        'code' => 'A',
        'title' => 'Mobile Responsive Interface and Navigation',
        'desc' => 'This section guides users on accessing the PSU Mental Health Portal via mobile web browsers or installing it as a Progressive Web App (PWA).',
        'steps' => [
            'Step 1: Open your smartphone browser (Chrome on Android, Safari on iOS) and navigate to the portal URL.',
            'Step 2: Sign in with your student or counselor credentials. The interface automatically adapts into a mobile-first responsive layout.',
            'Step 3: Tap the hamburger navigation menu at the top left to expand the mobile sidebar and access all modules: Dashboard, Pre-Assessment, Mood Journal, Appointments, Aria AI, and Emergency Help.',
            'Step 4: Use touch-optimized cards and scrollable lists designed for smooth one-hand mobile operation.'
        ],
        'note' => 'You can tap "Add to Home Screen" in your mobile browser to install the PSU Mental Health Portal as a standalone PWA app.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/07_student_mobile_dashboard.jpg',
            'caption' => 'Figure 29: Mobile Responsive Interface and One-Tap Floating Emergency SOS'
        ]
    ],
    [
        'part' => 'Part 4: Mobile Application and Progressive Web App (PWA) View',
        'code' => 'B',
        'title' => 'One-Tap Floating Emergency SOS on Mobile',
        'desc' => 'This section details how mobile users trigger instantaneous crisis intervention from any screen on their phone.',
        'steps' => [
            'Step 1: On any screen in the mobile view, locate the prominent floating red "Emergency Help" button.',
            'Step 2: Tap the button to immediately display one-touch dialing for campus security, university clinics, and national crisis hotlines.',
            'Step 3: Tap "Call Counselor" to launch an instant mobile WebRTC video or audio crisis session directly from your smartphone.'
        ],
        'note' => 'The mobile SOS button is permanently anchored to ensure students can request emergency intervention within seconds.',
        'image' => [
            'file' => $manualDir . '/new_screenshots/07_student_mobile_dashboard.jpg',
            'caption' => 'Figure 30: Floating Emergency SOS Button on Mobile View'
        ]
    ],
    [
        'part' => 'Part 4: Mobile Application and Progressive Web App (PWA) View',
        'code' => 'C',
        'title' => 'On-the-Go Wellness Tracking and Mobile AI Chat',
        'desc' => 'This section explains how students complete daily mood logs and converse with Aria AI on mobile devices.',
        'steps' => [
            'Step 1: Tap "Mood Journal" from the mobile menu to log your feelings using quick-touch mood emojis in under 30 seconds.',
            'Step 2: Tap "Talk to Assistant" to chat with Aria AI via an interface optimized for mobile keyboards and voice dictation.',
            'Step 3: Review your upcoming counseling appointments and check-in reminders directly on your mobile device.'
        ],
        'note' => 'Mobile access ensures continuous emotional support is available to students wherever they are, on or off campus.',
        'image' => [
            'file' => $manualDir . '/Students_Extracted/stoodints/Screenshot 2026-11-12 213613.png',
            'caption' => 'Figure 31: On-the-Go Conversational Support with Aria AI'
        ]
    ],
];

// 1. Generate Markdown File
echo "Generating Markdown User Manual...\n";
$md = "# USER MANUAL\n\n";
$md .= "## PSU MENTAL HEALTH PORTAL: AN INTEGRATED DIGITAL WELLNESS AND COUNSELING MANAGEMENT SYSTEM\n";
$md .= "**Pangasinan State University - Lingayen Campus**\n\n";
$md .= "---\n\n";
$md .= "### TABLE OF CONTENTS\n\n";

$currentPart = '';
foreach ($sections as $s) {
    if ($s['part'] !== $currentPart) {
        $currentPart = $s['part'];
        $md .= "#### " . $currentPart . "\n";
    }
    $md .= "- **Section {$s['code']}: {$s['title']}**\n";
}
$md .= "\n---\n\n";

$currentPart = '';
foreach ($sections as $s) {
    if ($s['part'] !== $currentPart) {
        $currentPart = $s['part'];
        $md .= "\n# " . $currentPart . "\n\n";
    }

    $md .= "## Section {$s['code']}: {$s['title']}\n\n";
    $md .= "{$s['desc']}\n\n";

    foreach ($s['steps'] as $step) {
        $md .= "- **{$step}**\n";
    }
    $md .= "\n";

    if (!empty($s['note'])) {
        $md .= "> **Note:** {$s['note']}\n\n";
    }

    if (!empty($s['image'])) {
        $relPath = str_replace($manualDir . '/', '', $s['image']['file']);
        $md .= "![" . $s['image']['caption'] . "](" . $relPath . ")\n";
        $md .= "*{$s['image']['caption']}*\n\n";
    }

    $md .= "---\n\n";
}

$mdPath = $manualDir . '/PSU_MENTAL_HEALTH_PORTAL_USER_MANUAL.md';
file_put_contents($mdPath, $md);
echo "Markdown manual saved to: $mdPath (" . strlen($md) . " bytes)\n";


// 2. Generate Word DOCX File using clean standard OOXML format
echo "Generating Native Word Document (.docx)...\n";
require_once __DIR__ . '/build-clean-docx.php';

$targetDocx = $manualDir . '/PSU-MENTAL-HEALTH-PORTAL-USER-MANUAL.docx';
generateStandardDocx($targetDocx, $sections, $manualDir);

echo "DOCX manual successfully created at: $targetDocx (" . filesize($targetDocx) . " bytes)\n";
echo "DONE!\n";
