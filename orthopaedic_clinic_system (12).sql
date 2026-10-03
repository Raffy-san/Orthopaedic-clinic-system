-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 03, 2026 at 01:19 PM
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
(59, 32, 39, '2026-10-01', '16:30:00', 'PM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-01 12:08:19'),
(60, 37, 39, '2026-10-04', '14:00:00', 'PM', 'Consultation', 'Left Knee pain', 'Confirmed', NULL, '2026-10-03 13:13:03');

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
(21, 1, 'CREATE', 'users', 39, NULL, NULL, '{\"Username\":\"Staff123\",\"FirstName\":\"Rafael\",\"LastName\":\"Sanoria\",\"Role\":\"Staff\",\"IsDoctor\":0,\"Email\":\"rafaelsanoria506@gmail.com\"}', '2026-09-28 22:09:18', '::1'),
(22, 39, 'CREATE', 'patients', 32, NULL, NULL, '{\"PatientCode\":\"PT-2026-D0CE\",\"FirstName\":\"Edith\",\"MiddleName\":\"Fajardo\",\"LastName\":\"Sanoria\",\"BirthDate\":\"2026-09-01\",\"Gender\":\"Female\",\"PatientType\":\"Regular\"}', '2026-09-28 22:10:44', '::1'),
(23, 39, 'CREATE', 'appointments', 59, NULL, NULL, '{\"PatientID\":32,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-01\",\"AppointmentTime\":\"16:30:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-01 20:08:19', '::1'),
(24, 1, 'UPDATE', 'consultations', 40, 'Diagnosis', NULL, 'test', '2026-10-01 20:09:58', '::1'),
(25, 1, 'UPDATE', 'consultations', 40, 'Treatment', NULL, 'test', '2026-10-01 20:09:58', '::1'),
(26, 1, 'CREATE', 'prescriptions', 31, NULL, NULL, '{\"medicine\":\"Naproxen\",\"dosage\":\"500mg\",\"frequency\":\"2x daily\",\"duration\":\"7 days\",\"instructions\":\"Take with food.\"}', '2026-10-01 20:09:58', '::1'),
(27, 1, 'CREATE', 'billing', 32, NULL, NULL, '{\"ConsultationID\":40,\"PatientID\":32,\"OriginalAmount\":500}', '2026-10-01 20:09:58', '::1'),
(28, 1, 'UPDATE', 'appointments', 59, 'Status', 'Confirmed', 'Completed', '2026-10-01 20:09:58', '::1'),
(29, 1, 'CREATE', 'payments', 35, NULL, NULL, '{\"BillingID\":32,\"AmountPaid\":500,\"ReceivedBy\":1}', '2026-10-01 20:10:09', '::1'),
(30, 1, 'UPDATE', 'billing', 32, 'Status', 'Unpaid', 'Paid', '2026-10-01 20:10:09', '::1'),
(31, 39, 'CREATE', 'patients', 33, NULL, NULL, '{\"PatientCode\":\"PT-2026-3131\",\"FirstName\":\"Johnrey\",\"MiddleName\":\"Yamson\",\"LastName\":\"Pitogo\",\"BirthDate\":\"2018-11-01\",\"Gender\":\"Male\",\"PatientType\":\"Regular\"}', '2026-10-02 00:40:30', '::1'),
(32, 39, 'CREATE', 'patients', 34, NULL, NULL, '{\"PatientCode\":\"PT-2026-3A65\",\"FirstName\":\"Johnrey\",\"MiddleName\":\"Yamson\",\"LastName\":\"Pitogo\",\"BirthDate\":\"2026-10-01\",\"Gender\":\"Male\",\"PatientType\":\"Regular\"}', '2026-10-02 00:45:10', '::1'),
(33, 39, 'UPDATE', 'patients', 34, 'LastName', 'Pitogo', 'Tidalgo', '2026-10-02 00:52:36', '::1'),
(34, 1, 'CREATE', 'medical_certificates', 1, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-02 20:26:27', '::1'),
(35, 1, 'CREATE', 'medical_certificates', 2, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 10:50:09', '::1'),
(36, 1, 'CREATE', 'medical_certificates', 3, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 10:51:01', '::1'),
(37, 1, 'CREATE', 'medical_certificates', 4, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:00:33', '::1'),
(38, 1, 'CREATE', 'medical_certificates', 5, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:06:19', '::1'),
(39, 1, 'CREATE', 'medical_certificates', 6, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:25:34', '::1'),
(40, 1, 'CREATE', 'medical_certificates', 7, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:47:33', '::1'),
(41, 1, 'CREATE', 'medical_certificates', 8, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:50:13', '::1'),
(42, 1, 'CREATE', 'medical_certificates', 9, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:50:43', '::1'),
(43, 1, 'CREATE', 'medical_certificates', 10, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:53:28', '::1'),
(44, 1, 'CREATE', 'medical_certificates', 11, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:54:17', '::1'),
(45, 1, 'CREATE', 'medical_certificates', 12, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:56:01', '::1'),
(46, 1, 'CREATE', 'medical_certificates', 13, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:56:29', '::1'),
(47, 1, 'CREATE', 'medical_certificates', 14, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 11:56:55', '::1'),
(48, 1, 'CREATE', 'medical_certificates', 15, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:14:35', '::1'),
(49, 1, 'CREATE', 'medical_certificates', 16, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:19:12', '::1'),
(50, 1, 'CREATE', 'medical_certificates', 17, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:20:24', '::1'),
(51, 1, 'CREATE', 'medical_certificates', 18, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:30:33', '::1'),
(52, 1, 'CREATE', 'medical_certificates', 19, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:32:38', '::1'),
(53, 39, 'UPDATE', 'patients', 32, 'BirthDate', '2026-09-01', '1970-09-01', '2026-10-03 12:36:31', '::1'),
(54, 1, 'CREATE', 'medical_certificates', 20, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:36:52', '::1'),
(55, 1, 'CREATE', 'medical_certificates', 21, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:37:04', '::1'),
(56, 1, 'CREATE', 'medical_certificates', 22, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"Physically Fit\",\"IssuedBy\":1}', '2026-10-03 12:38:59', '::1'),
(57, 1, 'CREATE', 'medical_certificates', 23, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:49:13', '::1'),
(58, 1, 'CREATE', 'medical_certificates', 24, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:49:59', '::1'),
(59, 1, 'CREATE', 'medical_certificates', 25, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:50:06', '::1'),
(60, 1, 'CREATE', 'medical_certificates', 26, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:50:53', '::1'),
(61, 1, 'CREATE', 'medical_certificates', 27, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:52:57', '::1'),
(62, 1, 'CREATE', 'medical_certificates', 28, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:53:38', '::1'),
(63, 1, 'CREATE', 'medical_certificates', 29, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:55:48', '::1'),
(64, 1, 'CREATE', 'medical_certificates', 30, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:57:43', '::1'),
(65, 1, 'CREATE', 'medical_certificates', 31, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:58:21', '::1'),
(66, 1, 'CREATE', 'medical_certificates', 32, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 12:58:54', '::1'),
(67, 1, 'CREATE', 'medical_certificates', 33, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:01:52', '::1'),
(68, 1, 'CREATE', 'medical_certificates', 34, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:05:16', '::1'),
(69, 1, 'CREATE', 'medical_certificates', 35, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:06:18', '::1'),
(70, 1, 'CREATE', 'medical_certificates', 36, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:07:02', '::1'),
(71, 1, 'CREATE', 'medical_certificates', 37, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:08:47', '::1'),
(72, 1, 'CREATE', 'medical_certificates', 38, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:09:18', '::1'),
(73, 1, 'CREATE', 'medical_certificates', 39, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:09:53', '::1'),
(74, 1, 'CREATE', 'medical_certificates', 40, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:10:34', '::1'),
(75, 1, 'CREATE', 'medical_certificates', 41, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:11:05', '::1'),
(76, 1, 'CREATE', 'medical_certificates', 42, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:11:25', '::1'),
(77, 1, 'CREATE', 'medical_certificates', 43, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:36:35', '::1'),
(78, 1, 'CREATE', 'medical_certificates', 44, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:36:50', '::1'),
(79, 1, 'CREATE', 'medical_certificates', 45, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:37:39', '::1'),
(80, 1, 'CREATE', 'medical_certificates', 46, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:40:32', '::1'),
(81, 1, 'CREATE', 'medical_certificates', 47, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:43:40', '::1'),
(82, 1, 'CREATE', 'medical_certificates', 48, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:44:30', '::1'),
(83, 1, 'CREATE', 'medical_certificates', 49, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 13:50:24', '::1'),
(84, 1, 'CREATE', 'medical_certificates', 50, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 14:02:13', '::1'),
(85, 1, 'CREATE', 'medical_certificates', 51, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"Physically fit\",\"IssuedBy\":1}', '2026-10-03 14:11:38', '::1'),
(86, 1, 'CREATE', 'medical_certificates', 52, NULL, NULL, '{\"PatientID\":32,\"Diagnosis\":\"test\",\"IssuedBy\":1}', '2026-10-03 17:11:08', '::1'),
(87, 39, 'CREATE', 'patients', 35, NULL, NULL, '{\"PatientCode\":\"PT-2026-A78F\",\"FirstName\":\"Vince\",\"MiddleName\":\"\",\"LastName\":\"Tidalgo\",\"BirthDate\":\"2004-06-03\",\"Gender\":\"Male\",\"PatientType\":\"PWD\"}', '2026-10-03 17:13:43', '::1'),
(88, 39, 'CREATE', 'patients', 36, NULL, NULL, '{\"PatientCode\":\"PT-2026-1430\",\"FirstName\":\"Vince\",\"MiddleName\":\"\",\"LastName\":\"Tidalgo\",\"BirthDate\":\"2004-06-03\",\"Gender\":\"Male\",\"PatientType\":\"PWD\"}', '2026-10-03 18:56:58', '::1'),
(89, 39, 'CREATE', 'patients', 37, NULL, NULL, '{\"PatientCode\":\"PT-2026-0CC1\",\"FirstName\":\"Vince\",\"MiddleName\":\"\",\"LastName\":\"Tidalgo\",\"BirthDate\":\"2004-07-03\",\"Gender\":\"Male\",\"PatientType\":\"PWD\",\"IdNumber\":\"123456789\"}', '2026-10-03 18:59:44', '::1'),
(90, 39, 'UPDATE', 'patients', 37, 'IdNumber', '123456789', NULL, '2026-10-03 20:00:52', '::1'),
(91, 39, 'UPDATE', 'patients', 37, 'IdNumber', NULL, '987654321', '2026-10-03 20:46:07', '::1'),
(92, 39, 'CREATE', 'appointments', 60, NULL, NULL, '{\"PatientID\":37,\"DoctorID\":39,\"AppointmentDate\":\"2026-10-04\",\"AppointmentTime\":\"14:00:00\",\"meridiem\":\"PM\",\"Purpose\":\"Consultation\"}', '2026-10-03 21:13:03', '::1');

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
(32, 40, 32, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-01 20:09:58', 'Paid');

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
(40, 59, 32, 1, 'test', 'test', 'test', 500.00, '20:09:48', 'PM', '20:09:58', '2026-10-01 20:09:48', 1);

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
(51, 32, 40, 1, 'Physically fit', 'Fit to go for College, but advised to rest for 3 days first.', 'storage/medical-certificates/medcert-PT-2026-D0CE-20261003141134.pdf', '2026-10-03 14:11:38'),
(52, 32, 40, 1, 'test', 'test', 'storage/medical-certificates/medcert-PT-2026-D0CE-20261003171107.pdf', '2026-10-03 17:11:08');

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
(35, 32, 500.00, 'Cash', 'OR-2026-00032-1', '2026-10-01 20:10:09', 1);

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
  `Instructions` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`PrescriptionID`, `ConsultationID`, `Medicine`, `Dosage`, `Frequency`, `Duration`, `Instructions`) VALUES
(31, 40, 'Naproxen', '500mg', '2x daily', '7 days', 'Take with food.');

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
  ADD KEY `idx_appointments_doctor` (`DoctorID`);

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
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`ConsultationID`),
  ADD UNIQUE KEY `uq_consultations_appointment` (`AppointmentID`),
  ADD KEY `idx_consultations_patient_date` (`PatientID`,`ConsultationDate`),
  ADD KEY `idx_consultations_doctor` (`DoctorID`);

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
  MODIFY `AppointmentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  MODIFY `CertificateID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
  MODIFY `PaymentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
