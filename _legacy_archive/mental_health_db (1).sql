-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2026 at 04:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mental_health_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_preassessments`
--

CREATE TABLE `ai_preassessments` (
  `pre_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `conversation_transcript` mediumtext DEFAULT NULL,
  `form_answers` text DEFAULT NULL,
  `ai_report` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_preassessments`
--

INSERT INTO `ai_preassessments` (`pre_id`, `student_id`, `conversation_transcript`, `form_answers`, `ai_report`, `created_at`) VALUES
(1, 2, 'Student: hello aria are you there\nAria: Thank you for reaching out here. There are no right or wrong answers — this is simply a space for you to be honest about how you\'re doing. To start, how would you describe how you\'ve been feeling overall lately?\nStudent: i\'ve been feeling sad lady\nAria: Thank you for trusting me with that. Feeling low or empty can be really isolating. Sometimes writing it down helps — have you seen the 🌈 Private Mood Journal in your dashboard? It\'s a safe place just for your thoughts. Has this been going on for a while, or did something recent make it worse?\nStudent: something recent make it worse\nAria: I\'m really listening to you. When these feelings show up during your day, how do they affect your energy, focus, or motivation?\nStudent: i can\'t focus on the things that is given to me\nAria: That helps me understand your situation better. On a scale from 1 to 10 — with 1 being very calm and 10 being extremely overwhelmed — where would you put yourself right now?\nStudent: around 8:00\nAria: That number tells me a lot. Is there one specific situation, person, or responsibility that you feel is contributing the most to that feeling?\nStudent: play specific situation\nAria: Thank you for explaining that. When things feel this way, what has helped you cope, even a little? We also have a Mindfulness Corner with breathing tools if you\'d like to try that.\nStudent: playing video games\nAria: Everything you’ve shared is important. If you\'re not ready for an appointment, you can always leave a Quick Note for the counselors anonymously. Is there anything else you’d like to add before we wrap up?\nStudent: nothing else ariana\nAria: Thank you so much for opening up today — that really takes courage. You\'ve shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.', '{\"mood_now\":\"\",\"stress_level\":0,\"energy_level\":0,\"sleep_quality\":0,\"main_concern\":\"\",\"self_harm_thoughts\":false}', '{\"mood\":\"concerning\",\"risk_level\":\"High\",\"stress_level\":1,\"energy_level\":1,\"summary\":\"This pre-assessment was generated from your conversation and self-report. It suggests your current stress\\/energy levels and highlights areas that may benefit from support.\",\"key_concerns\":[\"Sleep difficulties reported\"],\"recommendations\":[\"Consider booking a session with your guidance counselor.\",\"Try a short grounding or breathing exercise today (2\\u20133 minutes).\"],\"follow_up_needed\":true}', '2026-03-02 12:06:46'),
(2, 2, 'Student: I feel stressed\n\nAria: I hear how much pressure you\'re under right now. When stress piles up like that, it can affect your mood, sleep, and motivation. What would you say is the biggest source of pressure at the moment — school, family, relationships, or something else?\n\nStudent: relationship\n\nAria: I appreciate you sharing that. It sounds like there\'s quite a lot happening beneath the surface. What would you say has been affecting you the most — academics, relationships, family, or something else?', '{\"mood_now\":\"neutral\",\"stress_level\":5,\"energy_level\":5,\"sleep_quality\":3,\"main_concern\":\"\",\"self_harm_thoughts\":false}', '{\"mood\":\"low\",\"risk_level\":\"Moderate\",\"stress_level\":5,\"energy_level\":5,\"summary\":\"This pre-assessment was generated from your conversation and self-report. It suggests your current stress\\/energy levels and highlights areas that may benefit from support.\",\"key_concerns\":[],\"recommendations\":[\"Consider booking a session with your guidance counselor.\",\"Try a short grounding or breathing exercise today (2\\u20133 minutes).\"],\"follow_up_needed\":false}', '2026-03-03 16:19:41'),
(3, 2, 'Student: hi aria\n\nAria: Thank you for reaching out here. There are no right or wrong answers — this is simply a space for you to be honest about how you\'re doing. To start, how would you describe how you\'ve been feeling overall lately?\n\nStudent: i\'ve been feeling sad lately\n\nAria: Thank you for trusting me with that. Feeling low or empty can be really isolating. Sometimes writing it down helps — have you seen the 🌈 Private Mood Journal in your dashboard? It\'s a safe place just for your thoughts. Has this been going on for a while, or did something recent make it worse?\n\nStudent: accounting recent make it worse\n\nAria: I\'m really listening to you. When these feelings show up during your day, how do they affect your energy, focus, or motivation?\n\nStudent: i can\'t focus on the test that is given to me\n\nAria: That helps me understand your situation better. On a scale from 1 to 10 — with 1 being very calm and 10 being extremely overwhelmed — where would you put yourself right now?\n\nStudent: maria 8\n\nAria: That number tells me a lot. Is there one specific situation, person, or responsibility that you feel is contributing the most to that feeling?\n\nStudent: a person\n\nAria: Thank you for explaining that. When things feel this way, what has helped you cope, even a little? We also have a Mindfulness Corner with breathing tools if you\'d like to try that.\n\nStudent: playing video games\n\nAria: Everything you’ve shared is important. If you\'re not ready for an appointment, you can always leave a Quick Note for the counselors anonymously. Is there anything else you’d like to add before we wrap up?\n\nStudent: nothing are you thank you so much\n\nAria: Thank you so much for opening up today — that really takes courage. You\'ve shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.', '{\"mood_now\":\"concerning\",\"stress_level\":6,\"energy_level\":4,\"sleep_quality\":2,\"main_concern\":\"family\",\"self_harm_thoughts\":false}', '{\"mood\":\"concerning\",\"risk_level\":\"High\",\"stress_level\":6,\"energy_level\":4,\"summary\":\"This pre-assessment was generated from your conversation and self-report. It suggests your current stress\\/energy levels and highlights areas that may benefit from support.\",\"key_concerns\":[\"Sleep difficulties reported\"],\"recommendations\":[\"Consider booking a session with your guidance counselor.\",\"Try a short grounding or breathing exercise today (2\\u20133 minutes).\"],\"follow_up_needed\":true}', '2026-03-03 16:35:41'),
(4, 2, 'Student: hello aria\n\nAria: Thank you for reaching out here. There are no right or wrong answers — this is simply a space for you to be honest about how you\'re doing. To start, how would you describe how you\'ve been feeling overall lately?\n\nStudent: i feel sad baby\n\nAria: Thank you for trusting me with that. Feeling low or empty can be really isolating. Sometimes writing it down helps — have you seen the 🌈 Private Mood Journal in your dashboard? It\'s a safe place just for your thoughts. Has this been going on for a while, or did something recent make it worse?\n\nStudent: something recent make it worse\n\nAria: I\'m really listening to you. When these feelings show up during your day, how do they affect your energy, focus, or motivation?\n\nStudent: i can\'t focus on the desk that is given to me\n\nAria: That helps me understand your situation better. On a scale from 1 to 10 — with 1 being very calm and 10 being extremely overwhelmed — where would you put yourself right now?\n\nStudent: around 8:00\n\nAria: That number tells me a lot. Is there one specific situation, person, or responsibility that you feel is contributing the most to that feeling?\n\nStudent: a person\n\nAria: Thank you for explaining that. When things feel this way, what has helped you cope, even a little? We also have a Mindfulness Corner with breathing tools if you\'d like to try that.', '{\"mood_now\":\"neutral\",\"stress_level\":5,\"energy_level\":5,\"sleep_quality\":3,\"main_concern\":\"\",\"self_harm_thoughts\":false}', '{\"mood\":\"low\",\"risk_level\":\"Moderate\",\"stress_level\":5,\"energy_level\":5,\"summary\":\"This pre-assessment was generated from your conversation and self-report. It suggests your current stress\\/energy levels and highlights areas that may benefit from support.\",\"key_concerns\":[],\"recommendations\":[\"Consider booking a session with your guidance counselor.\",\"Try a short grounding or breathing exercise today (2\\u20133 minutes).\"],\"follow_up_needed\":false}', '2026-03-03 16:40:08'),
(5, 2, 'Aria: Hi teststudent, I\'ve been waiting for you. How are you feeling today? I\'m here to listen and whatever is on your mind, we can talk through it together.\n\nStudent: i feel stressed right now\n\nAria: I hear how much pressure you\'re under. Some things that genuinely help: break your tasks into smaller steps, schedule short breaks, and if your mind won\'t stop racing, try writing everything down in your Mood Journal to clear your head. What\'s the biggest source of pressure right now — school, family, or something else?\n\nStudent: ang school family and\n\nAria: School pressure is real — it\'s not just about grades but also expectations from yourself and others. Some practical tips: prioritize your tasks by urgency, study in focused 45-minute blocks with short breaks, and don\'t be afraid to ask for help from teachers or groupmates. What part of school is weighing on you the most?\n\nStudent: capstone\n\nAria: I\'m really listening. When these feelings come up during your day, how do they affect your energy, focus, or motivation to do things?\n\nStudent: i can\'t focus on the dust that is given to me\n\nAria: That helps me understand better. On a scale of 1 to 10 — 1 being very calm, 10 being extremely overwhelmed — where would you put yourself right now?\n\nStudent: 10 10\n\nAria: That number tells me a lot. Is there one specific situation, person, or responsibility that feels like the biggest contributor to how you\'re feeling?\n\nStudent: a person\n\nAria: Thank you for sharing that. When things get this heavy, what has helped you even a little in the past? Also, our Mindfulness Corner has quick breathing tools if you\'d like to try something right now.\n\nStudent: playing video games help me cook with this situation\n\nAria: I\'m glad you\'re looking for ways to help yourself — that\'s a really positive step. Here are some general wellbeing tips:\n• Talk about what you\'re feeling — to a friend, journal, or here with me\n• Move your body — even a short walk can shift your mood\n• Rest without guilt — your mind needs recovery time\n• Use the portal tools: Mood Journal, Mindfulness Corner, or Anonymous Quick Note\n• If things feel too heavy to carry alone, reach out to a counselor\nWhat area would you like more specific advice on?\n\nStudent: about about\n\nAria: Thank you so much for opening up today — that really takes courage. You\'ve shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.\n\nStudent: thank you aria\n\nAria: Thank you so much for opening up today — that really takes courage. You\'ve shared a lot, and it all matters. If anything feels heavy after this, please consider checking in with a trusted adult or your school counselor. You deserve support and care.', '{\"mood_now\":\"neutral\",\"stress_level\":5,\"energy_level\":5,\"sleep_quality\":3,\"main_concern\":\"\",\"self_harm_thoughts\":false}', '{\"mood\":\"low\",\"risk_level\":\"Moderate\",\"stress_level\":5,\"energy_level\":5,\"summary\":\"This pre-assessment was generated from your conversation and self-report. It suggests your current stress\\/energy levels and highlights areas that may benefit from support.\",\"key_concerns\":[],\"recommendations\":[\"Consider booking a session with your guidance counselor.\",\"Try a short grounding or breathing exercise today (2\\u20133 minutes).\"],\"follow_up_needed\":false}', '2026-03-05 14:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `anonymous_notes`
--

CREATE TABLE `anonymous_notes` (
  `note_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `counselor_id` int(11) DEFAULT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anonymous_notes`
--

INSERT INTO `anonymous_notes` (`note_id`, `student_id`, `message`, `reply`, `replied_at`, `counselor_id`, `status`, `created_at`) VALUES
(1, 2, 'i am stress', 'did something bad happened?\\r\\n', '2026-03-02 18:47:16', 3, 'new', '2026-03-02 18:43:38'),
(2, 2, 'i feel stressed', NULL, NULL, NULL, 'new', '2026-03-03 01:47:02'),
(3, 5, 'i feel sad because my bf dump me', NULL, NULL, NULL, 'new', '2026-03-15 03:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `anonymous_note_messages`
--

CREATE TABLE `anonymous_note_messages` (
  `message_id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `sender_type` enum('student','counselor','admin') NOT NULL,
  `message_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anonymous_note_messages`
