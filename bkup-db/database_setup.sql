-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025 at 09:58 PM
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
-- Database: `schedule_gen_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` varchar(64) NOT NULL,
  `course_code` varchar(64) NOT NULL,
  `course_title_external` varchar(255) NOT NULL,
  KEY `idx_course_id` (`course_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_id`, `course_code`, `course_title_external`) VALUES
(1, '654a1249bf6363.17931859', 'BUDT-DTB', 'WSQ Digital Transformation (SF)'),
(2, '64da094910d6b7.97848880', 'ACDS', 'Advanced Certificate in Data Science (E-Learning)'),
(3, '64da1cfc7561a0.40723288', 'ACDM', 'Advanced Certificate in Digital Marketing (E-Learning)'),
(4, '64db72930331a1.79943308', 'ACCA', 'Advanced Certificate in Cloud Administration (E-Learning)'),
(5, '64ec60cc52e997.09548257', 'ACAI', 'Advanced Certificate in Artificial Intelligence (E-Learning)'),
(6, '64e4759a0cc286.67851682', 'ACCS', 'Advanced Certificate in Cyber Security (E-Learning)'),
(7, '64ed939b01be94.08889975', 'ACSD', 'Advanced Certificate in Software Development (E-Learning)'),
(8, '64edb5948290f8.77291477', 'ACSI', 'Advanced Certificate in Software Implementation (E-Learning)'),
(9, '65950697353507.39517493', 'PDCA(SCTP-PDSS)', '(SCTP) WSQ Diploma in Infocomm Technology (Support) (Synchronous and Asynchronous E-Learning)'),
(10, '6537b2601151e8.85630246', 'PDDM', '(SCTP) Professional Diploma in Digital Marketing (E-Learning)'),
(11, '6538a4be15bb53.26571463', 'PDDS', '(SCTP) WSQ Diploma in Infocomm Technology (Data) (Synchronous and Asynchronous E-Learning)'),
(12, '6538a61f18f7b3.59681588', 'PDWD', '(SCTP) Professional Diploma in Full Stack Web development (E-Learning)'),
(13, '6538d219d7cf66.31764105', 'ACPA', 'Advanced Certificate in Process Automation (E-Learning)'),
(14, '6538d56c0220c5.52202264', 'ACSS', 'Advanced Certificate in System Support (E-Learning)'),
(15, '6538daa2927a29.48889940', 'BUBA-BAN', 'NA'),
(16, '653a1fe28e76b8.22188413', 'CCP-ISA', 'NA'),
(17, '653a3554875357.42380954', 'CCP-FWD', 'NA'),
(18, '653a41e076e7f7.88750726', 'CCP-CLS', 'NA'),
(19, '653a4742031820.72638178', 'CCP-IPM', 'NA'),
(20, '653a49b383a429.84955854', 'CCP-ITS', 'NA'),
(21, '653b7c37e1bfc0.88493213', 'PMI', 'Agile Project Management (Implementation) (SF) (Synchronous and Asynchronous E-Learning)'),
(22, '653b85157a45f5.19063709', 'DIN', 'Digital Innovation (SF) (Synchronous and Asynchronous E-Learning)'),
(23, '653b86e7c5b5d2.78715718', 'SFDW-GEN', 'SkillsFuture Digital Workplace 2.0 (SFDW 2.0) - Generic (Synchronous and Asynchronous E-Learning)'),
(24, '65486205765d11.64563678', 'PFCC', 'NA'),
(25, '65486966134671.06141500', 'PFCS', 'NA'),
(26, '6548797bb8b2e1.10073069', 'PFSA', 'NA'),
(27, '65487d32c28941.50055886', 'PGDM', 'Postgraduate Diploma in Digital Marketing and Implementation (E-Learning)'),
(28, '6554458fe6d356.71897801', 'ACIS-EIT', '(SCTP) WSQ Advanced Certificate in Infocomm Technology (Infrastructure) (Synchronous and Asynchronous E-Learning)'),
(29, '6594ee0b0335a7.78587452', 'ENT-OCS', 'NA'),
(30, '6594f06d1db4d7.85431741', 'ENT-CHW', 'NA'),
(31, '6594ef2b644a81.16508916', 'ENT-DTN', 'NA'),
(32, '6596a7f5cff6f7.89675105', 'SFDW-HRT', 'Skills Future Digital Workplace 2.0 (SFDW 2.0) â€“ Generic (Synchronous and Asynchronous E-Learning)'),
(33, '6597f9f17f2418.41716862', 'HDDB', 'Higher Diploma in Digital Business (E-Learning)'),
(34, '659bd6089725f3.18462120', 'ECCP-ISM', 'NA'),
(35, '65b8d9e627a852.41139310', 'DGFC-CMV', 'WSQ Content Marketing and Video (SF) (Synchronous and Asynchronous E-Learning)'),
(36, '65b8e564c4c947.90302873', 'DGFC-BAN', 'WSQ Business Analytics (SF) (Synchronous and Asynchronous E-Learning)'),
(37, '65b946f6d828d4.24363533', 'DGFC-BAC', 'WSQ Capstone Project - Business Analytics (SF) (Synchronous and Asynchronous E-Learning)'),
(38, '65b9e00ed2c3e0.21271904', 'DGFC-CMC', 'WSQ Omni Commerce Campaign (SF) (Synchronous and Asynchronous E-Learning)'),
(39, '65b9fad1536957.02866169', 'DGFC-BDM', 'WSQ Digital Marketing (SF) (Synchronous and Asynchronous E-Learning)'),
(40, '65ba04cbd684a3.12616768', 'DGFC-DMI', 'WSQ Capstone Project - Digital Marketing (SF) (Synchronous and Asynchronous E-Learning)'),
(41, '65c0b9beaee4f9.84960602', 'CFED', 'NA'),
(42, '65eedca289e8d5.32346195', 'ENHR-DAH', 'NA'),
(43, '65eedfafde8734.07940326', 'GAI', 'WSQ Generative AI (SF)'),
(44, '65eee33f0cdf02.51574355', 'ENTP-GAC', 'WSQ Capstone Project - Data Analytics (SF)'),
(45, '65eef5527bf0a7.16604204', 'PDDT', 'Professional Diploma in Digital Transformation (E-Learning)'),
(46, '65f162321c05e6.36749857', 'PDHR', 'Professional Diploma in Digital Transformation (E-Learning)'),
(47, '660fa583058ec9.14290321', 'PDDA', 'Professional Diploma in Data Science and Artificial Intelligence (E-Learning)'),
(48, '6629f88f5f4270.28334044', 'PFSA-PYT', 'WSQ Programming Foundations (SF) (Synchronous and Asynchronous E-learning)'),
(49, '662a079b06fb12.00887837', 'PFSA-DVO', 'WSQ Develop Enterprise Applications (SF) (Synchronous and Asynchronous E-learning)'),
(50, '662a1a26334f78.70065591', 'PFCC-PYT', 'WSQ Programming Foundations (SF) (Synchronous and Asynchronous E-learning)'),
(51, '662a1f7f241269.57214279', 'PFCC-DVO', 'WSQ Develop Enterprise Applications (SF) (Synchronous and Asynchronous E-learning)'),
(52, '6634a976a35bc1.61393977', 'PCDA', 'Professional Certificate in Data Analytics and Artificial Intelligence'),
(53, '6634b5fde8e751.41361848', 'CFDA', 'Certificate in Data Analytics'),
(54, '6634bd0a1881f8.26617316', 'CFGI', 'Certificate in Generative AI'),
(55, '6639e592e5e2f9.54797438', 'INTL-PDAA', 'Professional Diploma in AI Application (E-Learning)'),
(56, '6639fae825ae89.65154982', 'PFSD', 'Professional Diploma in Full Stack Software Development'),
(57, '663b44de1ed2b9.21198088', 'PFSA1', 'Professional Diploma in System Administration'),
(58, '663b36ed58aa78.97344161', 'CFSS', 'Certificate in System Support'),
(59, '663b3d4de6dac5.73946199', 'PCSS', 'Professional Certificate in System Support'),
(60, '663b4a24a1c340.77031305', 'PFSA2', 'Professional Diploma in System Administration'),
(61, '663d9c582e59d7.13846955', 'PCCS', 'Professional Certificate in Cloud Systems Support'),
(62, '663da018728757.94652928', 'CFCS', 'Certificate in Cloud Support'),
(63, '663d30262e5073.77784451', 'PFCC1', 'Professional Diploma in Cloud Computing'),
(64, '663d977b07c2e0.01043259', 'PFCC2', 'Professional Diploma in Cloud Computing'),
(65, '663df635571724.11774701', 'PFCS1', 'Professional Diploma in Cyber Security'),
(66, '663dfd4fe3c533.43549799', 'PFCS2', 'Professional Diploma in Cyber Security'),
(67, '663e02680831a7.16384966', 'CFCY', 'Certificate in Cloud Security'),
(68, '663e0b6b0494f0.62850807', 'PCCY', 'Professional Certificate in Cloud Security'),
(69, '6640a7180523e9.21733903', 'CFDM', 'Certificate in Digital Marketing'),
(70, '6640aaec8a9a44.74122101', 'CFCM', 'Certificate in Content Marketing'),
(71, '6640b28079f769.18315402', 'CFAM', 'Certificate in Agile Project Management'),
(72, '6640bb46229d21.23853855', 'CFOS', 'Certificate in Omni Sales'),
(73, '6640c26be4cb80.39938457', 'INTL-PDDM', 'Professional Diploma in Digital Marketing (E-Learning)'),
(74, '6640d91d701845.01587157', 'PFDM', 'Professional Diploma in Digital Marketing'),
(75, '6640e6f6e97b74.15511823', 'PGDM1', 'Postgraduate Diploma in Digital Marketing and Implementation (E-Learning)'),
(76, '664150a78de4f6.69876548', 'PDDT1', 'Professional Diploma in Digital Transformation (E-Learning)'),
(77, '66414b9594b8b3.19685331', 'PCDT', 'Professional Certificate in Digital Transformation'),
(78, '66695dc45ec279.86646020', 'DGFC-GAI', 'WSQ Generative AI (SF) (Synchronous and Asynchronous E-Learning)'),
(79, '6670040464f6a9.81436609', 'HNDC', 'Pearson BTEC Level 5 Higher National Diploma in Computing (Application Development and Testing) (E-Learning)'),
(80, '6670145ce1c475.01299140', 'INTL-FDSE', 'Pearson BTEC International Level 3 Foundation Diploma in Information Technology (E-Learning)'),
(81, '6670fca82e1075.62409884', 'MTDM', 'Master in Digital Marketing (E-Learning)'),
(82, '6671102b5b1e90.39439636', 'MTCS', 'Master in Computer Science  (E-Learning)'),
(83, '669e44c45d3882.64486876', 'PDDI1', '(SCTP) Professional Diploma in Digital Innovation (Synchronous and Asynchronous E-Learning)'),
(84, '66a0c78f5465d6.16981532', 'DGFC-PMI', 'Agile Project Management (Implementation) (SF) (Synchronous and Asynchronous E-Learning)'),
(85, '66a0cf579cf988.64607854', 'DGFC-PMC', 'Digital Innovation (SF) (Synchronous and Asynchronous E-Learning)'),
(86, '66a0e7b5574838.98939623', 'DGFC-GAC', 'WSQ Capstone Project-Data Analytics (SF) (Synchronous and Asynchronous E-Learning)'),
(87, '66a0ec83951b78.68030782', 'DGFC-SNS', 'WSQ Solution Sales (SF) (Synchronous and Asynchronous E-Learning)'),
(88, '66a0f01f7b2a73.55577774', 'DGFC-SNC', 'WSQ Capstone Project-Solution Sales (SF) (Synchronous and Asynchronous E-Learning)'),
(89, '66a702b424fbd2.81077319', 'PFCY', 'Professional Diploma in Cloud Computing and Cyber Security'),
(90, '66ab1f6f9d8e55.03681929', 'ACAD1', 'SCTP-Advanced Certificate in AI Application Development (Synchronous and Asynchronous E-Learning)'),
(91, '66ab2d64adb709.92361834', 'ACFD', 'SCTP-Advanced Certificate in Full Stack Software Development (Synchronous and Asynchronous E-Learning)'),
(92, '66acb6a8327ae8.85003524', 'INTL-BTSE', 'Bachelor of Science (Honours) in Computer Science (top up)'),
(93, '66c5a8c0f0e550.08493660', 'MTDI-TopUp', 'Master of Business Administration - Digital Innovation and Entrepreneurship'),
(94, '66d6c3fca20ba5.61318579', 'BUND-DGI', 'NA'),
(95, '66e31619a1d505.58343716', 'ENTP-AIDM', 'NA'),
(96, '66f41407e05529.40681186', 'INSG-FDSE', 'Pearson BTEC International Level 3 Foundation Diploma in Information Technology'),
(97, '66f4fd50e7bd88.75649801', 'INSG-HDSE', 'Higher Diploma in Software Engineering'),
(98, '66f50862048825.77094572', 'INSG-BTSE', 'Bachelor of Science (Honours) in Computer Science (top up)'),
(99, '66fc085e2e7472.15108291', 'INTL-HDSE', 'Higher Diploma in Software Engineering (E-Learning)'),
(100, '66fc0f0fe7ed23.35218841', 'PCHR', 'Professional Certificate in Human Resource Technology (ELearning)'),
(101, '66fc1686a1ee83.50735326', 'MTHR-TopUp', 'Master of Business Administration - HR Technology'),
(102, '67162beb68f165.43104713', 'ENTP-AIDA', 'NA'),
(103, '671b5b1c780c38.49469806', 'ENTP-RPA', 'WSQ Robotic Process Automation (SF) (Synchronous and Asynchronous E-Learning)'),
(104, '671b64590f9ef3.11937635', 'ENTP-CIA', 'WSQ Capstone Project - Intelligent Apps (SF) (Synchronous and Asynchronous E-Learning)'),
(105, '671b6e87e94aa2.78145571', 'ENTP-AIPM', 'NA'),
(106, '67206d009657b9.12846847', 'ENTP-AIPA', 'NA'),
(107, '672092b6c61d23.05928023', 'ENTP-AIOC', 'NA'),
(108, '67331864018315.24347395', 'INSG-FDDB', 'Pearson BTEC International Level 3 Foundation Diploma in Business'),
(109, '67331f2c790fd8.23739077', 'INSG-HDDB', 'Higher Diploma in Digital Business'),
(110, '673325065ebb70.22881707', 'INSG-BTDM', 'Bachelor of Science (Honours) in Business Management and Marketing (Top Up)'),
(111, '674ee407989875.19946192', 'SG-HDCC', 'Higher Diploma in Cloud Computing and Cyber Security (E-Learning)'),
(112, '67be07c7237591.41888595', 'PDDS1', '(SCTP) WSQ Diploma in Infocomm Technology (Data) (Synchronous and Asynchronous E-Learning) (Full Time)(TA Eligible)'),
(113, '67be157c146872.08817958', 'PDDM1', '(SCTP) Professional Diploma in Digital Marketing (E-Learning) (Full Time) (TA Eligible)'),
(114, '67d3e9a9dc7a69.28093467', 'PDDI2', '(SCTP) Professional Diploma in Digital Innovation (Synchronous and Asynchronous E-Learning) (Full Time) (TA Eligible)'),
(115, '67d6572da45992.06830065', 'ACIS', '(SCTP) WSQ Advanced Certificate in Infocomm Technology (Infrastructure) (Synchronous and Asynchronous E-Learning) (Full Time) (TA Eligible)'),
(116, '67d65cda6eaae5.47714534', 'PDCA1', '(SCTP) WSQ Diploma in Infocomm Technology (Support) (Synchronous and Asynchronous E-Learning) (Full Time) (TA Eligible)'),
(117, '67d92fc5746e30.29302021', 'PDWD1', '(SCTP) Professional Diploma in Full Stack Web Development (Synchronous and Asynchronous E-Learning) (Full-Time) (TA-eligible)'),
(118, '67f4fb9c267977.72830375', 'ACCS-EC', 'Advanced Certificate in Cyber Security (E-Learning)'),
(119, '682dba7e6be170.36215552', 'BAN', 'WSQ Business Analytics (SF)'),
(120, '6830172798dfd4.98808851', 'RPA', 'WSQ Robotic Process Automation (SF)'),
(121, '68396fb98a7cb4.53699138', 'DGFC-ECF', 'WSQ Security Fundamentals (SF)'),
(122, '684801f9ecc9c2.64613058', 'DSSC-DAA', 'WSQ Data Analytics Application Development (SF)'),
(123, '68524b0b1c77c0.81196080', 'DSSC-MCC', 'WSQ Omni-Channel Marketing Campaign Creation (SF)'),
(124, '68524687c4f584.82771665', 'DSSC-ACD', 'WSQ Conversational AI and Chatbot Development (SF)'),
(125, '685253a5a2e042.81133387', 'DSSC-AIC', 'WSQ AI-Powered Digital Content Creation (SF)'),
(126, '6852559a9e2394.76067121', 'DSSC-RAD', 'WSQ Robotic Process Automation Application Development (SF)'),
(127, '6855279ef04038.32954551', 'HESG-HDSE', 'Higher Diploma in Software Engineering'),
(128, '6855377867d7c3.11285323', 'INTL-PGSE', 'Postgraduate Diploma in Software Engineering (E-Learning)'),
(129, '68904361504a30.05721332', 'INTL-PCDI', 'Professional Certificate in Digital Innovation (eLearning)'),
(130, '6891ea4f8b5748.48015637', 'INTL-PDDA', 'Professional Diploma in Data Science and Artificial Intelligence (E-Learning)'),
(131, '6892ae74953e50.66142304', 'PCDI1', 'Professional Certificate in Digital Innovation'),
(132, '68c90f2eb359c1.75317234', 'ACDM-ACM', 'WSQ AI-Led Conversational Marketing Campaign and Chatbot (SF)');

