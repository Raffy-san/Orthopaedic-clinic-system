-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2026 at 03:45 PM
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
  `CreatedBy` int UNSIGNED DEFAULT NULL,
  `AppointmentDate` date NOT NULL,
  `AppointmentTime` time DEFAULT NULL,
  `meridiem` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Purpose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ChiefComplaint` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `Remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`AppointmentID`, `PatientID`, `DoctorID`, `CreatedBy`, `AppointmentDate`, `AppointmentTime`, `meridiem`, `Purpose`, `ChiefComplaint`, `Status`, `Remarks`, `CreatedAt`) VALUES
(86, 39, 1, NULL, '2026-10-14', '09:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-14 04:41:47'),
(87, 39, 1, 1, '2026-10-14', '09:30:00', 'AM', 'Follow-up check-up', 'Follow-up check-up', 'Completed', 'test', '2026-10-14 04:42:46'),
(88, 40, 1, NULL, '2026-10-14', '10:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-14 15:19:43'),
(89, 40, 1, 1, '2026-10-15', '09:00:00', 'AM', 'Follow-up check-up', 'Follow-up check-up', 'Completed', NULL, '2026-10-14 15:20:29'),
(90, 41, 1, NULL, '2026-10-15', '09:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-14 16:05:37'),
(91, 39, 1, NULL, '2026-10-15', '09:30:00', 'AM', 'Consultation', 'Left Knee pain', 'Confirmed', NULL, '2026-10-14 16:15:52'),
(92, 42, 1, NULL, '2026-10-15', '09:00:00', 'AM', 'Consultation', 'Right Knee pain', 'Cancelled', NULL, '2026-10-14 16:17:51'),
(93, 40, 1, 1, '2026-10-16', '09:00:00', 'AM', 'Follow-up check-up', 'Follow-up check-up', 'Confirmed', 'check healing process', '2026-10-14 16:28:18'),
(94, 43, 1, NULL, '2026-10-15', '11:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Confirmed', NULL, '2026-10-14 16:46:30'),
(95, 41, 1, 1, '2026-10-16', '09:00:00', 'AM', 'Follow-up check-up', 'Follow-up check-up', 'Confirmed', NULL, '2026-10-14 16:48:27'),
(96, 47, 1, NULL, '2026-10-16', '09:00:00', 'AM', 'Consultation', 'Left Knee pain', 'Cancelled', NULL, '2026-10-15 04:17:50'),
(97, 46, 1, NULL, '2026-10-21', NULL, NULL, 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-15 04:18:07'),
(98, 44, 1, NULL, '2026-10-20', '09:00:00', 'AM', 'consultation', 'test', 'Cancelled', NULL, '2026-10-15 04:39:32'),
(99, 44, 1, NULL, '2026-10-22', NULL, NULL, 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-21 14:15:38'),
(100, 42, 1, NULL, '2026-10-26', NULL, NULL, 'Consultation', 'Right Knee pain', 'Confirmed', NULL, '2026-10-21 14:25:04'),
(101, 46, 1, 1, '2026-10-21', NULL, NULL, 'Follow-up check-up', 'Follow-up check-up', 'Confirmed', NULL, '2026-10-21 15:05:50'),
(102, 44, 1, 1, '2026-10-23', NULL, NULL, 'Follow-up check-up', 'Follow-up check-up', 'Confirmed', NULL, '2026-10-22 15:07:36');

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `LogID` int UNSIGNED NOT NULL,
  `UserID` int UNSIGNED DEFAULT NULL,
  `Action` enum('CREATE','UPDATE','DELETE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `TableAffected` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `RecordID` int UNSIGNED NOT NULL,
  `FieldChanged` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `OldValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `NewValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `LogDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auditlogs`
--

INSERT INTO `auditlogs` (`LogID`, `UserID`, `Action`, `TableAffected`, `RecordID`, `FieldChanged`, `OldValue`, `NewValue`, `LogDate`, `IPAddress`) VALUES
(1, 1, 'CREATE', 'users', 41, NULL, NULL, '{\"Username\":\"Staff123\",\"FirstName\":\"Rafael\",\"MiddleName\":\"Fajardo\",\"LastName\":\"Sanoria\",\"Role\":\"Staff\",\"IsDoctor\":0,\"Email\":\"rafaelsanoria506@gmail.com\"}', '2026-10-14 00:51:41', '::1'),
(2, 1, 'CREATE', 'users', 42, NULL, NULL, '{\"Username\":\"Doctor123\",\"FirstName\":\"Jevy Jon\",\"MiddleName\":\"\",\"LastName\":\"Yotina\",\"Role\":\"Doctor\",\"IsDoctor\":0,\"Email\":null}', '2026-10-14 01:00:53', '::1'),
(3, 41, 'CREATE', 'patients', 39, NULL, NULL, '{\"PatientCode\":\"PT-2026-5B20\",\"FirstName\":\"Alexis\",\"MiddleName\":\"\",\"LastName\":\"Montalbo\",\"BirthDate\":\"2002-08-12\",\"Gender\":\"Female\",\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-14 12:41:34', '::1'),
(4, NULL, 'CREATE', 'appointments', 86, NULL, NULL, '{\"PatientID\":39,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-14\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-14 12:41:47', '::1'),
(5, 41, 'CREATE', 'Medicines', 5, NULL, NULL, '{\"Name\":\"Ibuprofen\",\"BrandName\":\"Test\",\"DefaultDosage\":\"200mg\"}', '2026-10-14 12:42:23', '::1'),
(6, 1, 'UPDATE', 'consultations', 65, 'Diagnosis', NULL, 'test', '2026-10-14 12:42:46', '::1'),
(7, 1, 'UPDATE', 'consultations', 65, 'Treatment', NULL, 'test', '2026-10-14 12:42:46', '::1'),
(8, 1, 'CREATE', 'prescriptions', 57, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"3 days\",\"quantity\":3,\"instructions\":\"Take with food.\"}', '2026-10-14 12:42:46', '::1'),
(9, 1, 'CREATE', 'billing', 55, NULL, NULL, '{\"ConsultationID\":65,\"PatientID\":39,\"OriginalAmount\":500}', '2026-10-14 12:42:46', '::1'),
(10, 1, 'UPDATE', 'appointments', 86, 'Status', 'Confirmed', 'Completed', '2026-10-14 12:42:46', '::1'),
(11, 1, 'CREATE', 'appointments', 87, NULL, NULL, '{\"PatientID\":39,\"AppointmentDate\":\"2026-10-14\",\"AppointmentTime\":\"09:30:00\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-14 12:42:46', '::1'),
(12, 1, 'CREATE', 'followups', 13, NULL, NULL, '{\"PatientID\":39,\"DoctorID\":1,\"AppointmentID\":87,\"FollowUpDate\":\"2026-10-14\"}', '2026-10-14 12:42:46', '::1'),
(13, 41, 'CREATE', 'payments', 12, NULL, NULL, '{\"BillingID\":55,\"AmountPaid\":500,\"ReceivedBy\":41}', '2026-10-14 12:47:13', '::1'),
(14, 41, 'UPDATE', 'billing', 55, 'Status', 'Unpaid', 'Paid', '2026-10-14 12:47:13', '::1'),
(15, 1, 'UPDATE', 'consultations', 66, 'Diagnosis', NULL, 'test', '2026-10-14 12:55:50', '::1'),
(16, 1, 'UPDATE', 'consultations', 66, 'Treatment', NULL, 'test', '2026-10-14 12:55:50', '::1'),
(17, 1, 'CREATE', 'prescriptions', 58, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"5 days\",\"quantity\":5,\"instructions\":\"May cause drowsiness. Avoid driving.\"}', '2026-10-14 12:55:50', '::1'),
(18, 1, 'CREATE', 'billing', 56, NULL, NULL, '{\"ConsultationID\":66,\"PatientID\":39,\"OriginalAmount\":500}', '2026-10-14 12:55:50', '::1'),
(19, 1, 'UPDATE', 'appointments', 87, 'Status', 'Confirmed', 'Completed', '2026-10-14 12:55:50', '::1'),
(20, 1, 'CREATE', 'payments', 13, NULL, NULL, '{\"BillingID\":56,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-14 20:10:30', '::1'),
(21, 1, 'UPDATE', 'billing', 56, 'Status', 'Unpaid', 'Paid', '2026-10-14 20:10:30', '::1'),
(22, 41, 'CREATE', 'patients', 40, NULL, NULL, '{\"PatientCode\":\"PT-2026-C8D6\",\"FirstName\":\"Alexander Dave\",\"MiddleName\":\"Besin\",\"LastName\":\"Fajardo\",\"BirthDate\":\"2002-10-15\",\"Gender\":\"Male\",\"Phone\":\"09667928516\",\"Email\":\"alexander.fajardo@gmail.com\",\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-14 22:46:49', '::1'),
(23, 41, 'UPDATE', 'patients', 40, 'Email', 'alexander.fajardo@gmail.com', 'fajardo.alexander@gmail.com', '2026-10-14 22:50:14', '::1'),
(24, NULL, 'CREATE', 'appointments', 88, NULL, NULL, '{\"PatientID\":40,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-14\",\"AppointmentTime\":\"10:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-14 23:19:43', '::1'),
(25, 1, 'UPDATE', 'consultations', 67, 'Diagnosis', NULL, 'Left Knee pain', '2026-10-14 23:20:29', '::1'),
(26, 1, 'UPDATE', 'consultations', 67, 'Treatment', NULL, 'Anti-inflamatory meds', '2026-10-14 23:20:29', '::1'),
(27, 1, 'CREATE', 'prescriptions', 59, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"5 days\",\"quantity\":5,\"instructions\":\"Take with food.\"}', '2026-10-14 23:20:29', '::1'),
(28, 1, 'CREATE', 'billing', 57, NULL, NULL, '{\"ConsultationID\":67,\"PatientID\":40,\"OriginalAmount\":500}', '2026-10-14 23:20:29', '::1'),
(29, 1, 'UPDATE', 'appointments', 88, 'Status', 'Confirmed', 'Completed', '2026-10-14 23:20:29', '::1'),
(30, 1, 'CREATE', 'appointments', 89, NULL, NULL, '{\"PatientID\":40,\"AppointmentDate\":\"2026-10-16\",\"AppointmentTime\":\"09:00:00\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-14 23:20:29', '::1'),
(31, 1, 'CREATE', 'followups', 14, NULL, NULL, '{\"PatientID\":40,\"DoctorID\":1,\"AppointmentID\":89,\"FollowUpDate\":\"2026-10-16\"}', '2026-10-14 23:20:29', '::1'),
(32, 1, 'CREATE', 'medical_certificates', 12, NULL, NULL, '{\"PatientID\":39,\"Diagnosis\":\"sdsd\",\"IssuedBy\":1}', '2026-10-14 23:30:23', '::1'),
(33, 1, 'CREATE', 'medical_certificates', 13, NULL, NULL, '{\"PatientID\":39,\"Diagnosis\":\"sdsdsd\",\"IssuedBy\":1}', '2026-10-14 23:32:32', '::1'),
(34, 41, 'CREATE', 'patients', 41, NULL, NULL, '{\"PatientCode\":\"PT-2026-C3CF\",\"FirstName\":\"Johnrey\",\"MiddleName\":\"\",\"LastName\":\"Pitogo\",\"BirthDate\":\"2003-01-12\",\"Gender\":\"Male\",\"Phone\":\"09667928519\",\"Email\":\"johnrey.pitogo@gmail.com\",\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-15 00:05:18', '::1'),
(35, 41, 'CREATE', 'appointments', 90, NULL, NULL, '{\"PatientID\":41,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-15\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 00:05:37', '::1'),
(36, 41, 'CREATE', 'appointments', 91, NULL, NULL, '{\"PatientID\":39,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-15\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 00:15:52', '::1'),
(37, 41, 'CREATE', 'payments', 14, NULL, NULL, '{\"BillingID\":57,\"AmountPaid\":499.99,\"ReceivedBy\":41}', '2026-10-15 00:16:37', '::1'),
(38, 41, 'UPDATE', 'billing', 57, 'Status', 'Unpaid', 'Partially Paid', '2026-10-15 00:16:37', '::1'),
(39, 41, 'CREATE', 'payments', 15, NULL, NULL, '{\"BillingID\":57,\"AmountPaid\":0.01,\"ReceivedBy\":41}', '2026-10-15 00:16:41', '::1'),
(40, 41, 'UPDATE', 'billing', 57, 'Status', 'Partially Paid', 'Paid', '2026-10-15 00:16:41', '::1'),
(41, NULL, 'UPDATE', 'appointments', 89, 'AppointmentDate/AppointmentTime', '2026-10-16 09:00:00 AM', '2026-10-15 09:00:00 AM', '2026-10-15 00:17:00', '::1'),
(42, 41, 'CREATE', 'patients', 42, NULL, NULL, '{\"PatientCode\":\"PT-2026-A536\",\"FirstName\":\"Kyle Dynes\",\"MiddleName\":\"\",\"LastName\":\"Lagria\",\"BirthDate\":\"2001-10-15\",\"Gender\":\"Male\",\"Phone\":\"09667928521\",\"Email\":null,\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-15 00:17:42', '::1'),
(43, 41, 'CREATE', 'appointments', 92, NULL, NULL, '{\"PatientID\":42,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-15\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 00:17:51', '::1'),
(49, 1, 'UPDATE', 'consultations', 68, 'Diagnosis', NULL, 'test', '2026-10-15 00:28:18', '::1'),
(50, 1, 'UPDATE', 'consultations', 68, 'Treatment', NULL, 'test', '2026-10-15 00:28:18', '::1'),
(51, 1, 'CREATE', 'prescriptions', 61, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"3 days\",\"quantity\":5,\"instructions\":\"Take on an empty stomach.\"}', '2026-10-15 00:28:18', '::1'),
(52, 1, 'CREATE', 'billing', 59, NULL, NULL, '{\"ConsultationID\":68,\"PatientID\":40,\"OriginalAmount\":500}', '2026-10-15 00:28:18', '::1'),
(53, 1, 'UPDATE', 'appointments', 89, 'Status', 'Confirmed', 'Completed', '2026-10-15 00:28:18', '::1'),
(54, 1, 'CREATE', 'appointments', 93, NULL, NULL, '{\"PatientID\":40,\"AppointmentDate\":\"2026-10-16\",\"AppointmentTime\":\"09:00:00\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-15 00:28:18', '::1'),
(55, 1, 'CREATE', 'followups', 15, NULL, NULL, '{\"PatientID\":40,\"DoctorID\":1,\"AppointmentID\":93,\"FollowUpDate\":\"2026-10-16\"}', '2026-10-15 00:28:18', '::1'),
(76, 41, 'CREATE', 'patients', 43, NULL, NULL, '{\"PatientCode\":\"PT-2026-E532\",\"FirstName\":\"Edith\",\"MiddleName\":\"\",\"LastName\":\"Sanoria\",\"BirthDate\":\"1970-07-01\",\"Gender\":\"Female\",\"Phone\":null,\"Email\":\"rafaelsanoria5123@gmail.com\",\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-15 00:46:21', '::1'),
(77, 41, 'CREATE', 'appointments', 94, NULL, NULL, '{\"PatientID\":43,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-15\",\"AppointmentTime\":\"09:30:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 00:46:30', '::1'),
(78, 41, 'UPDATE', 'patients', 43, 'Address', 'Planza, Babatngon, Leyte', 'Agbanga, Matalom, Leyte', '2026-10-15 00:47:14', '::1'),
(79, 41, 'UPDATE', 'patients', 43, 'City', 'Babatngon', 'Matalom', '2026-10-15 00:47:14', '::1'),
(80, 41, 'UPDATE', 'patients', 43, 'Barangay', 'Planza', 'Agbanga', '2026-10-15 00:47:14', '::1'),
(81, 1, 'UPDATE', 'consultations', 69, 'Diagnosis', NULL, 'test', '2026-10-15 00:48:27', '::1'),
(82, 1, 'UPDATE', 'consultations', 69, 'Treatment', NULL, 'test', '2026-10-15 00:48:27', '::1'),
(83, 1, 'CREATE', 'prescriptions', 66, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"3 days\",\"quantity\":3,\"instructions\":\"Take with food.\"}', '2026-10-15 00:48:27', '::1'),
(84, 1, 'CREATE', 'billing', 64, NULL, NULL, '{\"ConsultationID\":69,\"PatientID\":41,\"OriginalAmount\":500}', '2026-10-15 00:48:27', '::1'),
(85, 1, 'UPDATE', 'appointments', 90, 'Status', 'Confirmed', 'Completed', '2026-10-15 00:48:27', '::1'),
(86, 1, 'CREATE', 'appointments', 95, NULL, NULL, '{\"PatientID\":41,\"AppointmentDate\":\"2026-10-16\",\"AppointmentTime\":\"09:00:00\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-15 00:48:27', '::1'),
(87, 1, 'CREATE', 'followups', 16, NULL, NULL, '{\"PatientID\":41,\"DoctorID\":1,\"AppointmentID\":95,\"FollowUpDate\":\"2026-10-16\"}', '2026-10-15 00:48:27', '::1'),
(88, 1, 'CREATE', 'payments', 16, NULL, NULL, '{\"BillingID\":59,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-15 00:48:44', '::1'),
(89, 1, 'UPDATE', 'billing', 59, 'Status', 'Unpaid', 'Paid', '2026-10-15 00:48:44', '::1'),
(90, 1, 'CREATE', 'payments', 17, NULL, NULL, '{\"BillingID\":64,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-15 00:48:49', '::1'),
(91, 1, 'UPDATE', 'billing', 64, 'Status', 'Unpaid', 'Paid', '2026-10-15 00:48:49', '::1'),
(92, 41, 'CREATE', 'patients', 44, NULL, NULL, '{\"PatientCode\":\"PT-2026-7342\",\"FirstName\":\"test\",\"MiddleName\":\"test\",\"LastName\":\"tet\",\"BirthDate\":\"2026-10-07\",\"Gender\":\"Male\",\"Phone\":\"09667928522\",\"Email\":null,\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-15 01:04:55', '::1'),
(93, 41, 'CREATE', 'patients', 45, NULL, NULL, '{\"PatientCode\":\"PT-2026-BFB5\",\"FirstName\":\"tesss\",\"MiddleName\":\"ss\",\"LastName\":\"sss\",\"BirthDate\":\"2026-10-05\",\"Gender\":\"Male\",\"Phone\":\"09667928522\",\"Email\":null,\"PatientType\":\"PWD\",\"IdNumber\":\"12345678\"}', '2026-10-15 01:05:21', '::1'),
(94, 41, 'CREATE', 'patients', 46, NULL, NULL, '{\"PatientCode\":\"PT-2026-2134\",\"FirstName\":\"sdsd\",\"MiddleName\":\"sds\",\"LastName\":\"dsd\",\"BirthDate\":\"2026-10-09\",\"Gender\":\"Male\",\"Phone\":\"09667928523\",\"Email\":null,\"PatientType\":\"Senior Citizen\",\"IdNumber\":\"1234566666\"}', '2026-10-15 01:05:43', '::1'),
(95, 41, 'CREATE', 'patients', 47, NULL, NULL, '{\"PatientCode\":\"PT-2026-53C0\",\"FirstName\":\"sdsdsdsdsd\",\"MiddleName\":\"sdsds\",\"LastName\":\"dsdsd\",\"BirthDate\":\"2026-10-06\",\"Gender\":\"Male\",\"Phone\":\"09667928524\",\"Email\":null,\"PatientType\":\"Regular\",\"IdNumber\":null}', '2026-10-15 01:06:13', '::1'),
(96, NULL, 'UPDATE', 'appointments', 94, 'AppointmentDate/AppointmentTime', '2026-10-15 09:30:00 AM', '2026-10-15 10:00:00 AM', '2026-10-15 01:19:16', '::1'),
(97, NULL, 'UPDATE', 'appointments', 91, 'AppointmentDate/AppointmentTime', '2026-10-15 09:00:00 AM', '2026-10-15 09:30:00 AM', '2026-10-15 01:19:32', '::1'),
(98, 41, 'UPDATE', 'patients', 43, 'Phone', NULL, '09667928526', '2026-10-15 01:26:04', '::1'),
(99, NULL, 'UPDATE', 'appointments', 94, 'Rescheduled appointment (PT-2026-E532)', '2026-10-15 at 10:00 AM', '2026-10-15 at 10:30 AM', '2026-10-15 01:30:51', '::1'),
(100, 41, 'UPDATE', 'appointments', 94, 'Rescheduled appointment (PT-2026-E532)', '2026-10-15 at 10:30 AM', '2026-10-15 at 11:00 AM', '2026-10-15 01:33:35', '::1'),
(101, 41, 'UPDATE', 'patients', 39, 'FirstName', 'Alexis', 'Alex', '2026-10-15 01:36:38', '::1'),
(102, 41, 'CREATE', 'appointments', 96, NULL, NULL, '{\"PatientID\":47,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-16\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 12:17:50', '::1'),
(103, 41, 'CREATE', 'appointments', 97, NULL, NULL, '{\"PatientID\":46,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-16\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"Consultation\"}', '2026-10-15 12:18:07', '::1'),
(104, 1, 'CREATE', 'medical_certificates', 14, NULL, NULL, '{\"PatientID\":39,\"Diagnosis\":\"sdsds\",\"IssuedBy\":1}', '2026-10-15 12:23:57', '::1'),
(105, 1, 'CREATE', 'medical_certificates', 15, NULL, NULL, '{\"PatientID\":39,\"Diagnosis\":\"sdsds\",\"IssuedBy\":1}', '2026-10-15 12:24:10', '::1'),
(106, 41, 'CREATE', 'appointments', 98, NULL, NULL, '{\"PatientID\":44,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-20\",\"AppointmentTime\":\"09:00:00\",\"meridiem\":\"AM\",\"Purpose\":\"consultation\"}', '2026-10-15 12:39:32', '::1'),
(107, 41, 'UPDATE', 'appointments', 97, 'Rescheduled appointment (PT-2026-2134)', '2026-10-16', '2026-10-22', '2026-10-21 22:13:02', '::1'),
(108, 41, 'UPDATE', 'appointments', 97, 'Rescheduled appointment (PT-2026-2134)', '2026-10-22', '2026-10-27', '2026-10-21 22:13:14', '::1'),
(109, 41, 'CREATE', 'appointments', 99, NULL, NULL, '{\"PatientID\":44,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-23\",\"Purpose\":\"Consultation\"}', '2026-10-21 22:15:38', '::1'),
(110, 41, 'CREATE', 'appointments', 100, NULL, NULL, '{\"PatientID\":42,\"DoctorID\":1,\"AppointmentDate\":\"2026-10-26\",\"Purpose\":\"Consultation\"}', '2026-10-21 22:25:04', '::1'),
(111, 41, 'UPDATE', 'appointments', 97, 'Rescheduled appointment (PT-2026-2134)', '2026-10-27', '2026-10-21', '2026-10-21 22:28:17', '::1'),
(112, 1, 'UPDATE', 'consultations', 70, 'Diagnosis', NULL, 'Left Knee Pain', '2026-10-21 23:05:50', '::1'),
(113, 1, 'UPDATE', 'consultations', 70, 'Treatment', NULL, '', '2026-10-21 23:05:50', '::1'),
(114, 1, 'CREATE', 'prescriptions', 67, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"2x daily\",\"duration\":\"2 days\",\"quantity\":4,\"instructions\":\"Take with food.\"}', '2026-10-21 23:05:50', '::1'),
(115, 1, 'CREATE', 'billing', 65, NULL, NULL, '{\"ConsultationID\":70,\"PatientID\":46,\"OriginalAmount\":500}', '2026-10-21 23:05:50', '::1'),
(116, 1, 'UPDATE', 'appointments', 97, 'Status', 'Confirmed', 'Completed', '2026-10-21 23:05:50', '::1'),
(117, 1, 'CREATE', 'appointments', 101, NULL, NULL, '{\"PatientID\":46,\"AppointmentDate\":\"2026-10-21\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-21 23:05:50', '::1'),
(118, 1, 'CREATE', 'followups', 17, NULL, NULL, '{\"PatientID\":46,\"DoctorID\":1,\"AppointmentID\":101,\"FollowUpDate\":\"2026-10-21\"}', '2026-10-21 23:05:50', '::1'),
(119, 1, 'UPDATE', 'billing', 65, 'DiscountType', 'None', 'Senior Citizen', '2026-10-22 21:09:40', '::1'),
(120, 1, 'UPDATE', 'billing', 65, 'DiscountPercent', '0.00', '20.00', '2026-10-22 21:09:40', '::1'),
(121, 1, 'UPDATE', 'billing', 65, 'DiscountAmount', '0.00', '100.00', '2026-10-22 21:09:40', '::1'),
(122, 1, 'UPDATE', 'billing', 65, 'FinalAmount', '500.00', '400.00', '2026-10-22 21:09:40', '::1'),
(123, 1, 'CREATE', 'payments', 18, NULL, NULL, '{\"BillingID\":65,\"AmountPaid\":200,\"ReceivedBy\":1}', '2026-10-22 21:09:40', '::1'),
(124, 1, 'UPDATE', 'billing', 65, 'Status', 'Unpaid', 'Partially Paid', '2026-10-22 21:09:40', '::1'),
(125, 1, 'CREATE', 'payments', 19, NULL, NULL, '{\"BillingID\":65,\"AmountPaid\":200,\"ReceivedBy\":1}', '2026-10-22 22:52:51', '::1'),
(126, 1, 'UPDATE', 'billing', 65, 'Status', 'Partially Paid', 'Paid', '2026-10-22 22:52:51', '::1'),
(127, 41, 'UPDATE', 'appointments', 99, 'Rescheduled appointment (PT-2026-7342)', '2026-10-23', '2026-10-22', '2026-10-22 23:07:17', '::1'),
(128, 1, 'UPDATE', 'consultations', 71, 'Diagnosis', NULL, 'test', '2026-10-22 23:07:36', '::1'),
(129, 1, 'UPDATE', 'consultations', 71, 'Treatment', NULL, '', '2026-10-22 23:07:36', '::1'),
(130, 1, 'CREATE', 'prescriptions', 68, NULL, NULL, '{\"medicine\":\"Ibuprofen (Test)\",\"dosage\":\"200mg\",\"frequency\":\"1x daily\",\"duration\":\"1 day\",\"quantity\":1,\"instructions\":\"Take with food.\"}', '2026-10-22 23:07:36', '::1'),
(131, 1, 'CREATE', 'billing', 66, NULL, NULL, '{\"ConsultationID\":71,\"PatientID\":44,\"OriginalAmount\":500}', '2026-10-22 23:07:36', '::1'),
(132, 1, 'UPDATE', 'appointments', 99, 'Status', 'Confirmed', 'Completed', '2026-10-22 23:07:36', '::1'),
(133, 1, 'CREATE', 'appointments', 102, NULL, NULL, '{\"PatientID\":44,\"AppointmentDate\":\"2026-10-23\",\"Purpose\":\"Follow-up check-up\",\"Status\":\"Confirmed\"}', '2026-10-22 23:07:36', '::1'),
(134, 1, 'CREATE', 'followups', 18, NULL, NULL, '{\"PatientID\":44,\"DoctorID\":1,\"AppointmentID\":102,\"FollowUpDate\":\"2026-10-23\"}', '2026-10-22 23:07:36', '::1'),
(135, 1, 'CREATE', 'payments', 20, NULL, NULL, '{\"BillingID\":66,\"AmountPaid\":250,\"ReceivedBy\":1}', '2026-10-22 23:08:02', '::1'),
(136, 1, 'UPDATE', 'billing', 66, 'Status', 'Unpaid', 'Partially Paid', '2026-10-22 23:08:02', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `BillingID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `OriginalAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `DiscountType` enum('None','Senior Citizen','PWD') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
  `DiscountPercent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `DiscountAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `FinalAmount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BillingDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Status` enum('Unpaid','Partially Paid','Paid','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`BillingID`, `ConsultationID`, `PatientID`, `OriginalAmount`, `DiscountType`, `DiscountPercent`, `DiscountAmount`, `FinalAmount`, `BillingDate`, `Status`) VALUES
(55, 65, 39, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-14 12:42:46', 'Paid'),
(56, 66, 39, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-14 12:55:50', 'Paid'),
(57, 67, 40, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-14 23:20:29', 'Paid'),
(59, 68, 40, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-15 00:28:18', 'Paid'),
(64, 69, 41, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-15 00:48:27', 'Paid'),
(65, 70, 46, 500.00, 'Senior Citizen', 20.00, 100.00, 400.00, '2026-10-21 23:05:50', 'Paid'),
(66, 71, 44, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-22 23:07:36', 'Partially Paid');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clinic_schedule`
--

INSERT INTO `clinic_schedule` (`ScheduleID`, `DayOfWeek`, `IsOpen`, `StartTime`, `EndTime`) VALUES
(1, 1, 1, '09:00:00', '12:00:00'),
(2, 2, 1, '09:00:00', '12:00:00'),
(3, 3, 1, '09:00:00', '12:00:00'),
(4, 4, 1, '09:00:00', '12:00:00'),
(5, 5, 1, '09:00:00', '12:00:00'),
(6, 6, 0, '09:00:00', '12:00:00'),
(7, 7, 0, '09:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `ConsultationID` int UNSIGNED NOT NULL,
  `AppointmentID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `DoctorID` int UNSIGNED NOT NULL,
  `Diagnosis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Treatment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ConsultationFee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `StartTime` time DEFAULT NULL,
  `Meridiem` enum('AM','PM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `ConsultationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsCompleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`ConsultationID`, `AppointmentID`, `PatientID`, `DoctorID`, `Diagnosis`, `Treatment`, `Notes`, `ConsultationFee`, `StartTime`, `Meridiem`, `EndTime`, `ConsultationDate`, `IsCompleted`) VALUES
(65, 86, 39, 1, 'test', 'test', '', 500.00, '12:42:08', 'PM', '12:42:46', '2026-10-14 12:42:08', 1),
(66, 87, 39, 1, 'test', 'test', '', 500.00, '12:55:38', 'PM', '12:55:50', '2026-10-14 12:55:38', 1),
(67, 88, 40, 1, 'Left Knee pain', 'Anti-inflamatory meds', '', 500.00, '23:19:57', 'PM', '23:20:29', '2026-10-14 23:19:57', 1),
(68, 89, 40, 1, 'test', 'test', '', 500.00, '00:26:48', 'AM', '00:28:18', '2026-10-15 00:26:48', 1),
(69, 90, 41, 1, 'test', 'test', '', 500.00, '00:34:02', 'AM', '00:48:27', '2026-10-15 00:34:02', 1),
(70, 97, 46, 1, 'Left Knee Pain', '', 'Ttetetetetsatafdasdadasdasd', 500.00, '22:28:23', 'PM', '23:05:50', '2026-10-21 22:28:23', 1),
(71, 99, 44, 1, 'test', '', 'test', 500.00, '23:07:21', 'PM', '23:07:36', '2026-10-22 23:07:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_unavailability`
--

CREATE TABLE `doctor_unavailability` (
  `UnavailabilityID` int UNSIGNED NOT NULL,
  `UnavailableDate` date NOT NULL,
  `Reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CreatedBy` int UNSIGNED DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `Status` enum('Scheduled','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Scheduled',
  `Remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `followups`
--

INSERT INTO `followups` (`FollowUpID`, `PatientID`, `DoctorID`, `AppointmentID`, `FollowUpDate`, `Status`, `Remarks`) VALUES
(13, 39, 1, 87, '2026-10-14', 'Completed', 'test'),
(14, 40, 1, 89, '2026-10-15', 'Completed', NULL),
(15, 40, 1, 93, '2026-10-16', 'Scheduled', 'check healing process'),
(16, 41, 1, 95, '2026-10-16', 'Scheduled', NULL),
(17, 46, 1, 101, '2026-10-21', 'Scheduled', NULL),
(18, 44, 1, 102, '2026-10-23', 'Scheduled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_certificates`
--

CREATE TABLE `medical_certificates` (
  `CertificateID` int UNSIGNED NOT NULL,
  `PatientID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED DEFAULT NULL,
  `IssuedBy` int UNSIGNED DEFAULT NULL,
  `Diagnosis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `FilePath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `IssuedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_certificates`
--

INSERT INTO `medical_certificates` (`CertificateID`, `PatientID`, `ConsultationID`, `IssuedBy`, `Diagnosis`, `Remarks`, `FilePath`, `IssuedAt`) VALUES
(12, 39, 66, 1, 'sdsd', 'sdsds', 'storage/medical-certificates/medcert-PT-2026-5B20-20261014233023.pdf', '2026-10-14 23:30:23'),
(13, 39, 66, 1, 'sdsdsd', 'sdsdsds', 'storage/medical-certificates/medcert-PT-2026-5B20-20261014233231.pdf', '2026-10-14 23:32:32'),
(14, 39, 66, 1, 'sdsds', 'dsdsdsd', 'storage/medical-certificates/medcert-PT-2026-5B20-20261015122356.pdf', '2026-10-15 12:23:57'),
(15, 39, 66, 1, 'sdsds', 'dsdsds', 'storage/medical-certificates/medcert-PT-2026-5B20-20261015122409.pdf', '2026-10-15 12:24:10');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `MedicineID` int NOT NULL,
  `Name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `BrandName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DefaultDosage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`MedicineID`, `Name`, `BrandName`, `DefaultDosage`, `IsActive`) VALUES
(5, 'Ibuprofen', 'Test', '200mg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `PatientID` int UNSIGNED NOT NULL,
  `PatientCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `UserID` int UNSIGNED DEFAULT NULL,
  `FirstName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MiddleName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LastName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `BirthDate` date NOT NULL,
  `Gender` enum('Male','Female','Other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CivilStatus` enum('Single','Married','Widowed','Separated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `City` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Barangay` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Allergies` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Latitude` decimal(10,7) DEFAULT NULL,
  `Longitude` decimal(10,7) DEFAULT NULL,
  `Phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BloodType` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmergencyContact` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EmergencyPhone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PatientType` enum('Regular','Senior Citizen','PWD') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Regular',
  `IdNumber` int DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`PatientID`, `PatientCode`, `UserID`, `FirstName`, `MiddleName`, `LastName`, `BirthDate`, `Gender`, `CivilStatus`, `Address`, `Province`, `City`, `Barangay`, `Allergies`, `Latitude`, `Longitude`, `Phone`, `Email`, `BloodType`, `EmergencyContact`, `EmergencyPhone`, `PatientType`, `IdNumber`, `CreatedAt`) VALUES
(39, 'PT-2026-5B20', NULL, 'Alex', '', 'Montalbo', '2002-08-12', 'Female', NULL, 'Asuncion, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Asuncion', NULL, NULL, NULL, '09667928520', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 04:41:34'),
(40, 'PT-2026-C8D6', NULL, 'Alexander Dave', 'Besin', 'Fajardo', '2002-10-15', 'Male', NULL, 'Bato II, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Bato II', NULL, NULL, NULL, '09667928516', 'fajardo.alexander@gmail.com', NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 14:46:49'),
(41, 'PT-2026-C3CF', NULL, 'Johnrey', '', 'Pitogo', '2003-01-12', 'Male', NULL, 'Bactul II, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Bactul II', NULL, NULL, NULL, '09667928519', 'johnrey.pitogo@gmail.com', NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 16:05:18'),
(42, 'PT-2026-A536', NULL, 'Kyle Dynes', '', 'Lagria', '2001-10-15', 'Male', NULL, 'Bilibol, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Bilibol', NULL, NULL, NULL, '09667928521', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 16:17:42'),
(43, 'PT-2026-E532', NULL, 'Edith', '', 'Sanoria', '1970-07-01', 'Female', NULL, 'Agbanga, Matalom, Leyte', 'Leyte', 'Matalom', 'Agbanga', NULL, NULL, NULL, '09667928526', 'rafaelsanoria5123@gmail.com', NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 16:46:21'),
(44, 'PT-2026-7342', NULL, 'test', 'test', 'tet', '2026-10-07', 'Male', NULL, 'District III (Pob.), Hinundayan, Southern Leyte', 'Southern Leyte', 'Hinundayan', 'District III (Pob.)', NULL, NULL, NULL, '09667928522', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 17:04:55'),
(45, 'PT-2026-BFB5', NULL, 'tesss', 'ss', 'sss', '2026-10-05', 'Male', NULL, 'Bogo, City of Maasin, Southern Leyte', 'Southern Leyte', 'City of Maasin', 'Bogo', NULL, NULL, NULL, '09667928522', NULL, NULL, NULL, NULL, 'PWD', 12345678, '2026-10-14 17:05:21'),
(46, 'PT-2026-2134', NULL, 'sdsd', 'sds', 'dsd', '2026-10-09', 'Male', NULL, 'Cantamuac, Malitbog, Southern Leyte', 'Southern Leyte', 'Malitbog', 'Cantamuac', NULL, NULL, NULL, '09667928523', NULL, NULL, NULL, NULL, 'Senior Citizen', 1234566666, '2026-10-14 17:05:43'),
(47, 'PT-2026-53C0', NULL, 'sdsdsdsdsd', 'sdsds', 'dsdsd', '2026-10-06', 'Male', NULL, 'Ilaya, Hinunangan, Southern Leyte', 'Southern Leyte', 'Hinunangan', 'Ilaya', NULL, NULL, NULL, '09667928524', NULL, NULL, NULL, NULL, 'Regular', NULL, '2026-10-14 17:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int UNSIGNED NOT NULL,
  `BillingID` int UNSIGNED NOT NULL,
  `AmountPaid` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Cash','GCash','Credit Card','Debit Card') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ReferenceNo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ReceivedBy` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PaymentID`, `BillingID`, `AmountPaid`, `PaymentMethod`, `ReferenceNo`, `PaymentDate`, `ReceivedBy`) VALUES
(12, 55, 500.00, 'Cash', 'OR-2026-00055-1', '2026-10-14 12:47:13', 41),
(13, 56, 500.00, 'Cash', 'OR-2026-00056-1', '2026-10-14 20:10:30', 1),
(14, 57, 499.99, 'Cash', 'OR-2026-00057-1', '2026-10-15 00:16:37', 41),
(15, 57, 0.01, 'Cash', 'OR-2026-00057-2', '2026-10-15 00:16:41', 41),
(16, 59, 500.00, 'Cash', 'OR-2026-00059-1', '2026-10-15 00:48:44', 1),
(17, 64, 500.00, 'Cash', 'OR-2026-00064-1', '2026-10-15 00:48:49', 1),
(18, 65, 200.00, 'Cash', 'OR-2026-00065-1', '2026-10-22 21:09:40', 1),
(19, 65, 200.00, 'Cash', 'OR-2026-00065-2', '2026-10-22 22:52:51', 1),
(20, 66, 250.00, 'Cash', 'OR-2026-00066-1', '2026-10-22 23:08:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `PrescriptionID` int UNSIGNED NOT NULL,
  `ConsultationID` int UNSIGNED NOT NULL,
  `Medicine` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Dosage` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Frequency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Duration` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `Instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`PrescriptionID`, `ConsultationID`, `Medicine`, `Dosage`, `Frequency`, `Duration`, `Quantity`, `Instructions`) VALUES
(57, 65, 'Ibuprofen (Test)', '200mg', '2x daily', '3 days', 3, 'Take with food.'),
(58, 66, 'Ibuprofen (Test)', '200mg', '2x daily', '5 days', 5, 'May cause drowsiness. Avoid driving.'),
(59, 67, 'Ibuprofen (Test)', '200mg', '2x daily', '5 days', 5, 'Take with food.'),
(61, 68, 'Ibuprofen (Test)', '200mg', '2x daily', '3 days', 5, 'Take on an empty stomach.'),
(66, 69, 'Ibuprofen (Test)', '200mg', '2x daily', '3 days', 3, 'Take with food.'),
(67, 70, 'Ibuprofen (Test)', '200mg', '2x daily', '2 days', 4, 'Take with food.'),
(68, 71, 'Ibuprofen (Test)', '200mg', '1x daily', '1 day', 1, 'Take with food.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int UNSIGNED NOT NULL,
  `Username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `PasswordHash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `FirstName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MiddleName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LastName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Role` enum('Admin','Doctor','Staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Staff',
  `IsDoctor` tinyint(1) NOT NULL DEFAULT '0',
  `Email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `PasswordHash`, `FirstName`, `MiddleName`, `LastName`, `Role`, `IsDoctor`, `Email`, `Phone`, `Status`, `CreatedAt`) VALUES
(1, 'admin', '$2y$10$pwAk7.doB.4QsClzr1avqOaygMYD0.BmiYxclUNAS354szNORMQKW', 'Thyam', 'Tiu', 'Fookson', 'Admin', 1, NULL, NULL, 'Active', '2026-08-21 09:46:12'),
(41, 'Staff123', '$2y$10$Rzu2.djom8aE.eFmNdtfsexc2acG0mTY0mN7qMf9.sw415knHsNJG', 'Rafael', 'Fajardo', 'Sanoria', 'Staff', 0, 'rafaelsanoria506@gmail.com', NULL, 'Active', '2026-10-13 16:51:41'),
(42, 'Doctor123', '$2y$10$U.5BQRr8BG7.I/wgNkWX8ujcqJmOh3NAKdq.G0/i5YjarTLFIeEXC', 'Jevy Jon', '', 'Yotina', 'Doctor', 0, NULL, NULL, 'Active', '2026-10-13 17:00:53');

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
  ADD KEY `idx_appt_date_time` (`AppointmentDate`,`AppointmentTime`),
  ADD KEY `fk_appt_created_by` (`CreatedBy`);

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
  MODIFY `AppointmentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `clinic_schedule`
--
ALTER TABLE `clinic_schedule`
  MODIFY `ScheduleID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `doctor_unavailability`
--
ALTER TABLE `doctor_unavailability`
  MODIFY `UnavailabilityID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  MODIFY `CertificateID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `MedicineID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_appt_created_by` FOREIGN KEY (`CreatedBy`) REFERENCES `users` (`UserID`);

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