--

INSERT INTO `anonymous_note_messages` (`message_id`, `note_id`, `sender_type`, `message_text`, `created_at`) VALUES
(1, 1, 'student', 'i am stress', '2026-03-02 18:43:38'),
(2, 1, 'admin', 'did something bad happened?\\r\\n', '2026-03-02 18:47:16'),
(3, 1, 'student', 'im b ad mood', '2026-03-03 01:38:59'),
(4, 2, 'student', 'i feel stressed', '2026-03-03 01:47:02'),
(5, 3, 'student', 'i feel sad because my bf dump me', '2026-03-15 03:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `duration_min` int(11) NOT NULL DEFAULT 30,
  `status` enum('requested','confirmed','declined','cancelled','completed') NOT NULL DEFAULT 'requested',
  `reason` text DEFAULT NULL,
  `counselor_message` text DEFAULT NULL,
  `is_priority` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `student_id`, `counselor_id`, `scheduled_at`, `duration_min`, `status`, `reason`, `counselor_message`, `is_priority`, `created_at`, `updated_at`) VALUES
(1, 4, 3, '2026-03-15 19:41:00', 30, 'confirmed', 'i want to talk', '', 0, '2026-03-15 03:42:05', '2026-03-15 18:10:51'),
(2, 5, 3, '2026-03-16 19:53:00', 30, 'confirmed', '', '', 0, '2026-03-15 03:53:45', '2026-03-15 18:11:33'),
(3, 2, 3, '2026-03-20 20:02:00', 30, 'confirmed', '', '', 0, '2026-03-15 04:02:52', '2026-03-15 18:11:37'),
(4, 7, 8, '2026-03-21 10:14:00', 30, 'completed', '', '', 0, '2026-03-15 18:14:31', '2026-03-15 19:03:28'),
(5, 7, 3, '2026-03-16 10:17:00', 30, 'requested', '', NULL, 0, '2026-03-15 18:17:50', '2026-03-15 18:17:50');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_questions`
--

CREATE TABLE `assessment_questions` (
  `question_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `question_text` text NOT NULL,
  `question_number` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_questions`
--

INSERT INTO `assessment_questions` (`question_id`, `category`, `question_text`, `question_number`, `created_at`) VALUES
(1, 'Depression', 'I feel sad or empty most of the time', 1, '2026-03-02 10:11:45'),
(2, 'Depression', 'I lose interest in activities I usually enjoy', 2, '2026-03-02 10:11:45'),
(3, 'Depression', 'I have difficulty concentrating on tasks', 3, '2026-03-02 10:11:45'),
(4, 'Depression', 'I feel worthless or guilty about things', 4, '2026-03-02 10:11:45'),
(5, 'Depression', 'I have thoughts of hurting myself', 5, '2026-03-02 10:11:45'),
(6, 'Anxiety', 'I feel nervous or anxious most days', 6, '2026-03-02 10:11:45'),
(7, 'Anxiety', 'I worry about things I cannot control', 7, '2026-03-02 10:11:45'),
(8, 'Anxiety', 'I experience sudden panic attacks', 8, '2026-03-02 10:11:45'),
(9, 'Anxiety', 'I avoid situations that make me anxious', 9, '2026-03-02 10:11:45'),
(10, 'Anxiety', 'My anxiety interferes with my daily activities', 10, '2026-03-02 10:11:45'),
(11, 'Stress', 'I feel overwhelmed by my academic workload', 11, '2026-03-02 10:11:45'),
(12, 'Stress', 'I have difficulty managing my time', 12, '2026-03-02 10:11:45'),
(13, 'Stress', 'I experience physical symptoms of stress (headaches, muscle tension)', 13, '2026-03-02 10:11:45'),
(14, 'Stress', 'I feel irritable or easily frustrated', 14, '2026-03-02 10:11:45'),
(15, 'Stress', 'I have trouble sleeping due to stress', 15, '2026-03-02 10:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_scores`
--