-- --------------------------------------------------------

--
-- Table structure for table `login_audit`
--

CREATE TABLE `login_audit` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_audit`
--

INSERT INTO `login_audit` (`id`, `user_id`, `ip`, `user_agent`, `logged_at`) VALUES
(1, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 19:05:20'),
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 19:06:01'),
(3, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 19:06:20'),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 19:08:05'),
(5, 1, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-09-15 19:29:45'),
(6, 1, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-09-15 19:39:58'),
(7, 1, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', '2025-09-15 19:40:03'),
(8, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:02:13'),
(9, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:11:26'),
(10, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-17 02:56:12'),
(11, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-17 06:58:57'),
(12, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:37:55'),
(13, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:41:00'),
(14, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:41:48'),
(15, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:42:36'),
(16, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:46:08'),
(17, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 15:17:29'),
(18, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 15:29:59'),
(19, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 15:34:17'),
(20, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 15:35:20'),
(21, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 08:20:19'),
(22, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 17:28:14'),
(23, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 02:26:22'),
(24, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 02:27:39'),
(25, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 02:28:41'),
(26, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 06:14:35'),
(27, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 03:18:13'),
(28, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 18:12:22'),
(29, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 02:57:08'),
(30, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:12:49'),
(31, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:23:28'),
(32, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 05:04:05'),
(33, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-06 06:03:16'),
(34, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 02:31:06'),
(35, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-04 05:21:14'),
(36, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-04 06:05:27'),
(37, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-04 06:06:34'),
(38, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-04 06:55:45'),
(39, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-04 06:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `public_holidays`
--

CREATE TABLE `public_holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country_code` char(2) NOT NULL,
  `hdate` date NOT NULL,
  `name` varchar(190) NOT NULL,
  `source` varchar(40) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `public_holidays`
--

INSERT INTO `public_holidays` (`id`, `country_code`, `hdate`, `name`, `source`, `created_at`, `updated_at`) VALUES
(1, 'SG', '2025-05-12', 'Vesak Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(2, 'SG', '2025-08-09', 'National Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(3, 'SG', '2025-12-25', 'Christmas Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(4, 'SG', '2025-04-18', 'Good Friday', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(5, 'SG', '2025-05-01', 'Labour Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(6, 'SG', '2025-01-29', 'Chinese New Year\'s Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(7, 'SG', '2025-10-20', 'Diwali/Deepavali', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(8, 'SG', '2025-01-30', 'Second Day of Chinese New Year', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(9, 'SG', '2025-01-01', 'New Year’s Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(10, 'SG', '2025-03-31', 'Hari Raya Puasa', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(11, 'SG', '2025-05-03', 'Election Day', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04'),
(12, 'SG', '2025-06-07', 'Hari Raya Haji', 'GOOGLE_ICS', '2025-09-17 03:46:04', '2025-09-17 03:46:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(2, 'Admin'),
(3, 'SuperAdmin'),
(1, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `oid` char(36) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `oid`, `email`, `display_name`, `created_at`, `last_login_at`, `last_login_ip`) VALUES
(1, '406a9508-f468-47b2-8281-fb48fe2c679b', 'hashan@educlaas.com', 'Hashan Rathnayake - Technology Associate', '2025-09-15 19:05:20', '2025-11-04 06:56:01', '::1'),
(2, '65c1a4dd-3ece-4a3e-92d1-7412a7c870d9', 'testpowerplatform@educlaas.com', 'Testing Power Platform', '2025-09-18 15:34:17', '2025-09-22 02:27:39', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_code` (`course_code`),
  ADD KEY `idx_course_code` (`course_code`);

