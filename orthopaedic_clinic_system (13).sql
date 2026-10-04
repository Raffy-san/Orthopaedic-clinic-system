-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 04, 2026 at 03:47 PM
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
(66, 37, 39, '2026-10-04', '14:00:00', 'PM', 'Consultation', 'Left Knee pain', 'Completed', NULL, '2026-10-04 15:42:01'),
(67, 34, 39, '2026-10-04', '14:30:00', 'PM', 'Consultation', 'Right Knee pain', 'Completed', NULL, '2026-10-04 15:44:03');

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
(145, 1, 'UPDATE', 'appointments', 67, 'Status', 'Confirmed', 'Completed', '2026-10-04 23:44:18', '::1');

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
(39, 47, 37, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-04 23:42:22', 'Unpaid'),
(40, 48, 34, 500.00, 'None', 0.00, 0.00, 500.00, '2026-10-04 23:44:18', 'Unpaid');

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
(47, 66, 37, 1, 'test', 'Anti-inflamatory meds', '', 500.00, '23:42:09', 'PM', '23:42:22', '2026-10-04 23:42:09', 1),
(48, 67, 34, 1, 'test', 'test', '', 500.00, '23:44:10', 'PM', '23:44:18', '2026-10-04 23:44:10', 1);

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
(41, 47, 'Celecoxib', '200mg', '1x daily', '7 days', 7, 'Take with food.'),
(42, 48, 'Ibuprofen', '400mg', '3x daily', '5 days', 10, 'Take with food.');

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
  MODIFY `AppointmentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medical_certificates`
--
ALTER TABLE `medical_certificates`
  MODIFY `CertificateID` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `PaymentID` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
