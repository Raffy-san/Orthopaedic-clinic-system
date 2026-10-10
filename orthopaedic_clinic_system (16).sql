-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 10, 2026 at 01:56 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `orthopaedic_clinic_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `DoctorID` int UNSIGNED NOT NULL,
  `AppointmentDate` date NOT NULL,
  `AppointmentTime` time NOT NULL,
  `meridiem` enum('AM','PM') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ChiefComplaint` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `Remarks` text COLLATE utf8mb4_unicode_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `AppointmentDate`, `AppointmentTime`, `meridiem`, `Purpose`, `ChiefComplaint`, `Status`, `Remarks`, `CreatedAt`) VALUES
(74, 37, 39, '2026-10-05', '14:00:00', 'PM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-05 14:43:49'),
(75, 34, 39, '2026-10-06', '16:30:00', 'PM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-05 14:43:58'),
(76, 33, 39, '2026-10-08', '09:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Cancelled', NULL, '2026-10-07 16:00:20'),
(77, 32, 39, '2026-10-09', '11:00:00', 'AM', 'Consultation', 'Right Knee pain', 'Completed', NULL, '2026-10-08 13:43:24'),
(78, 33, 39, '2026-10-09', '11:30:00', 'AM', 'Consultation', 'Left Knee pain', 'Cancelled', NULL, '2026-10-09 04:06:52'),
(79, 37, 39, '2026-10-13', '11:30:00', 'AM', 'Consultation', 'Left Knee pain', 'Confirmed', NULL, '2026-10-09 07:28:43'),
(80, 32, 39, '2026-10-10', '09:00:00', 'AM', 'Consultation', 'Right Knee pain', 'Completed', NULL, '2026-10-10 01:19:37'),
(81, 34, 39, '2026-10-10', '11:30:00', 'AM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-10 11:02:16'),
(82, 34, 1, '2026-10-16', '10:00:00', 'AM', 'Follow-up check-up', 'Follow-up check-up', 'Confirmed', 'Check healing Process', '2026-10-10 13:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `LogID` int UNSIGNED NOT NULL,
  `UserID` int UNSIGNED DEFAULT NULL,
  `Action` enum('CREATE','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TableAffected` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `RecordID` int UNSIGNED NOT NULL,
  `FieldChanged` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `OldValue` text COLLATE utf8mb4_unicode_ci,
  `NewValue` text COLLATE utf8mb4_unicode_ci,
  `LogDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auditlogs`
--

INSERT INTO `auditlogs` (`LogID`, `UserID`, `Action`, `TableAffected`, `RecordID`, `FieldChanged`, `OldValue`, `NewValue`, `LogDate`, `IPAddress`) VALUES
(96, 1, 'CREATE', 'prescriptions', 33, NULL, NULL, '{\"medicine\":\"Celecoxib\",\"dosage\":\"200mg\",\"frequency\":\"1x daily\",\"duration\":\"7 days\",\"quantity\":7,\"instructions\":\"Take with food.\"}', '2026-10-04 16:45:11', '::1'),
(97, 1, 'CREATE', 'billing', 33, NULL, NULL, '{\"ConsultationID\":41,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-04 16:45:11', '::1'),
(98, 1, 'UPDATE', 'appointments', 60, 'Status', 'Confirmed', 'Completed', '2026-10-04 16:45:11', '::1'),
(99, 1, 'UPDATE', 'billing', 33, 'DiscountType', 'None', 'PWD', '2026-10-04 17:01:52', '::1'),
(100, 1, 'CREATE', 'payments', 36, NULL, NULL, '{\"BillingID\":33,\"AmountPaid\":400,\"ReceivedBy\":1}', '2026-10-04 17:01:52', '::1'),
(101, 1, 'UPDATE', 'billing', 33, 'Status', 'Unpaid', 'Partially Paid', '2026-10-04 17:01:52', '::1'),
(102, 39, 'CREATE', 'appointments', 61, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"16:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 20:12:38', '::1'),
(103, 1, 'UPDATE', 'consultations', 42, 'Diagnosis', NULL, 'Right Knee Pain, Inflammation', '2026-10-04 20:13:20', '::1'),
(104, 1, 'UPDATE', 'consultations', 42, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 20:13:20', '::1'),
(105, 1, 'CREATE', 'prescriptions', 34, NULL, NULL, '{\"medicine\":\"Celecoxib\",\"dosage\":\"200mg\",\"frequency\":\"1x daily\",\"duration\":\"7 days\",\"quantity\":7,\"instructions\":\"Take with food.\"}', '2026-10-04 20:13:20', '::1'),
(106, 1, 'CREATE', 'prescriptions', 35, NULL, NULL, '{\"medicine\":\"Methylcobalamin\",\"dosage\":\"500mcg\",\"frequency\":\"1x daily\",\"duration\":\"30 days\",\"quantity\":30,\"instructions\":\"\"}', '2026-10-04 20:13:20', '::1'),
(107, 1, 'CREATE', 'prescriptions', 36, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-04 20:13:20', '::1'),
(108, 1, 'CREATE', 'billing', 34, NULL, NULL, '{\"ConsultationID\":42,\"PatientID\":34,\"OriginalAmount\":500}', '2026-10-04 20:13:20', '::1'),
(109, 1, 'UPDATE', 'appointments', 61, 'Status', 'Confirmed', 'Completed', '2026-10-04 20:13:20', '::1'),
(110, 39, 'CREATE', 'appointments', 62, NULL, NULL, '{\"PatientID\":33,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"16:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 20:14:35', '::1'),
(111, 1, 'UPDATE', 'consultations', 43, 'Diagnosis', NULL, 'Left Knee pain', '2026-10-04 20:14:55', '::1'),
(112, 1, 'UPDATE', 'consultations', 43, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 20:14:55', '::1'),
(113, 1, 'CREATE', 'prescriptions', 37, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-04 20:14:55', '::1'),
(114, 1, 'CREATE', 'billing', 35, NULL, NULL, '{\"ConsultationID\":43,\"PatientID\":33,\"OriginalAmount\":500}', '2026-10-04 20:14:55', '::1'),
(115, 1, 'UPDATE', 'appointments', 62, 'Status', 'Confirmed', 'Completed', '2026-10-04 20:14:55', '::1'),
(116, 39, 'CREATE', 'appointments', 63, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"15:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 20:36:10', '::1'),
(117, 1, 'UPDATE', 'consultations', 44, 'Diagnosis', NULL, 'Left Knee pain', '2026-10-04 20:56:09', '::1'),
(118, 1, 'UPDATE', 'consultations', 44, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 20:56:09', '::1'),
(119, 1, 'CREATE', 'prescriptions', 38, NULL, NULL, '{\"medicine\":\"Naproxen\",\"dosage\":\"500mg\",\"frequency\":\"2x daily\",\"duration\":\"7 days\",\"quantity\":14,\"instructions\":\"Take with food.\"}', '2026-10-04 20:56:09', '::1'),
(120, 1, 'CREATE', 'billing', 36, NULL, NULL, '{\"ConsultationID\":44,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-04 20:56:09', '::1'),
(121, 1, 'UPDATE', 'appointments', 63, 'Status', 'Confirmed', 'Completed', '2026-10-04 20:56:09', '::1'),
(122, 39, 'CREATE', 'appointments', 64, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 23:14:12', '::1'),
(123, 1, 'UPDATE', 'consultations', 45, 'Diagnosis', NULL, 'test', '2026-10-04 23:14:37', '::1'),
(124, 1, 'UPDATE', 'consultations', 45, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 23:14:37', '::1'),
(125, 1, 'CREATE', 'prescriptions', 39, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-04 23:14:37', '::1'),
(126, 1, 'CREATE', 'billing', 37, NULL, NULL, '{\"ConsultationID\":45,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-04 23:14:37', '::1'),
(127, 1, 'UPDATE', 'appointments', 64, 'Status', 'Confirmed', 'Completed', '2026-10-04 23:14:37', '::1'),
(128, 39, 'CREATE', 'appointments', 65, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 23:23:00', '::1'),
(129, 1, 'UPDATE', 'consultations', 46, 'Diagnosis', NULL, 'test', '2026-10-04 23:23:22', '::1'),
(130, 1, 'UPDATE', 'consultations', 46, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 23:23:22', '::1'),
(131, 1, 'CREATE', 'prescriptions', 40, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-04 23:23:22', '::1'),
(132, 1, 'CREATE', 'billing', 38, NULL, NULL, '{\"ConsultationID\":46,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-04 23:23:22', '::1'),
(133, 1, 'UPDATE', 'appointments', 65, 'Status', 'Confirmed', 'Completed', '2026-10-04 23:23:22', '::1'),
(134, 39, 'CREATE', 'appointments', 66, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 23:42:01', '::1'),
(135, 1, 'UPDATE', 'consultations', 47, 'Diagnosis', NULL, 'test', '2026-10-04 23:42:22', '::1'),
(136, 1, 'UPDATE', 'consultations', 47, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-04 23:42:22', '::1'),
(137, 1, 'CREATE', 'prescriptions', 41, NULL, NULL, '{\"medicine\":\"Celecoxib\",\"dosage\":\"200mg\",\"frequency\":\"1x daily\",\"duration\":\"7 days\",\"quantity\":7,\"instructions\":\"Take with food.\"}', '2026-10-04 23:42:22', '::1'),
(138, 1, 'CREATE', 'billing', 39, NULL, NULL, '{\"ConsultationID\":47,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-04 23:42:22', '::1'),
(139, 1, 'UPDATE', 'appointments', 66, 'Status', 'Confirmed', 'Completed', '2026-10-04 23:42:22', '::1'),
(140, 39, 'CREATE', 'appointments', 67, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"14:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-04 23:44:03', '::1'),
(141, 1, 'UPDATE', 'consultations', 48, 'Diagnosis', NULL, 'test', '2026-10-04 23:44:18', '::1'),
(142, 1, 'UPDATE', 'consultations', 48, 'Treatment', NULL, 'test', '2026-10-04 23:44:18', '::1'),
(143, 1, 'CREATE', 'prescriptions', 42, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-04 23:44:18', '::1'),
(144, 1, 'CREATE', 'billing', 40, NULL, NULL, '{\"ConsultationID\":48,\"PatientID\":34,\"OriginalAmount\":500}', '2026-10-04 23:44:18', '::1'),
(145, 1, 'UPDATE', 'appointments', 67, 'Status', 'Confirmed', 'Completed', '2026-10-04 23:44:18', '::1'),
(146, 1, 'CREATE', 'medical_certificates', 1, NULL, NULL, '{\"PatientID\":34,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-05 20:27:58', '::1'),
(147, 1, 'UPDATE', 'billing', 39, 'DiscountType', 'None', 'PWD', '2026-10-05 22:08:57', '::1'),
(148, 1, 'UPDATE', 'billing', 39, 'DiscountPercent', '0.00', '20.00', '2026-10-05 22:08:57', '::1'),
(149, 1, 'UPDATE', 'billing', 39, 'DiscountAmount', '0.00', '100.00', '2026-10-05 22:08:57', '::1'),
(150, 1, 'UPDATE', 'billing', 39, 'FinalAmount', '500.00', '400.00', '2026-10-05 22:08:57', '::1'),
(151, 1, 'CREATE', 'payments', 1, NULL, NULL, '{\"BillingID\":39,\"AmountPaid\":399.98,\"ReceivedBy\":1}', '2026-10-05 22:08:57', '::1'),
(152, 1, 'UPDATE', 'billing', 39, 'Status', 'Unpaid', 'Partially Paid', '2026-10-05 22:08:57', '::1'),
(153, 1, 'CREATE', 'payments', 2, NULL, NULL, '{\"BillingID\":39,\"AmountPaid\":0.02,\"ReceivedBy\":1}', '2026-10-05 22:09:04', '::1'),
(154, 1, 'UPDATE', 'billing', 39, 'Status', 'Partially Paid', 'Paid', '2026-10-05 22:09:04', '::1'),
(155, 1, 'CREATE', 'payments', 3, NULL, NULL, '{\"BillingID\":40,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-05 22:09:14', '::1'),
(156, 1, 'UPDATE', 'billing', 40, 'Status', 'Unpaid', 'Paid', '2026-10-05 22:09:14', '::1'),
(157, 39, 'CREATE', 'appointments', 68, NULL, NULL, '{\"PatientID\":33,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:09:55', '::1'),
(158, 1, 'UPDATE', 'consultations', 49, 'Diagnosis', NULL, 'test', '2026-10-05 22:12:02', '::1'),
(159, 1, 'UPDATE', 'consultations', 49, 'Treatment', NULL, 'test', '2026-10-05 22:12:02', '::1'),
(160, 1, 'CREATE', 'prescriptions', 43, NULL, NULL, '{\"medicine\":\"Naproxen\",\"dosage\":\"500mg\",\"frequency\":\"2x daily\",\"duration\":\"7 days\",\"quantity\":14,\"instructions\":\"Take with food.\"}', '2026-10-05 22:12:02', '::1'),
(161, 1, 'CREATE', 'billing', 41, NULL, NULL, '{\"ConsultationID\":49,\"PatientID\":33,\"OriginalAmount\":500}', '2026-10-05 22:12:02', '::1'),
(162, 1, 'UPDATE', 'appointments', 68, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:12:02', '::1'),
(163, 1, 'CREATE', 'medical_certificates', 2, NULL, NULL, '{\"PatientID\":33,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-05 22:14:12', '::1'),
(164, 39, 'CREATE', 'appointments', 69, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:25:23', '::1'),
(165, 1, 'UPDATE', 'consultations', 50, 'Diagnosis', NULL, 'test', '2026-10-05 22:25:42', '::1'),
(166, 1, 'UPDATE', 'consultations', 50, 'Treatment', NULL, 'test', '2026-10-05 22:25:42', '::1'),
(167, 1, 'CREATE', 'prescriptions', 44, NULL, NULL, '{\"medicine\":\"Calcium + Vitamin D3\",\"dosage\":\"600mg\\/400IU\",\"frequency\":\"1x daily\",\"duration\":\"30 days\",\"quantity\":30,\"instructions\":\"\"}', '2026-10-05 22:25:42', '::1'),
(168, 1, 'CREATE', 'billing', 42, NULL, NULL, '{\"ConsultationID\":50,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-05 22:25:42', '::1'),
(169, 1, 'UPDATE', 'appointments', 69, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:25:42', '::1'),
(170, 39, 'CREATE', 'appointments', 70, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:27:22', '::1'),
(171, 1, 'UPDATE', 'consultations', 51, 'Diagnosis', NULL, 'test', '2026-10-05 22:27:37', '::1'),
(172, 1, 'UPDATE', 'consultations', 51, 'Treatment', NULL, 'test', '2026-10-05 22:27:37', '::1'),
(173, 1, 'CREATE', 'prescriptions', 45, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-05 22:27:37', '::1'),
(174, 1, 'CREATE', 'billing', 43, NULL, NULL, '{\"ConsultationID\":51,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-05 22:27:37', '::1'),
(175, 1, 'UPDATE', 'appointments', 70, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:27:37', '::1'),
(176, 39, 'CREATE', 'appointments', 71, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:32:43', '::1'),
(177, 39, 'CREATE', 'appointments', 72, NULL, NULL, '{\"PatientID\":33,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"15:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:32:52', '::1'),
(178, 1, 'UPDATE', 'consultations', 52, 'Diagnosis', NULL, 'test', '2026-10-05 22:33:17', '::1'),
(179, 1, 'UPDATE', 'consultations', 52, 'Treatment', NULL, 'test', '2026-10-05 22:33:17', '::1'),
(180, 1, 'CREATE', 'prescriptions', 46, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-05 22:33:17', '::1'),
(181, 1, 'CREATE', 'billing', 44, NULL, NULL, '{\"ConsultationID\":52,\"PatientID\":34,\"OriginalAmount\":500}', '2026-10-05 22:33:17', '::1'),
(182, 1, 'UPDATE', 'appointments', 71, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:33:17', '::1'),
(183, 1, 'UPDATE', 'consultations', 53, 'Diagnosis', NULL, 'test', '2026-10-05 22:36:10', '::1'),
(184, 1, 'UPDATE', 'consultations', 53, 'Treatment', NULL, 'test', '2026-10-05 22:36:10', '::1'),
(185, 1, 'CREATE', 'prescriptions', 47, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-05 22:36:10', '::1'),
(186, 1, 'CREATE', 'billing', 45, NULL, NULL, '{\"ConsultationID\":53,\"PatientID\":33,\"OriginalAmount\":500}', '2026-10-05 22:36:10', '::1'),
(187, 1, 'UPDATE', 'appointments', 72, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:36:10', '::1'),
(188, 39, 'CREATE', 'appointments', 73, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"15:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:39:58', '::1'),
(189, 1, 'UPDATE', 'consultations', 54, 'Diagnosis', NULL, 'test', '2026-10-05 22:40:35', '::1'),
(190, 1, 'UPDATE', 'consultations', 54, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-05 22:40:35', '::1'),
(191, 1, 'CREATE', 'prescriptions', 48, NULL, NULL, '{\"medicine\":\"Naproxen\",\"dosage\":\"500mg\",\"frequency\":\"2x daily\",\"duration\":\"7 days\",\"quantity\":14,\"instructions\":\"Take with food.\"}', '2026-10-05 22:40:35', '::1'),
(192, 1, 'CREATE', 'billing', 46, NULL, NULL, '{\"ConsultationID\":54,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-05 22:40:35', '::1'),
(193, 1, 'UPDATE', 'appointments', 73, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:40:35', '::1'),
(194, 39, 'CREATE', 'appointments', 74, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:43:49', '::1'),
(195, 39, 'CREATE', 'appointments', 75, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-05\",\"AppointmentTime\":\"14:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-05 22:43:58', '::1'),
(196, 1, 'UPDATE', 'consultations', 55, 'Diagnosis', NULL, 'test', '2026-10-05 22:44:14', '::1'),
(197, 1, 'UPDATE', 'consultations', 55, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-05 22:44:14', '::1'),
(198, 1, 'CREATE', 'prescriptions', 49, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"5 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-05 22:44:14', '::1'),
(199, 1, 'CREATE', 'billing', 47, NULL, NULL, '{\"ConsultationID\":55,\"PatientID\":37,\"OriginalAmount\":500}', '2026-10-05 22:44:14', '::1'),
(200, 1, 'UPDATE', 'appointments', 74, 'Status', 'Confirmed', 'Completed', '2026-10-05 22:44:14', '::1'),
(201, 39, 'CREATE', 'medical_certificates', 3, NULL, NULL, '{\"PatientID\":37,\"Diagnosis\":\"test\",\"IssuedBy\":39}', '2026-10-05 23:11:51', '::1'),
(202, 1, 'UPDATE', 'Medicines', 1, 'IsActive', '1', '0', '2026-10-06 20:29:38', '::1'),
(203, 1, 'UPDATE', 'Medicines', 1, 'IsActive', '0', '1', '2026-10-06 20:29:38', '::1'),
(204, 1, 'CREATE', 'Medicines', 2, NULL, NULL, '{\"Name\":\"Naproxen\",\"DefaultDosage\":\"200mg\"}', '2026-10-06 20:29:45', '::1'),
(205, NULL, 'UPDATE', 'appointments', 75, 'AppointmentDate/AppointmentTime', '2026-10-05 14:30:00 PM', '2026-10-06 16:30:00 PM', '2026-10-06 20:44:20', '::1'),
(206, 1, 'UPDATE', 'consultations', 56, 'Diagnosis', NULL, 'test', '2026-10-06 20:44:58', '::1'),
(207, 1, 'UPDATE', 'consultations', 56, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-06 20:44:58', '::1'),
(208, 1, 'CREATE', 'prescriptions', 50, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"2x daily\",\"duration\":\"5 days\",\"quantity\":7,\"instructions\":\"Take with food.\"}', '2026-10-06 20:44:58', '::1'),
(209, 1, 'CREATE', 'billing', 48, NULL, NULL, '{\"ConsultationID\":56,\"PatientID\":34,\"OriginalAmount\":500}', '2026-10-06 20:44:59', '::1'),
(210, 1, 'UPDATE', 'appointments', 75, 'Status', 'Confirmed', 'Completed', '2026-10-06 20:44:59', '::1'),
(211, 1, 'CREATE', 'payments', 4, NULL, NULL, '{\"BillingID\":48,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-06 22:29:23', '::1'),
(212, 1, 'UPDATE', 'billing', 48, 'Status', 'Unpaid', 'Paid', '2026-10-06 22:29:23', '::1'),
(213, 1, 'UPDATE', 'billing', 47, 'DiscountType', 'None', 'PWD', '2026-10-06 22:29:30', '::1'),
(214, 1, 'UPDATE', 'billing', 47, 'DiscountPercent', '0.00', '20.00', '2026-10-06 22:29:30', '::1'),
(215, 1, 'UPDATE', 'billing', 47, 'DiscountAmount', '0.00', '100.00', '2026-10-06 22:29:30', '::1'),
(216, 1, 'UPDATE', 'billing', 47, 'FinalAmount', '500.00', '400.00', '2026-10-06 22:29:30', '::1'),
(217, 1, 'CREATE', 'payments', 5, NULL, NULL, '{\"BillingID\":47,\"AmountPaid\":400,\"ReceivedBy\":1}', '2026-10-06 22:29:30', '::1'),
(218, 1, 'UPDATE', 'billing', 47, 'Status', 'Unpaid', 'Paid', '2026-10-06 22:29:30', '::1'),
(219, 1, 'CREATE', 'doctor_unavailability', 1, NULL, NULL, '{\"UnavailableDate\":\"2026-10-07\",\"Reason\":\"Sick Leave\"}', '2026-10-07 21:45:56', '::1'),
(220, 1, 'DELETE', 'doctor_unavailability', 1, NULL, '{\"UnavailableDate\":\"2026-10-07\",\"Reason\":\"Sick Leave\"}', NULL, '2026-10-07 22:13:07', '::1'),
(221, 1, 'CREATE', 'doctor_unavailability', 2, NULL, NULL, '{\"UnavailableDate\":\"2026-10-08\",\"Reason\":\"Sick Leave\"}', '2026-10-07 22:13:16', '::1'),
(222, 1, 'DELETE', 'doctor_unavailability', 2, NULL, '{\"UnavailableDate\":\"2026-10-08\",\"Reason\":\"Sick Leave\"}', NULL, '2026-10-07 23:59:43', '::1'),
(223, NULL, 'CREATE', 'appointments', 76, NULL, NULL, '{\"PatientID\":33,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-08\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-08 00:00:20', '::1'),
(224, 1, 'CREATE', 'doctor_unavailability', 3, NULL, NULL, '{\"UnavailableDate\":\"2026-10-12\",\"Reason\":\"Sick Leave\"}', '2026-10-08 21:06:31', '::1'),
(225, NULL, 'CREATE', 'appointments', 77, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-09\",\"AppointmentTime\":\"10:30:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-08 21:43:24', '::1'),
(226, NULL, 'UPDATE', 'appointments', 77, 'AppointmentDate/AppointmentTime', '2026-10-09 10:30:00 AM', '2026-10-09 11:00:00 AM', '2026-10-08 21:44:22', '::1'),
(227, 1, 'UPDATE', 'consultations', 58, 'Diagnosis', NULL, 'test', '2026-10-09 11:32:06', '::1'),
(228, 1, 'UPDATE', 'consultations', 58, 'Treatment', NULL, 'test', '2026-10-09 11:32:06', '::1'),
(229, 1, 'CREATE', 'prescriptions', 51, NULL, NULL, '{\"medicine\":\"Naproxen\",\"dosage\":\"200mg\",\"frequency\":\"3x daily\",\"duration\":\"7 days\",\"quantity\":10,\"instructions\":\"Take with food.\"}', '2026-10-09 11:32:06', '::1'),
(230, 1, 'CREATE', 'billing', 49, NULL, NULL, '{\"ConsultationID\":58,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-09 11:32:06', '::1'),
(231, 1, 'UPDATE', 'appointments', 77, 'Status', 'Confirmed', 'Completed', '2026-10-09 11:32:06', '::1'),
(232, NULL, 'CREATE', 'appointments', 78, NULL, NULL, '{\"PatientID\":33,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-09\",\"AppointmentTime\":\"11:30:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-09 12:06:52', '::1'),
(233, NULL, 'CREATE', 'appointments', 79, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-10\",\"AppointmentTime\":\"09:30:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-09 15:28:43', '::1'),
(234, NULL, 'UPDATE', 'appointments', 79, 'AppointmentDate/AppointmentTime', '2026-10-10 09:30:00 AM', '2026-10-13 11:30:00 AM', '2026-10-09 15:29:47', '::1'),
(235, 1, 'CREATE', 'payments', 6, NULL, NULL, '{\"BillingID\":50,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-10 09:17:59', '::1'),
(236, 1, 'UPDATE', 'billing', 50, 'Status', 'Unpaid', 'Paid', '2026-10-10 09:17:59', '::1'),
(237, 1, 'CREATE', 'payments', 7, NULL, NULL, '{\"BillingID\":49,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-10 09:18:05', '::1'),
(238, 1, 'UPDATE', 'billing', 49, 'Status', 'Unpaid', 'Paid', '2026-10-10 09:18:05', '::1'),
(239, NULL, 'CREATE', 'appointments', 80, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-10\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-10 09:19:37', '::1'),
(240, 1, 'UPDATE', 'consultations', 60, 'Diagnosis', NULL, 'test', '2026-10-10 18:58:43', '::1'),
(241, 1, 'UPDATE', 'consultations', 60, 'Treatment', NULL, 'test', '2026-10-10 18:58:43', '::1'),
(242, 1, 'CREATE', 'prescriptions', 52, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"3x daily\",\"duration\":\"\",\"quantity\":14,\"instructions\":\"Take on an empty stomach.\"}', '2026-10-10 18:58:43', '::1'),
(243, 1, 'CREATE', 'billing', 51, NULL, NULL, '{\"ConsultationID\":60,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-10 18:58:43', '::1'),
(244, 1, 'UPDATE', 'appointments', 80, 'Status', 'Confirmed', 'Completed', '2026-10-10 18:58:43', '::1'),
(245, 1, 'CREATE', 'followups', 10, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":1,\"AppointmentID\":80,\"FollowUpDate\":\"2026-10-17\"}', '2026-10-10 18:58:43', '::1'),
(246, NULL, 'CREATE', 'appointments', 81, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-13\",\"AppointmentTime\":\"10:30:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-10 19:02:16', '::1'),
(247, NULL, 'UPDATE', 'appointments', 81, 'AppointmentDate/AppointmentTime', '2026-10-13 10:30:00 AM', '2026-10-10 11:30:00 AM', '2026-10-10 21:03:55', '::1'),
(248, 1, 'UPDATE', 'consultations', 61, 'Diagnosis', NULL, 'test', '2026-10-10 21:05:58', '::1'),
(249, 1, 'UPDATE', 'consultations', 61, 'Treatment', NULL, 'test', '2026-10-10 21:05:58', '::1'),
(250, 1, 'CREATE', 'prescriptions', 53, NULL, NULL, '{\"medicine\":\"Ibuprofen\",\"dosage\":\"400mg\",\"frequency\":\"2x daily\",\"duration\":\"7 days\",\"quantity\":7,\"instructions\":\"Take with food.\"}', '2026-10-10 21:05:58', '::1'),
(251, 1, 'CREATE', 'billing', 52, NULL, NULL, '{\"ConsultationID\":61,\"PatientID\":34,\"OriginalAmount\":500}', '2026-10-10 21:05:58', '::1'),
(252, 1, 'UPDATE', 'appointments', 81, 'Status', 'Confirmed', 'Completed', '2026-10-10 21:05:58', '::1'),
(253, 1, 'CREATE', 'appointments', 82, NULL, NULL, '{\"PatientID\":34,\"AppointmentDate\":\"2026-10-15\",\"AppointmentTime\":\"09:00:00\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-10 21:05:58', '::1'),
(254, 1, 'CREATE', 'followups', 11, NULL, NULL, '{\"PatientID\":34,\"DoctorID\":1,\"AppointmentID\":82,\"FollowUpDate\":\"2026-10-15\"}', '2026-10-10 21:05:58', '::1'),
(255, 1, 'CREATE', 'payments', 8, NULL, NULL, '{\"BillingID\":52,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-10 21:07:00', '::1'),
(256, 1, 'UPDATE', 'billing', 52, 'Status', 'Unpaid', 'Paid', '2026-10-10 21:07:00', '::1'),
(257, NULL, 'UPDATE', 'appointments', 82, 'AppointmentDate/AppointmentTime', '2026-10-15 09:00:00 AM', '2026-10-16 10:00:00 AM', '2026-10-10 21:17:18', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `BillingID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `OriginalAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `DiscountType` enum('None','Senior Citizen','PWD') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
  `DiscountPercent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `DiscountAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `FinalAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillingDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Status` enum('Unpaid','Partially Paid','Paid','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`BillingID`, `ConsultationID`, `PatientID`, `OriginalAmount`, `DiscountType`, `DiscountPercent`, `DiscountAmount`, `FinalAmount`, `BillingDate`, `Status`) VALUES
(47, 55, 37, 500.00, 'PWD', 20.00, 100.00, 400.00, '2026-10-05 22:44:14', 'Paid'),
(48, 56, 34, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-06 20:44:58', 'Paid'),
(49, 58, 32, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-09 11:32:06', 'Paid'),
(50, 59, 33, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-10 09:17:58', 'Paid'),
(51, 60, 32, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-10 18:58:43', 'Unpaid'),
(52, 61, 34, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-10 21:05:58', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `clinic_schedule`
--

CREATE TABLE `clinic_schedule` (
  `ScheduleID` int UNSIGNED NOT NULL,
  `DayOfWeek` tinyint UNSIGNED NOT NULL,
  `IsOpen` tinyint(1) NOT NULL DEFAULT '1',
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clinic_schedule`
--

INSERT INTO `clinic_schedule` (`ScheduleID`, `DayOfWeek`, `IsOpen`, `StartTime`, `EndTime`) VALUES
(1, 1, 1, '09:00:00', '12:00:00'),
(2, 2, 1, '09:00:00', '12:00:00'),
(3, 3, 1, '09:00:00', '12:00:00'),
(4, 4, 1, '09:00:00', '12:00:00'),
(5, 5, 1, '09:00:00', '12:00:00'),
(6, 6, 1, '09:00:00', '12:00:00'),
(7, 7, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `ConsultationID` int UNSIGNED NOT NULL,
  `AppointmentID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `DoctorID` int UNSIGNED NOT NULL,
  `Diagnosis` text COLLATE utf8mb4_unicode_ci,
  `Treatment` text COLLATE utf8mb4_unicode_ci,
  `Notes` text COLLATE utf8mb4_unicode_ci,
  `ConsultationFee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `StartTime` time DEFAULT NULL,
  `Meridiem` enum('AM','PM') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `ConsultationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsCompleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`ConsultationID`, `AppointmentID`, `PatientID`, `DoctorID`, `Diagnosis`, `Treatment`, `Notes`, `ConsultationFee`, `StartTime`, `Meridiem`, `EndTime`, `ConsultationDate`, `IsCompleted`) VALUES
(55, 74, 37, 1, 'test', 'Anti-inflamatory meds', '', 500.00, '22:44:06', 'PM', '22:44:14', '2026-10-05 22:44:06', 1),
(56, 75, 34, 1, 'test', 'Anti-inflamatory meds', '', 500.00, '20:44:27', 'PM', '20:44:58', '2026-10-06 20:44:27', 1),
(57, 76, 33, 1, NULL, NULL, NULL, 500.00, '20:25:44', 'PM', NULL, '2026-10-08 20:25:44', 0),
(58, 77, 32, 1, 'test', 'test', '', 500.00, '11:31:52', 'AM', '11:32:06', '2026-10-09 11:31:52', 1),
(59, 78, 33, 1, NULL, NULL, NULL, 500.00, '12:07:05', 'PM', NULL, '2026-10-09 12:07:05', 0),
(60, 80, 32, 1, 'test', 'test', '', 500.00, '18:40:35', 'PM', '18:58:43', '2026-10-10 18:40:35', 1),
(61, 81, 34, 1, 'test', 'test', '', 500.00, '21:04:03', 'PM', '21:05:58', '2026-10-10 21:04:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_unavailability`
--

CREATE TABLE `doctor_unavailability` (
  `UnavailabilityID` int UNSIGNED NOT NULL,
  `UnavailableDate` date NOT NULL,
  `Reason` varchar(255) DEFAULT NULL,
  `CreatedBy` int UNSIGNED DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_unavailability`
--

INSERT INTO `doctor_unavailability` (`UnavailabilityID`, `UnavailableDate`, `Reason`, `CreatedBy`, `CreatedAt`) VALUES
(3, '2026-10-12', 'Sick Leave', 1, '2026-10-08 21:06:31');

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

CREATE TABLE `followups` (
  `FollowUpID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `DoctorID` int UNSIGNED NOT NULL,
  `AppointmentID` int UNSIGNED DEFAULT NULL,
  `FollowUpDate` date NOT NULL,
  `Status` enum('Scheduled','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Scheduled',
  `Remarks` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `followups`
--

INSERT INTO `followups` (`FollowUpID`, `PatientID`, `DoctorID`, `AppointmentID`, `FollowUpDate`, `Status`, `Remarks`) VALUES
(10, 32, 1, 80, '2026-10-17', 'Scheduled', 'Check healing progress'),
(11, 34, 1, 82, '2026-10-16', 'Scheduled', 'Check healing Process');

-- --------------------------------------------------------

--
-- Table structure for table `medical_certificates`
--

CREATE TABLE `medical_certificates` (
  `CertificateID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED DEFAULT NULL,
  `IssuedBy` int UNSIGNED DEFAULT NULL,
  `Diagnosis` text,
  `Remarks` text,
  `FilePath` varchar(255) NOT NULL,
  `IssuedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medical_certificates`
--

INSERT INTO `medical_certificates` (`CertificateID`, `PatientID`, `ConsultationID`, `IssuedBy`, `Diagnosis`, `Remarks`, `FilePath`, `IssuedAt`) VALUES
(1, 34, NULL, 1, 'test', 'test', 'storage/medical-certificates/medcert-PT-2026-3A65-20261005202756.pdf', '2026-10-05 20:27:58'),
(2, 33, NULL, 1, 'test', 'tyest', 'storage/medical-certificates/medcert-PT-2026-3131-20261005221411.pdf', '2026-10-05 22:14:12'),
(3, 37, 55, 39, 'test', 'test', 'storage/medical-certificates/medcert-PT-2026-0CC1-20261005231151.pdf', '2026-10-05 23:11:51');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `MedicineID` int NOT NULL,
  `Name` varchar(100) NOT NULL,
  `DefaultDosage` varchar(50) DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`MedicineID`, `Name`, `DefaultDosage`, `IsActive`) VALUES
(1, 'Ibuprofen', '400mg', 1),
(2, 'Naproxen', '200mg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int UNSIGNED NOT NULL,
  `UserID` int UNSIGNED NOT NULL,
  `Title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `ReferenceID` int UNSIGNED DEFAULT NULL,
  `IsRead` tinyint(1) NOT NULL DEFAULT '0',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `PatientID` int UNSIGNED NOT NULL,
  `PatientCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `UserID` int UNSIGNED DEFAULT NULL,
  `FirstName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MiddleName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LastName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BirthDate` date NOT NULL,
  `Gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `CivilStatus` enum('Single','Married','Widowed','Separated') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `City` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Allergies` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Latitude` decimal(10,7) DEFAULT NULL,
  `Longitude` decimal(10,7) DEFAULT NULL,
  `Phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BloodType` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmergencyContact` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmergencyPhone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PatientType` enum('Regular','Senior Citizen','PWD') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Regular',
  `IdNumber` int DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`PatientID`, `PatientCode`, `UserID`, `FirstName`, `MiddleName`, `LastName`, `BirthDate`, `Gender`, `CivilStatus`, `Address`, `Province`, `City`, `Barangay`, `Allergies`, `Latitude`, `Longitude`, `Phone`, `Email`, `BloodType`, `EmergencyContact`, `EmergencyPhone`, `PatientType`, `IdNumber`, `CreatedAt`) VALUES
(32, 'PT-2026-D0CE', NULL, 'Edith', 'Fajardo', 'Sanoria', '1970-09-01', 'Female', NULL, 'Asuncion, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Asuncion', NULL, NULL, NULL, '09667928518', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-09-28 14:10:44'),
(33, 'PT-2026-3131', NULL, 'Johnrey', 'Yamson', 'Pitogo', '2018-11-01', 'Male', NULL, 'Badiang, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Badiang', NULL, NULL, NULL, '09667928520', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-01 16:40:30'),
(34, 'PT-2026-3A65', NULL, 'Johnrey', 'Yamson', 'Tidalgo', '2026-10-01', 'Male', NULL, 'Bagtican, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Bagtican', NULL, NULL, NULL, '09667928520', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-01 16:45:10'),
(37, 'PT-2026-0CC1', NULL, 'Vince', '', 'Tidalgo', '2004-07-03', 'Male', NULL, 'Ibarra, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Ibarra', NULL, NULL, NULL, '09667928516', NULL, NULL, NULL, NULL, 'PWD', 987654321, '2026-10-03 10:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int UNSIGNED NOT NULL,
  `BillingID` int UNSIGNED NOT NULL,
  `AmountPaid` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Cash','GCash','Credit Card','Debit Card') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ReferenceNo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ReceivedBy` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PaymentID`, `BillingID`, `AmountPaid`, `PaymentMethod`, `ReferenceNo`, `PaymentDate`, `ReceivedBy`) VALUES
(4, 48, 500.00, 'Cash', 'OR-2026-00048-1', '2026-10-06 22:29:23', 1),
(5, 47, 400.00, 'Cash', 'OR-2026-00047-1', '2026-10-06 22:29:30', 1),
(6, 50, 500.00, 'Cash', 'OR-2026-00050-1', '2026-10-10 09:17:59', 1),
(7, 49, 500.00, 'Cash', 'OR-2026-00049-1', '2026-10-10 09:18:05', 1),
(8, 52, 500.00, 'Cash', 'OR-2026-00052-1', '2026-10-10 21:07:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `PrescriptionID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED NOT NULL,
  `Medicine` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Dosage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Frequency` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Duration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `Instructions` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`PrescriptionID`, `ConsultationID`, `Medicine`, `Dosage`, `Frequency`, `Duration`, `Quantity`, `Instructions`) VALUES
(49, 55, 'Ibuprofen', '400mg', '3x daily', '5 days', 10, 'Take with food.'),
(50, 56, 'Ibuprofen', '400mg', '2x daily', '5 days', 7, 'Take with food.'),
(51, 58, 'Naproxen', '200mg', '3x daily', '7 days', 10, 'Take with food.'),
(52, 60, 'Ibuprofen', '400mg', '3x daily', '', 14, 'Take on an empty stomach.'),
(53, 61, 'Ibuprofen', '400mg', '2x daily', '7 days', 7, 'Take with food.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int UNSIGNED NOT NULL,
  `Username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PasswordHash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FirstName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LastName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Role` enum('Admin','Doctor','Receptionist','Staff','Patient') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Receptionist',
  `IsDoctor` tinyint(1) NOT NULL DEFAULT '0',
  `Email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` enum('Active','Inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `PasswordHash`, `FirstName`, `LastName`, `Role`, `IsDoctor`, `Email`, `Phone`, `Status`, `CreatedAt`) VALUES
(1, 'admin', '$2y$10$pwAk7.doB.4QsClzr1avqOaygMYD0.BmiYxclUNAS354szNORMQKW', 'System', 'Administrator', 'Admin', 1, NULL, NULL, 'Active', '2026-08-21 09:46:12'),
(39, 'Staff123', '$2y$10$hzWMkDPrRTlkMwp.Jenzq.cZZbOTnEjvyXKVIElYbgosiptbbJcYS', 'Rafael', 'Sanoria', 'Staff', 0, 'rafaelsanoria506@gmail.com', '09667928517', 'Active', '2026-09-28 14:09:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `idx_appointments_schedule` (`AppointmentDate`,`AppointmentTime`,`Status`),
  ADD KEY `idx_appointments_patient` (`PatientID`),
  ADD KEY `idx_appointments_doctor` (`DoctorID`),
  ADD KEY `idx_appt_date_time` (`AppointmentDate`,`AppointmentTime`);

--
-- Indexes for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `idx_auditlogs_user_date` (`UserID`,`LogDate`),
  ADD KEY `idx_auditlogs_record` (`TableAffected`,`RecordID`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`BillingID`),
  ADD UNIQUE KEY `uq_billing_consultation` (`ConsultationID`),
  ADD KEY `idx_billing_patient_status` (`PatientID`,`Status`);

--
-- Indexes for table `clinic_schedule`
--
ALTER TABLE `clinic_schedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD UNIQUE KEY `unique_day` (`DayOfWeek`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`ConsultationID`),
  ADD UNIQUE KEY `uq_consultations_appointment` (`AppointmentID`),
  ADD KEY `idx_consultations_patient_date` (`PatientID`,`ConsultationDate`),
  ADD KEY `idx_consultations_doctor` (`DoctorID`);

--
-- Indexes for table `doctor_unavailability`
--
ALTER TABLE `doctor_unavailability`
  ADD PRIMARY KEY (`UnavailabilityID`),
  ADD UNIQUE KEY `unique_date` (`UnavailableDate`),
  ADD KEY `fk_unavailability_createdby` (`CreatedBy`);

--
-- Indexes for table `followups`
--
ALTER TABLE `followups`
  ADD PRIMARY KEY (`FollowUpID`),
  ADD KEY `idx_followups_date_status` (`FollowUpDate`,`Status`),
  ADD KEY `idx_followups_patient` (`PatientID`),
  ADD KEY `idx_followups_doctor` (`DoctorID`),
  ADD KEY `fk_followups_appointment` (`AppointmentID`);

--
-- Indexes for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  ADD PRIMARY KEY (`CertificateID`),
  ADD KEY `fk_medcert_patient` (`PatientID`),
  ADD KEY `fk_medcert_consultation` (`ConsultationID`),
  ADD KEY `fk_medcert_issuedby` (`IssuedBy`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`MedicineID`),
  ADD UNIQUE KEY `uq_medicine_name` (`Name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `idx_notifications_user_read` (`UserID`,`IsRead`,`CreatedAt`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`),
  ADD UNIQUE KEY `uq_patients_code` (`PatientCode`),
  ADD UNIQUE KEY `uq_patients_user` (`UserID`),
  ADD KEY `idx_patients_name` (`LastName`,`FirstName`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `idx_payments_billing` (`BillingID`),
  ADD KEY `idx_payments_received_by` (`ReceivedBy`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`PrescriptionID`),
  ADD KEY `idx_prescriptions_consultation` (`ConsultationID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `uq_users_username` (`Username`),
  ADD UNIQUE KEY `uq_users_email` (`Email`),
  ADD KEY `idx_users_role_status` (`Role`,`Status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `AppointmentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `clinic_schedule`
--
ALTER TABLE `clinic_schedule`
  MODIFY `ScheduleID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `doctor_unavailability`
--
ALTER TABLE `doctor_unavailability`
  MODIFY `UnavailabilityID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  MODIFY `CertificateID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `MedicineID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON UPDATE CASCADE;

--
-- Constraints for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `fk_auditlogs_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `fk_billing_consultation` FOREIGN KEY (`ConsultationID`) REFERENCES `consultations` (`ConsultationID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_billing_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON UPDATE CASCADE;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `fk_consultations_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultations_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultations_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON UPDATE CASCADE;

--
-- Constraints for table `doctor_unavailability`
--
ALTER TABLE `doctor_unavailability`
  ADD CONSTRAINT `fk_unavailability_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `followups`
--
ALTER TABLE `followups`
  ADD CONSTRAINT `fk_followups_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_followups_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_followups_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON UPDATE CASCADE;

--
-- Constraints for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  ADD CONSTRAINT `fk_medcert_consultation` FOREIGN KEY (`ConsultationID`) REFERENCES `consultations` (`ConsultationID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medcert_issuedby` FOREIGN KEY (`IssuedBy`) REFERENCES `users` (`UserID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_medcert_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_billing` FOREIGN KEY (`BillingID`) REFERENCES `billing` (`BillingID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`ReceivedBy`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescriptions_consultation` FOREIGN KEY (`ConsultationID`) REFERENCES `consultations` (`ConsultationID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