--
-- Indexes for table `login_audit`
--
ALTER TABLE `login_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `public_holidays`
--
ALTER TABLE `public_holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_country_day` (`country_code`,`hdate`),
  ADD KEY `idx_range` (`hdate`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `oid` (`oid`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_ur_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `login_audit`
--
ALTER TABLE `login_audit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `public_holidays`
--
ALTER TABLE `public_holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_audit`
--
ALTER TABLE `login_audit`
  ADD CONSTRAINT `login_audit_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- --------------------------------------------------------
-- Table: templates
-- --------------------------------------------------------

CREATE TABLE `templates` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` varchar(64) NOT NULL,
  `learning_mode` ENUM('Full-Time','Part-Time') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user` INT(11) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_user` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_created_user` (`created_user`),
  KEY `idx_updated_user` (`updated_user`),
  CONSTRAINT `fk_templates_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_templates_created_user` FOREIGN KEY (`created_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_templates_updated_user` FOREIGN KEY (`updated_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: template_data
-- --------------------------------------------------------

CREATE TABLE `template_data` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` BIGINT(20) UNSIGNED NOT NULL,
  `session_id` INT(11) NOT NULL,
  `session_day` VARCHAR(50) DEFAULT NULL,
  `session_of_the_day` VARCHAR(50) DEFAULT NULL,
  `session_code` VARCHAR(100) DEFAULT NULL,
  `session_mode` VARCHAR(50) DEFAULT NULL,
  `topics` TEXT,
  `session_day_of_module` VARCHAR(50) DEFAULT NULL,
  `hours` DECIMAL(4,2) DEFAULT NULL,
  `session_type` VARCHAR(100) DEFAULT NULL,
  `faculty` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template_id` (`template_id`),
  CONSTRAINT `fk_template_data_template` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: schedule
-- --------------------------------------------------------

CREATE TABLE `schedule` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `cohort_code` VARCHAR(100) NOT NULL,
  `learning_mode` ENUM('Full-Time','Part-Time') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user` INT(11) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_user` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_created_user` (`created_user`),
  KEY `idx_updated_user` (`updated_user`),
  CONSTRAINT `fk_schedule_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_created_user` FOREIGN KEY (`created_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_updated_user` FOREIGN KEY (`updated_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: schedule_data
-- --------------------------------------------------------

CREATE TABLE `schedule_data` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_id` BIGINT(20) UNSIGNED NOT NULL,
  `schedule_json` JSON NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user` INT(11) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_user` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_schedule_id` (`schedule_id`),
  KEY `idx_created_user` (`created_user`),
  KEY `idx_updated_user` (`updated_user`),
  CONSTRAINT `fk_schedule_data_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_data_created_user` FOREIGN KEY (`created_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_data_updated_user` FOREIGN KEY (`updated_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
