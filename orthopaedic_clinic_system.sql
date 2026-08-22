-- Southern Leyte Orthopaedic Clinic database
-- Import this file in phpMyAdmin or run it in MySQL.

CREATE DATABASE IF NOT EXISTS `orthopaedic_clinic_system`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `orthopaedic_clinic_system`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `billing`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `consultations`;
DROP TABLE IF EXISTS `followups`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `auditlogs`;
DROP TABLE IF EXISTS `patients`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `UserID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Username` VARCHAR(50) NOT NULL,
  `PasswordHash` VARCHAR(255) NOT NULL,
  `FirstName` VARCHAR(100) NOT NULL,
  `LastName` VARCHAR(100) NOT NULL,
  `Role` ENUM('Admin','Doctor','Receptionist','Staff','Patient') NOT NULL DEFAULT 'Receptionist',
  `IsDoctor` TINYINT(1) NOT NULL DEFAULT 0,
  `Email` VARCHAR(150) DEFAULT NULL,
  `Phone` VARCHAR(30) DEFAULT NULL,
  `Status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `uq_users_username` (`Username`),
  UNIQUE KEY `uq_users_email` (`Email`),
  KEY `idx_users_role_status` (`Role`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `patients` (
  `PatientID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PatientCode` VARCHAR(20) NOT NULL,
  `UserID` INT UNSIGNED DEFAULT NULL,
  `FirstName` VARCHAR(100) NOT NULL,
  `MiddleName` VARCHAR(100) DEFAULT NULL,
  `LastName` VARCHAR(100) NOT NULL,
  `BirthDate` DATE NOT NULL,
  `Gender` ENUM('Male','Female','Other') NOT NULL,
  `CivilStatus` ENUM('Single','Married','Widowed','Separated') DEFAULT NULL,
  `Address` VARCHAR(255) DEFAULT NULL,
  `Phone` VARCHAR(30) DEFAULT NULL,
  `Email` VARCHAR(150) DEFAULT NULL,
  `BloodType` VARCHAR(5) DEFAULT NULL,
  `EmergencyContact` VARCHAR(150) DEFAULT NULL,
  `EmergencyPhone` VARCHAR(30) DEFAULT NULL,
  `PatientType` ENUM('Regular','Senior Citizen','PWD') NOT NULL DEFAULT 'Regular',
  `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientID`),
  UNIQUE KEY `uq_patients_code` (`PatientCode`),
  UNIQUE KEY `uq_patients_user` (`UserID`),
  KEY `idx_patients_name` (`LastName`,`FirstName`),
  CONSTRAINT `fk_patients_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointments` (
  `AppointmentID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PatientID` INT UNSIGNED NOT NULL,
  `DoctorID` INT UNSIGNED NOT NULL,
  `AppointmentDate` DATE NOT NULL,
  `AppointmentTime` TIME NOT NULL,
  `Purpose` VARCHAR(255) NOT NULL,
  `Status` ENUM('Pending','Confirmed','Completed','Cancelled','Rescheduled') NOT NULL DEFAULT 'Pending',
  `Remarks` TEXT DEFAULT NULL,
  `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`AppointmentID`),
  KEY `idx_appointments_schedule` (`AppointmentDate`,`AppointmentTime`,`Status`),
  KEY `idx_appointments_patient` (`PatientID`),
  KEY `idx_appointments_doctor` (`DoctorID`),
  CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `consultations` (
  `ConsultationID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AppointmentID` INT UNSIGNED NOT NULL,
  `PatientID` INT UNSIGNED NOT NULL,
  `DoctorID` INT UNSIGNED NOT NULL,
  `Diagnosis` TEXT NOT NULL,
  `Treatment` TEXT DEFAULT NULL,
  `Notes` TEXT DEFAULT NULL,
  `ConsultationFee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `ConsultationDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ConsultationID`),
  UNIQUE KEY `uq_consultations_appointment` (`AppointmentID`),
  KEY `idx_consultations_patient_date` (`PatientID`,`ConsultationDate`),
  KEY `idx_consultations_doctor` (`DoctorID`),
  CONSTRAINT `fk_consultations_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_consultations_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_consultations_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prescriptions` (
  `PrescriptionID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ConsultationID` INT UNSIGNED NOT NULL,
  `Medicine` VARCHAR(150) NOT NULL,
  `Dosage` VARCHAR(100) NOT NULL,
  `Frequency` VARCHAR(100) NOT NULL,
  `Duration` VARCHAR(100) DEFAULT NULL,
  `Instructions` TEXT DEFAULT NULL,
  PRIMARY KEY (`PrescriptionID`),
  KEY `idx_prescriptions_consultation` (`ConsultationID`),
  CONSTRAINT `fk_prescriptions_consultation` FOREIGN KEY (`ConsultationID`) REFERENCES `consultations` (`ConsultationID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `followups` (
  `FollowUpID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PatientID` INT UNSIGNED NOT NULL,
  `DoctorID` INT UNSIGNED NOT NULL,
  `AppointmentID` INT UNSIGNED DEFAULT NULL,
  `FollowUpDate` DATE NOT NULL,
  `Status` ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `Remarks` TEXT DEFAULT NULL,
  PRIMARY KEY (`FollowUpID`),
  KEY `idx_followups_date_status` (`FollowUpDate`,`Status`),
  KEY `idx_followups_patient` (`PatientID`),
  KEY `idx_followups_doctor` (`DoctorID`),
  CONSTRAINT `fk_followups_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_followups_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `users` (`UserID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_followups_appointment` FOREIGN KEY (`AppointmentID`) REFERENCES `appointments` (`AppointmentID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `billing` (
  `BillingID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ConsultationID` INT UNSIGNED NOT NULL,
  `PatientID` INT UNSIGNED NOT NULL,
  `OriginalAmount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `DiscountType` ENUM('None','Senior Citizen','PWD') NOT NULL DEFAULT 'None',
  `DiscountPercent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `DiscountAmount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `FinalAmount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `BillingDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Status` ENUM('Unpaid','Partially Paid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid',
  PRIMARY KEY (`BillingID`),
  UNIQUE KEY `uq_billing_consultation` (`ConsultationID`),
  KEY `idx_billing_patient_status` (`PatientID`,`Status`),
  CONSTRAINT `fk_billing_consultation` FOREIGN KEY (`ConsultationID`) REFERENCES `consultations` (`ConsultationID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_billing_patient` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `PaymentID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `BillingID` INT UNSIGNED NOT NULL,
  `AmountPaid` DECIMAL(10,2) NOT NULL,
  `PaymentMethod` ENUM('Cash','GCash','Credit Card','Debit Card') NOT NULL,
  `ReferenceNo` VARCHAR(100) DEFAULT NULL,
  `PaymentDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ReceivedBy` INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`PaymentID`),
  KEY `idx_payments_billing` (`BillingID`),
  KEY `idx_payments_received_by` (`ReceivedBy`),
  CONSTRAINT `fk_payments_billing` FOREIGN KEY (`BillingID`) REFERENCES `billing` (`BillingID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`ReceivedBy`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `auditlogs` (
  `LogID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `UserID` INT UNSIGNED DEFAULT NULL,
  `Action` VARCHAR(255) NOT NULL,
  `TableAffected` VARCHAR(100) DEFAULT NULL,
  `RecordID` INT UNSIGNED DEFAULT NULL,
  `LogDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`LogID`),
  KEY `idx_auditlogs_user_date` (`UserID`,`LogDate`),
  KEY `idx_auditlogs_record` (`TableAffected`,`RecordID`),
  CONSTRAINT `fk_auditlogs_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add real password hashes from PHP password_hash() before enabling login.
-- Example: INSERT INTO users (Username, PasswordHash, FirstName, LastName, Role)
-- VALUES ('admin', '$2y$10$replace_with_a_real_hash', 'System', 'Administrator', 'Admin');