CREATE TABLE `assessment_scores` (
  `score_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `depression_score` int(11) DEFAULT NULL,
  `anxiety_score` int(11) DEFAULT NULL,
  `stress_score` int(11) DEFAULT NULL,
  `overall_score` int(11) DEFAULT NULL,
  `risk_level` enum('Low','Moderate','High','Critical') NOT NULL,
  `assessment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `report_generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `counselor_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_scores`
--

INSERT INTO `assessment_scores` (`score_id`, `user_id`, `depression_score`, `anxiety_score`, `stress_score`, `overall_score`, `risk_level`, `assessment_date`, `report_generated_at`, `counselor_notes`) VALUES
(1, 2, 0, 0, 0, 0, 'Low', '2026-03-04 01:31:21', '2026-03-04 01:31:21', NULL),
(2, 2, 0, 0, 0, 0, 'Low', '2026-03-04 11:24:23', '2026-03-04 11:24:23', NULL),
(3, 2, 0, 0, 0, 0, 'Low', '2026-03-04 11:36:13', '2026-03-04 11:36:13', NULL),
(4, 2, 0, 0, 0, 0, 'Low', '2026-03-04 11:39:29', '2026-03-04 11:39:29', NULL),
(5, 2, 0, 0, 0, 0, 'Low', '2026-03-04 11:40:14', '2026-03-04 11:40:14', NULL),
(6, 2, 19, 19, 19, 19, 'Critical', '2026-03-04 11:43:32', '2026-03-04 11:43:32', NULL),
(7, 2, 18, 18, 19, 18, 'Critical', '2026-03-04 11:49:37', '2026-03-04 11:49:37', NULL),
(8, 5, 1, 2, 4, 2, 'Low', '2026-03-15 03:34:26', '2026-03-15 03:34:26', NULL),
(9, 4, 20, 20, 20, 20, 'Critical', '2026-03-15 03:41:32', '2026-03-15 03:41:32', NULL),
(10, 6, 15, 15, 15, 15, 'High', '2026-03-15 04:01:32', '2026-03-15 04:01:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `counselor_availability`
--

CREATE TABLE `counselor_availability` (
  `availability_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sun,1=Mon,...,6=Sat',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counselor_notes`
--

CREATE TABLE `counselor_notes` (
  `note_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `note_text` text NOT NULL,
  `recommendation` varchar(500) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselor_notes`
--

INSERT INTO `counselor_notes` (`note_id`, `counselor_id`, `student_id`, `note_text`, `recommendation`, `follow_up_date`, `created_at`, `updated_at`) VALUES
(1, 3, 7, 'wawa ka naman lods', 'huminga ka lang okay na yan', '2026-03-22', '2026-03-15 19:03:03', '2026-03-15 19:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `mood_logs`
--

CREATE TABLE `mood_logs` (
  `log_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mood_score` tinyint(4) NOT NULL COMMENT '1=Sad/Critical, 5=Happy/Great',
  `mood_emoji` varchar(10) NOT NULL,
  `note` text DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mood_logs`
--

INSERT INTO `mood_logs` (`log_id`, `student_id`, `mood_score`, `mood_emoji`, `note`, `logged_at`) VALUES
(1, 2, 2, '😕', 'i am worried about my capstone, what if i dont complete the output on time', '2026-03-02 18:33:14'),
(2, 2, 5, '😊', '', '2026-03-04 01:30:35');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 3, 'New Appointment Request', 'Gelil Pogi has requested an appointment on March 15, 2026 at 7:41 PM.', 'appointment', 1, '2026-03-15 03:42:05'),
(2, 3, 'New Appointment Request', 'Jenny Rose Solomon has requested an appointment on March 16, 2026 at 7:53 PM.', 'appointment', 1, '2026-03-15 03:53:45'),
(3, 3, 'New Appointment Request', 'teststudenttt has requested an appointment on March 20, 2026 at 8:02 PM.', 'appointment', 1, '2026-03-15 04:02:52'),
(4, 3, 'New Appointment Request', 'Jillian has requested an appointment on March 21, 2026 at 10:14 AM.', 'appointment', 1, '2026-03-15 18:14:31'),
(5, 3, 'New Appointment Request', 'Jillian has requested an appointment on March 16, 2026 at 10:17 AM.', 'appointment', 1, '2026-03-15 18:17:50'),
(6, 7, 'Appointment Cancelled', 'Your session on March 21, 2026 at 10:14 AM has been cancelled.', 'appointment', 1, '2026-03-15 18:22:43'),
(7, 7, 'Appointment Confirmed ✅', 'Your session with Dr. Mang Kepweng on March 21, 2026 at 10:14 AM has been confirmed.', 'appointment', 1, '2026-03-15 18:22:48'),
(8, 7, 'Appointment Confirmed ✅', 'Your session with Dr. Mang Kepweng on March 21, 2026 at 10:14 AM has been confirmed.', 'appointment', 1, '2026-03-15 18:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `session_logs`
--

CREATE TABLE `session_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `activity` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_logs`
--

INSERT INTO `session_logs` (`log_id`, `user_id`, `login_time`, `logout_time`, `activity`) VALUES
(1, 2, '2026-03-02 10:14:04', NULL, 'User logged in'),
(2, 2, '2026-03-02 10:14:29', NULL, 'Student started AI chat session (fallback counselor)'),
(3, 2, '2026-03-02 10:45:22', NULL, 'User logged in'),
(4, 2, '2026-03-02 10:46:10', NULL, 'Student started AI chat session (fallback counselor)'),
(5, 2, '2026-03-02 10:47:12', NULL, 'Student started AI chat session (fallback counselor)'),
(6, 2, '2026-03-02 10:53:13', NULL, 'User logged out'),
(7, 2, '2026-03-02 11:24:27', NULL, 'User logged in'),
(8, 2, '2026-03-02 11:24:30', NULL, 'User logged out'),
(9, 3, '2026-03-02 11:27:28', NULL, 'User logged in'),
(10, 3, '2026-03-02 11:45:52', NULL, 'User logged out'),
(11, 2, '2026-03-02 11:55:49', NULL, 'User logged in'),
(12, 2, '2026-03-02 11:58:19', NULL, 'Student started AI chat session (fallback counselor)'),
(13, 2, '2026-03-02 12:04:23', NULL, 'Student started AI chat session (fallback counselor)'),
(14, 2, '2026-03-02 12:07:35', NULL, 'User logged out'),
(15, 2, '2026-03-02 12:20:17', NULL, 'User logged in'),
(16, 2, '2026-03-02 12:20:24', NULL, 'User logged out'),
(17, 2, '2026-03-02 12:28:34', NULL, 'User logged in'),
(18, 2, '2026-03-02 12:35:57', NULL, 'User logged in'),
(19, 2, '2026-03-02 12:50:33', NULL, 'User logged out'),
(20, 2, '2026-03-02 18:32:40', NULL, 'User logged in'),
(21, 2, '2026-03-02 18:33:14', NULL, 'Student logged mood: 😕'),
(22, 2, '2026-03-02 18:33:20', NULL, 'User logged out'),
(23, 3, '2026-03-02 18:33:37', NULL, 'User logged in'),
(24, 3, '2026-03-02 18:43:13', NULL, 'User logged out'),
(25, 2, '2026-03-02 18:43:23', NULL, 'User logged in'),
(26, 2, '2026-03-02 18:43:38', NULL, 'Student sent an anonymous quick note'),
(27, 2, '2026-03-02 18:43:43', NULL, 'User logged out'),
(28, 3, '2026-03-02 18:43:54', NULL, 'User logged in'),
(29, 3, '2026-03-02 18:47:16', NULL, 'Admin replied to anonymous note #1'),
(30, 3, '2026-03-02 18:47:23', NULL, 'User logged out'),
(31, 2, '2026-03-02 18:47:42', NULL, 'User logged in'),
(32, 2, '2026-03-02 19:04:02', NULL, 'User logged out'),
(33, 2, '2026-03-02 19:11:36', NULL, 'User logged in'),
(34, 2, '2026-03-02 20:50:36', NULL, 'User logged in'),
(35, 2, '2026-03-03 01:37:18', NULL, 'User logged in'),
(36, 2, '2026-03-03 01:38:59', NULL, 'Student replied to anonymous note #1'),
(37, 2, '2026-03-03 01:41:04', NULL, 'User logged out'),
(38, 3, '2026-03-03 01:41:57', NULL, 'User logged in'),
(39, 3, '2026-03-03 01:43:36', NULL, 'User logged out'),
(40, 3, '2026-03-03 01:46:08', NULL, 'User logged in'),
(41, 3, '2026-03-03 01:46:32', NULL, 'User logged out'),
(42, 2, '2026-03-03 01:46:46', NULL, 'User logged in'),
(43, 2, '2026-03-03 01:47:02', NULL, 'Student sent a new anonymous quick note'),
(44, 2, '2026-03-03 01:47:06', NULL, 'User logged out'),
(45, 3, '2026-03-03 01:47:24', NULL, 'User logged in'),
(46, 2, '2026-03-03 16:03:40', NULL, 'User logged in'),
(47, 2, '2026-03-03 16:04:31', NULL, 'User logged in'),
(48, 2, '2026-03-03 16:04:49', NULL, 'Student started AI chat session (fallback counselor)'),
(49, 2, '2026-03-03 16:06:04', NULL, 'Student started AI chat session (fallback counselor)'),
(50, 2, '2026-03-03 16:08:42', NULL, 'Student started AI chat session (fallback counselor)'),
(51, 2, '2026-03-03 16:46:37', NULL, 'User logged out'),
(52, 3, '2026-03-03 16:48:37', NULL, 'User logged in'),
(53, 2, '2026-03-04 01:32:27', '2026-03-04 01:32:27', 'User logged in | Session ended'),
(54, 2, '2026-03-04 01:30:35', NULL, 'Student logged mood: 😊'),
(55, 2, '2026-03-04 01:31:21', NULL, 'Completed assessment with score 0'),
(56, 2, '2026-03-04 11:25:50', '2026-03-04 11:25:50', 'User logged in | Session ended'),
(57, 2, '2026-03-04 11:24:23', NULL, 'Completed assessment with score 0'),
(58, 2, '2026-03-04 11:36:24', '2026-03-04 11:36:24', 'User logged in | Session ended'),
(59, 2, '2026-03-04 11:36:13', NULL, 'Completed assessment with score 0'),
(60, 2, '2026-03-04 11:38:50', NULL, 'User logged in'),
(61, 2, '2026-03-04 11:39:29', NULL, 'Completed assessment with score 0'),
(62, 2, '2026-03-04 11:40:14', NULL, 'Completed assessment with score 0'),
(63, 2, '2026-03-04 11:43:32', NULL, 'Completed assessment with score 19'),
(64, 2, '2026-03-04 11:49:37', NULL, 'Completed assessment with score 18'),
(65, 2, '2026-03-04 12:11:44', NULL, 'User logged in'),
(66, 2, '2026-03-04 12:12:15', NULL, 'Student started AI chat session (fallback counselor)'),
(67, 2, '2026-03-04 12:24:23', NULL, 'User logged in'),
(68, 2, '2026-03-04 12:24:56', NULL, 'Student started AI chat session (fallback counselor)'),
(69, 2, '2026-03-04 12:31:25', NULL, 'Student started AI chat session (fallback counselor)'),
(70, 2, '2026-03-04 14:00:59', NULL, 'User logged in'),
(71, 2, '2026-03-05 14:52:53', '2026-03-05 14:52:53', 'User logged in | Session ended'),
(72, 2, '2026-03-05 14:18:21', NULL, 'Student started AI chat session (fallback counselor)'),
(73, 2, '2026-03-05 14:25:30', NULL, 'Student started AI chat session (fallback counselor)'),
(74, 2, '2026-03-05 14:53:35', '2026-03-05 14:53:35', 'User logged in | Session ended'),
(75, 2, '2026-03-05 14:54:46', '2026-03-05 14:54:46', 'User logged in | Session ended'),
(76, 2, '2026-03-05 14:59:18', '2026-03-05 14:59:18', 'User logged in | Session ended'),
(77, 2, '2026-03-05 15:00:55', '2026-03-05 15:00:55', 'User logged in | Session ended'),
(78, 2, '2026-03-06 01:56:19', NULL, 'User logged in'),
(79, 4, '2026-03-15 03:13:19', NULL, 'User logged in'),
(80, 5, '2026-03-15 03:28:20', NULL, 'User logged in'),
(81, 3, '2026-03-15 03:31:35', NULL, 'User logged in'),
(82, 5, '2026-03-15 03:32:37', NULL, 'User logged in'),
(83, 5, '2026-03-15 03:34:26', NULL, 'Completed assessment with score 2'),
(84, 5, '2026-03-15 03:34:59', NULL, 'Student sent a new anonymous quick note'),
(85, 3, '2026-03-15 03:35:39', NULL, 'User logged in'),
(86, 4, '2026-03-15 03:39:13', NULL, 'User logged in'),
(87, 4, '2026-03-15 03:41:32', NULL, 'Completed assessment with score 20'),
(88, 4, '2026-03-15 03:42:05', NULL, 'Student requested counselor appointment'),
(89, 3, '2026-03-15 03:43:10', NULL, 'User logged in'),
(90, 4, '2026-03-15 03:46:16', NULL, 'User logged in'),
(91, 4, '2026-03-15 03:46:31', NULL, 'Student started AI chat session (fallback counselor)'),
(92, 3, '2026-03-15 03:51:34', NULL, 'User logged in'),
(93, 5, '2026-03-15 03:53:31', NULL, 'User logged in'),
(94, 5, '2026-03-15 03:53:45', NULL, 'Student requested counselor appointment'),
(95, 3, '2026-03-15 03:54:11', NULL, 'User logged in'),
(96, 6, '2026-03-15 03:57:37', NULL, 'User logged in'),
(97, 3, '2026-03-15 03:58:34', NULL, 'User logged in'),
(98, 6, '2026-03-15 03:59:31', NULL, 'User logged in'),
(99, 3, '2026-03-15 04:00:40', '2026-03-15 04:00:40', 'User logged in | Session ended'),
(100, 6, '2026-03-15 04:00:48', NULL, 'User logged in'),
(101, 6, '2026-03-15 04:01:32', NULL, 'Completed assessment with score 15'),
(102, 2, '2026-03-15 04:02:22', NULL, 'User logged in'),
(103, 2, '2026-03-15 04:02:52', NULL, 'Student requested counselor appointment'),
(104, 3, '2026-03-15 18:11:44', '2026-03-15 18:11:44', 'User logged in | Session ended'),
(105, 3, '2026-03-15 18:10:33', NULL, 'Head counselor cleared priority for appointment #1'),
(106, 3, '2026-03-15 18:10:51', NULL, 'Head counselor set appointment #1 to confirmed'),
(107, 3, '2026-03-15 18:11:27', NULL, 'Head counselor cleared priority for appointment #3'),
(108, 3, '2026-03-15 18:11:33', NULL, 'Head counselor set appointment #2 to confirmed'),
(109, 3, '2026-03-15 18:11:37', NULL, 'Head counselor set appointment #3 to confirmed'),
(110, 4, '2026-03-15 18:12:18', '2026-03-15 18:12:18', 'User logged in | Session ended'),
(111, 7, '2026-03-15 18:18:32', '2026-03-15 18:18:32', 'User logged in | Session ended'),
(112, 7, '2026-03-15 18:14:31', NULL, 'Student requested counselor appointment'),
(113, 7, '2026-03-15 18:17:50', NULL, 'Student requested counselor appointment'),
(114, 3, '2026-03-15 18:20:38', '2026-03-15 18:20:38', 'User logged in | Session ended'),
(115, 3, '2026-03-15 18:22:11', '2026-03-15 18:22:11', 'User logged in | Session ended'),
(116, 3, '2026-03-15 18:21:42', NULL, 'Head counselor reassigned appointment #4 to counselor 3'),
(117, 3, '2026-03-15 18:21:55', NULL, 'Head counselor reassigned appointment #4 to counselor 8'),
(118, 8, '2026-03-15 18:23:03', '2026-03-15 18:23:03', 'User logged in | Session ended'),
(119, 8, '2026-03-15 18:22:43', NULL, 'Counselor updated appointment #4 to cancelled'),
(120, 8, '2026-03-15 18:22:48', NULL, 'Counselor updated appointment #4 to confirmed'),
(121, 8, '2026-03-15 18:22:51', NULL, 'Counselor updated appointment #4 to confirmed'),
(122, 3, '2026-03-15 18:54:02', NULL, 'User logged in'),
(123, 3, '2026-03-15 19:03:33', '2026-03-15 19:03:33', 'User logged in | Session ended'),
(124, 3, '2026-03-15 19:03:28', NULL, 'Head counselor set appointment #4 to completed'),
(125, 7, '2026-03-15 19:03:41', NULL, 'User logged in');

-- --------------------------------------------------------

--
-- Table structure for table `student_responses`
--

CREATE TABLE `student_responses` (
  `response_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `response_value` int(11) NOT NULL,
  `assessment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_responses`
--

INSERT INTO `student_responses` (`response_id`, `user_id`, `question_id`, `response_value`, `assessment_date`) VALUES
(1, 2, 1, 0, '2026-03-04 01:31:21'),
(2, 2, 2, 0, '2026-03-04 01:31:21'),
(3, 2, 3, 0, '2026-03-04 01:31:21'),
(4, 2, 4, 0, '2026-03-04 01:31:21'),
(5, 2, 5, 0, '2026-03-04 01:31:21'),
(6, 2, 6, 0, '2026-03-04 01:31:21'),
(7, 2, 7, 0, '2026-03-04 01:31:21'),
(8, 2, 8, 0, '2026-03-04 01:31:21'),
(9, 2, 9, 0, '2026-03-04 01:31:21'),
(10, 2, 10, 0, '2026-03-04 01:31:21'),
(11, 2, 11, 0, '2026-03-04 01:31:21'),
(12, 2, 12, 0, '2026-03-04 01:31:21'),
(13, 2, 13, 0, '2026-03-04 01:31:21'),
(14, 2, 14, 0, '2026-03-04 01:31:21'),
(15, 2, 15, 0, '2026-03-04 01:31:21'),
(16, 2, 1, 0, '2026-03-04 11:24:23'),
(17, 2, 2, 0, '2026-03-04 11:24:23'),
(18, 2, 3, 0, '2026-03-04 11:24:23'),
(19, 2, 4, 0, '2026-03-04 11:24:23'),
(20, 2, 5, 0, '2026-03-04 11:24:23'),
(21, 2, 6, 0, '2026-03-04 11:24:23'),
(22, 2, 7, 0, '2026-03-04 11:24:23'),
(23, 2, 8, 0, '2026-03-04 11:24:23'),
(24, 2, 9, 0, '2026-03-04 11:24:23'),
(25, 2, 10, 0, '2026-03-04 11:24:23'),
(26, 2, 11, 0, '2026-03-04 11:24:23'),
(27, 2, 12, 0, '2026-03-04 11:24:23'),
(28, 2, 13, 0, '2026-03-04 11:24:23'),
(29, 2, 14, 0, '2026-03-04 11:24:23'),
(30, 2, 15, 0, '2026-03-04 11:24:23'),
(31, 2, 1, 0, '2026-03-04 11:36:13'),
(32, 2, 2, 0, '2026-03-04 11:36:13'),
(33, 2, 3, 0, '2026-03-04 11:36:13'),
(34, 2, 4, 0, '2026-03-04 11:36:13'),
(35, 2, 5, 0, '2026-03-04 11:36:13'),
(36, 2, 6, 0, '2026-03-04 11:36:13'),
(37, 2, 7, 0, '2026-03-04 11:36:13'),
(38, 2, 8, 0, '2026-03-04 11:36:13'),
(39, 2, 9, 0, '2026-03-04 11:36:13'),
(40, 2, 10, 0, '2026-03-04 11:36:13'),
(41, 2, 11, 0, '2026-03-04 11:36:13'),
(42, 2, 12, 0, '2026-03-04 11:36:13'),
(43, 2, 13, 0, '2026-03-04 11:36:13'),
(44, 2, 14, 0, '2026-03-04 11:36:13'),
(45, 2, 15, 0, '2026-03-04 11:36:13'),
(46, 2, 1, 0, '2026-03-04 11:39:29'),
(47, 2, 2, 0, '2026-03-04 11:39:29'),
(48, 2, 3, 0, '2026-03-04 11:39:29'),
(49, 2, 4, 0, '2026-03-04 11:39:29'),
(50, 2, 5, 0, '2026-03-04 11:39:29'),
(51, 2, 6, 0, '2026-03-04 11:39:29'),
(52, 2, 7, 0, '2026-03-04 11:39:29'),
(53, 2, 8, 0, '2026-03-04 11:39:29'),
(54, 2, 9, 0, '2026-03-04 11:39:29'),
(55, 2, 10, 0, '2026-03-04 11:39:29'),
(56, 2, 11, 0, '2026-03-04 11:39:29'),
(57, 2, 12, 0, '2026-03-04 11:39:29'),
(58, 2, 13, 0, '2026-03-04 11:39:29'),
(59, 2, 14, 0, '2026-03-04 11:39:29'),
(60, 2, 15, 0, '2026-03-04 11:39:29'),
(61, 2, 1, 0, '2026-03-04 11:40:14'),
(62, 2, 2, 0, '2026-03-04 11:40:14'),
(63, 2, 3, 0, '2026-03-04 11:40:14'),
(64, 2, 4, 0, '2026-03-04 11:40:14'),
(65, 2, 5, 0, '2026-03-04 11:40:14'),
(66, 2, 6, 0, '2026-03-04 11:40:14'),
(67, 2, 7, 0, '2026-03-04 11:40:14'),
(68, 2, 8, 0, '2026-03-04 11:40:14'),
(69, 2, 9, 0, '2026-03-04 11:40:14'),
(70, 2, 10, 0, '2026-03-04 11:40:14'),
(71, 2, 11, 0, '2026-03-04 11:40:14'),
(72, 2, 12, 0, '2026-03-04 11:40:14'),
(73, 2, 13, 0, '2026-03-04 11:40:14'),
(74, 2, 14, 0, '2026-03-04 11:40:14'),
(75, 2, 15, 0, '2026-03-04 11:40:14'),
(76, 2, 1, 4, '2026-03-04 11:43:32'),
(77, 2, 2, 4, '2026-03-04 11:43:32'),
(78, 2, 3, 4, '2026-03-04 11:43:32'),
(79, 2, 4, 4, '2026-03-04 11:43:32'),
(80, 2, 5, 3, '2026-03-04 11:43:32'),
(81, 2, 6, 4, '2026-03-04 11:43:32'),
(82, 2, 7, 3, '2026-03-04 11:43:32'),
(83, 2, 8, 4, '2026-03-04 11:43:32'),
(84, 2, 9, 4, '2026-03-04 11:43:32'),
(85, 2, 10, 4, '2026-03-04 11:43:32'),
(86, 2, 11, 4, '2026-03-04 11:43:32'),
(87, 2, 12, 4, '2026-03-04 11:43:32'),
(88, 2, 13, 4, '2026-03-04 11:43:32'),
(89, 2, 14, 4, '2026-03-04 11:43:32'),
(90, 2, 15, 3, '2026-03-04 11:43:32'),
(91, 2, 1, 4, '2026-03-04 11:49:37'),
(92, 2, 2, 3, '2026-03-04 11:49:37'),
(93, 2, 3, 4, '2026-03-04 11:49:37'),
(94, 2, 4, 3, '2026-03-04 11:49:37'),
(95, 2, 5, 4, '2026-03-04 11:49:37'),
(96, 2, 6, 4, '2026-03-04 11:49:37'),
(97, 2, 7, 3, '2026-03-04 11:49:37'),
(98, 2, 8, 4, '2026-03-04 11:49:37'),
(99, 2, 9, 3, '2026-03-04 11:49:37'),
(100, 2, 10, 4, '2026-03-04 11:49:37'),
(101, 2, 11, 4, '2026-03-04 11:49:37'),
(102, 2, 12, 3, '2026-03-04 11:49:37'),
(103, 2, 13, 4, '2026-03-04 11:49:37'),
(104, 2, 14, 4, '2026-03-04 11:49:37'),
(105, 2, 15, 4, '2026-03-04 11:49:37'),
(106, 5, 1, 0, '2026-03-15 03:34:26'),
(107, 5, 2, 0, '2026-03-15 03:34:26'),
(108, 5, 3, 1, '2026-03-15 03:34:26'),
(109, 5, 4, 0, '2026-03-15 03:34:26'),
(110, 5, 5, 0, '2026-03-15 03:34:26'),
(111, 5, 6, 0, '2026-03-15 03:34:26'),
(112, 5, 7, 2, '2026-03-15 03:34:26'),
(113, 5, 8, 0, '2026-03-15 03:34:26'),
(114, 5, 9, 0, '2026-03-15 03:34:26'),
(115, 5, 10, 0, '2026-03-15 03:34:26'),
(116, 5, 11, 2, '2026-03-15 03:34:26'),
(117, 5, 12, 1, '2026-03-15 03:34:26'),
(118, 5, 13, 0, '2026-03-15 03:34:26'),
(119, 5, 14, 1, '2026-03-15 03:34:26'),
(120, 5, 15, 0, '2026-03-15 03:34:26'),
(121, 4, 1, 4, '2026-03-15 03:41:32'),
(122, 4, 2, 4, '2026-03-15 03:41:32'),
(123, 4, 3, 4, '2026-03-15 03:41:32'),
(124, 4, 4, 4, '2026-03-15 03:41:32'),
(125, 4, 5, 4, '2026-03-15 03:41:32'),
(126, 4, 6, 4, '2026-03-15 03:41:32'),
(127, 4, 7, 4, '2026-03-15 03:41:32'),
(128, 4, 8, 4, '2026-03-15 03:41:32'),
(129, 4, 9, 4, '2026-03-15 03:41:32'),
(130, 4, 10, 4, '2026-03-15 03:41:32'),
(131, 4, 11, 4, '2026-03-15 03:41:32'),
(132, 4, 12, 4, '2026-03-15 03:41:32'),
(133, 4, 13, 4, '2026-03-15 03:41:32'),
(134, 4, 14, 4, '2026-03-15 03:41:32'),
(135, 4, 15, 4, '2026-03-15 03:41:32'),
(136, 6, 1, 3, '2026-03-15 04:01:32'),
(137, 6, 2, 3, '2026-03-15 04:01:32'),
(138, 6, 3, 3, '2026-03-15 04:01:32'),
(139, 6, 4, 3, '2026-03-15 04:01:32'),
(140, 6, 5, 3, '2026-03-15 04:01:32'),
(141, 6, 6, 3, '2026-03-15 04:01:32'),
(142, 6, 7, 3, '2026-03-15 04:01:32'),
(143, 6, 8, 3, '2026-03-15 04:01:32'),
(144, 6, 9, 3, '2026-03-15 04:01:32'),
(145, 6, 10, 3, '2026-03-15 04:01:32'),
(146, 6, 11, 3, '2026-03-15 04:01:32'),
(147, 6, 12, 3, '2026-03-15 04:01:32'),
(148, 6, 13, 3, '2026-03-15 04:01:32'),
(149, 6, 14, 3, '2026-03-15 04:01:32'),
(150, 6, 15, 3, '2026-03-15 04:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `user_type` enum('student','counselor','admin') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `full_name`, `roll_number`, `user_type`, `date_of_birth`, `gender`, `contact_number`, `department`, `semester`, `created_at`, `updated_at`) VALUES
(2, 'teststudent@gmail.com', '$2y$10$ZpOM8EP61IjyXgrLPpEnhuulYegZjvF911bBln1U8o7rYl0/.9qZ.', 'teststudenttt', '23-sc-4117', 'student', '2021-01-01', 'Male', '09120129521', 'BSIT', 1, '2026-03-02 10:13:52', '2026-03-05 15:00:37'),
(3, 'headcounselor@gmail.com', '$2y$10$XT2NKAn7wj0g7F6OraWJFeln1PNVCXwi8s1cChQHSRkgRxtFfz4J6', 'Head Counselor', NULL, 'admin', NULL, NULL, NULL, NULL, NULL, '2026-03-02 11:22:34', '2026-03-02 11:26:45'),
(4, 'gelil@gmail.com', '$2y$10$/6iP5XLUoZ6iyCqP0wN0fuvYbk3HQsty8UDMYf0WAG7bHIEuOGpdK', 'Gelil Pogi', '', 'student', '2005-06-15', 'Male', '+639164947108', 'Information Technology', NULL, '2026-03-15 03:11:59', '2026-03-15 03:11:59'),
(5, 'jenny@gmail.com', '$2y$10$CbrRiYyw69B9TJMoGU4PZO/anLtMytwQyNMxJfOcJMftUGPhEYVs.', 'Jenny Rose Solomon', '23-SC-1111', 'student', '2018-02-17', 'Male', '+639164947104', 'Information Technology', NULL, '2026-03-15 03:26:38', '2026-03-15 03:26:38'),
(6, 'joshuarulloda2003@gmail.com', '$2y$10$UsoYR1p0sAMg2i47LCYeSu4EUrS321/UJmjjyuTt0wRCXluIWqUkS', 'joshua', '23-SC-1117', 'student', '2026-03-13', 'Male', '+639164947101', 'Information Technology', NULL, '2026-03-15 03:57:21', '2026-03-15 03:57:21'),
(7, 'jillian@gmail.com', '$2y$10$mtj7Z57JzvKRpawV.RNuOeJppXE3RBqChxwodZVLzwUl4u0RI/wFC', 'Jillian', '23-SC-1234', 'student', '2010-12-16', 'Female', '+639164941234', 'Information Technology', NULL, '2026-03-15 18:13:26', '2026-03-15 18:13:26'),
(8, 'mangkepweng@psu.edu.ph', '$2y$10$ARgKYzU/OPdbmRQ0kS0deOTwfu5fZ.V/cB3N4EUUUYHFvCSQSud4q', 'Dr. Mang Kepweng', NULL, 'counselor', NULL, NULL, NULL, 'Guidance Sanctuary', NULL, '2026-03-15 18:20:34', '2026-03-15 18:20:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_preassessments`
--
ALTER TABLE `ai_preassessments`
  ADD PRIMARY KEY (`pre_id`),
  ADD KEY `idx_student_created` (`student_id`,`created_at`);

--
-- Indexes for table `anonymous_notes`
--
ALTER TABLE `anonymous_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_anon_counselor` (`counselor_id`);

--
-- Indexes for table `anonymous_note_messages`
--
ALTER TABLE `anonymous_note_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_msg_note` (`note_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `idx_student_date` (`student_id`,`scheduled_at`),
  ADD KEY `idx_counselor_date` (`counselor_id`,`scheduled_at`);

--
-- Indexes for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `idx_user_date` (`user_id`,`assessment_date`);

--
-- Indexes for table `counselor_availability`
--
ALTER TABLE `counselor_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `idx_counselor_day` (`counselor_id`,`day_of_week`);

--
-- Indexes for table `counselor_notes`
--
ALTER TABLE `counselor_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `counselor_id` (`counselor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_student_date` (`student_id`,`logged_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user` (`user_id`);

--
-- Indexes for table `session_logs`
--
ALTER TABLE `session_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_responses`
--
ALTER TABLE `student_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `idx_user_assessment` (`user_id`,`assessment_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `roll_number` (`roll_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_preassessments`
--
ALTER TABLE `ai_preassessments`
  MODIFY `pre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `anonymous_notes`
--
ALTER TABLE `anonymous_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `anonymous_note_messages`
--
ALTER TABLE `anonymous_note_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `counselor_availability`
--
ALTER TABLE `counselor_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counselor_notes`
--
ALTER TABLE `counselor_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mood_logs`
--
ALTER TABLE `mood_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `session_logs`
--
ALTER TABLE `session_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `student_responses`
--
ALTER TABLE `student_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_preassessments`
--
ALTER TABLE `ai_preassessments`
  ADD CONSTRAINT `fk_pre_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `anonymous_notes`
--
ALTER TABLE `anonymous_notes`
  ADD CONSTRAINT `anonymous_notes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_anon_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `anonymous_note_messages`
--
ALTER TABLE `anonymous_note_messages`
  ADD CONSTRAINT `fk_msg_note` FOREIGN KEY (`note_id`) REFERENCES `anonymous_notes` (`note_id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `assessment_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `counselor_availability`
--
ALTER TABLE `counselor_availability`
  ADD CONSTRAINT `fk_avail_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `counselor_notes`
--
ALTER TABLE `counselor_notes`
  ADD CONSTRAINT `counselor_notes_ibfk_1` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `counselor_notes_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `mood_logs`
--
ALTER TABLE `mood_logs`
  ADD CONSTRAINT `mood_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `session_logs`
--
ALTER TABLE `session_logs`
  ADD CONSTRAINT `session_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_responses`
--
ALTER TABLE `student_responses`
  ADD CONSTRAINT `student_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_responses_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `assessment_questions` (`question_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
