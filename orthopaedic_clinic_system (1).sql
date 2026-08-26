-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2026 at 01:03 PM
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
-- Database: `orthopaedic_clinic_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `AppointmentID` int(10) UNSIGNED NOT NULL,
  `PatientID` int(10) UNSIGNED NOT NULL,
  `DoctorID` int(10) UNSIGNED NOT NULL,
  `AppointmentDate` date NOT NULL,
  `AppointmentTime` time NOT NULL,
  `Purpose` varchar(255) NOT NULL,
  `Status` enum('Pending','Confirmed','Completed','Cancelled','Rescheduled') NOT NULL DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `LogID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED DEFAULT NULL,
  `Action` varchar(255) NOT NULL,
  `TableAffected` varchar(100) DEFAULT NULL,
  `RecordID` int(10) UNSIGNED DEFAULT NULL,
  `LogDate` datetime NOT NULL DEFAULT current_timestamp(),
  `IPAddress` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `BillingID` int(10) UNSIGNED NOT NULL,
  `ConsultationID` int(10) UNSIGNED NOT NULL,
  `PatientID` int(10) UNSIGNED NOT NULL,
  `OriginalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `DiscountType` enum('None','Senior Citizen','PWD') NOT NULL DEFAULT 'None',
  `DiscountPercent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `DiscountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `FinalAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `BillingDate` datetime NOT NULL DEFAULT current_timestamp(),
  `Status` enum('Unpaid','Partially Paid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `ConsultationID` int(10) UNSIGNED NOT NULL,
  `AppointmentID` int(10) UNSIGNED NOT NULL,
  `PatientID` int(10) UNSIGNED NOT NULL,
  `DoctorID` int(10) UNSIGNED NOT NULL,
  `Diagnosis` text NOT NULL,
  `Treatment` text DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `ConsultationFee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ConsultationDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

CREATE TABLE `followups` (
  `FollowUpID` int(10) UNSIGNED NOT NULL,
  `PatientID` int(10) UNSIGNED NOT NULL,
  `DoctorID` int(10) UNSIGNED NOT NULL,
  `AppointmentID` int(10) UNSIGNED DEFAULT NULL,
  `FollowUpDate` date NOT NULL,
  `Status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `PatientID` int(10) UNSIGNED NOT NULL,
  `PatientCode` varchar(20) NOT NULL,
  `UserID` int(10) UNSIGNED DEFAULT NULL,
  `FirstName` varchar(100) NOT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) NOT NULL,
  `BirthDate` date NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `CivilStatus` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Phone` varchar(30) DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `BloodType` varchar(5) DEFAULT NULL,
  `EmergencyContact` varchar(150) DEFAULT NULL,
  `EmergencyPhone` varchar(30) DEFAULT NULL,
  `PatientType` enum('Regular','Senior Citizen','PWD') NOT NULL DEFAULT 'Regular',
  `Latitude` decimal(10,7) DEFAULT NULL,
  `Longitude` decimal(10,7) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(10) UNSIGNED NOT NULL,
  `BillingID` int(10) UNSIGNED NOT NULL,
  `AmountPaid` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Cash','GCash','Credit Card','Debit Card') NOT NULL,
  `ReferenceNo` varchar(100) DEFAULT NULL,
  `PaymentDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ReceivedBy` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `PrescriptionID` int(10) UNSIGNED NOT NULL,
  `ConsultationID` int(10) UNSIGNED NOT NULL,
  `Medicine` varchar(150) NOT NULL,
  `Dosage` varchar(100) NOT NULL,
  `Frequency` varchar(100) NOT NULL,
  `Duration` varchar(100) DEFAULT NULL,
  `Instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(10) UNSIGNED NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Role` enum('Admin','Doctor','Receptionist','Staff','Patient') NOT NULL DEFAULT 'Receptionist',
  `IsDoctor` tinyint(1) NOT NULL DEFAULT 0,
  `Email` varchar(150) DEFAULT NULL,
  `Phone` varchar(30) DEFAULT NULL,
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
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
  MODIFY `AppointmentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `LogID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BillingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `ConsultationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `FollowUpID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `PrescriptionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
