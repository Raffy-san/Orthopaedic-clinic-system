-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 21, 2026 at 04:50 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `LogID` int UNSIGNED NOT NULL,
  `UserID` int UNSIGNED DEFAULT NULL,
  `Action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TableAffected` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RecordID` int UNSIGNED DEFAULT NULL,
  `LogDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `Instructions` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'admin', '$2y$10$pwAk7.doB.4QsClzr1avqOaygMYD0.BmiYxclUNAS354szNORMQKW', 'System', 'Administrator', 'Admin', 1, NULL, NULL, 'Active', '2026-08-21 09:46:12');

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
  MODIFY `AppointmentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
