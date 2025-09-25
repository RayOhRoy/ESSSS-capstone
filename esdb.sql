-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 07:23 PM
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
-- Database: `esdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `ActivityLogID` varchar(10) NOT NULL,
  `ProjectID` varchar(20) NOT NULL,
  `DocumentID` varchar(10) NOT NULL,
  `Status` enum('CREATED','VIEWED','MODIFIED','DELETED','RETRIEVED','RETURNED','UPLOADED') NOT NULL,
  `EmployeeID` varchar(10) NOT NULL,
  `Time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`ActivityLogID`, `ProjectID`, `DocumentID`, `Status`, `EmployeeID`, `Time`) VALUES
('ACT-001', 'CAL-01-001-TOP', '', 'CREATED', 'ES0000', '2025-09-20 04:47:41'),
('ACT-002', 'CAL-01-001-TOP', 'DOC-00001', 'UPLOADED', 'ES0000', '2025-09-20 04:47:41'),
('ACT-003', 'CAL-01-001-TOP', 'DOC-00002', 'UPLOADED', 'ES0000', '2025-09-20 04:47:41'),
('ACT-004', 'HAG-01-001-SUB', '', 'CREATED', 'ES0000', '2025-09-20 04:48:41'),
('ACT-005', 'HAG-01-001-SUB', 'DOC-00003', 'UPLOADED', 'ES0000', '2025-09-20 04:48:41'),
('ACT-006', 'HAG-01-001-SUB', 'DOC-00004', 'UPLOADED', 'ES0000', '2025-09-20 04:48:41'),
('ACT-007', 'CAL-01-001-TOP', '', 'MODIFIED', 'ES0000', '2025-09-21 15:52:44'),
('ACT-008', 'CAL-01-001-TOP', '', 'MODIFIED', 'ES0000', '2025-09-21 15:55:41'),
('ACT-009', 'CAL-01-001-TOP', '', 'MODIFIED', 'ES0000', '2025-09-21 15:56:03'),
('ACT-010', 'HAG-01-001-SUB', '', 'MODIFIED', 'ES0000', '2025-09-21 15:56:18'),
('ACT-011', 'CAL-01-001-TOP', '', 'MODIFIED', 'ES0000', '2025-09-21 16:05:36'),
('ACT-012', 'CAL-01-002-SKE', '', 'CREATED', 'ES0000', '2025-09-24 16:11:38'),
('ACT-013', 'CAL-01-002-SKE', 'DOC-00005', 'UPLOADED', 'ES0000', '2025-09-24 16:11:38'),
('ACT-014', 'CAL-01-002-SKE', 'DOC-00006', 'UPLOADED', 'ES0000', '2025-09-24 16:11:38'),
('ACT-015', 'CAL-01-002-SKE', 'DOC-00007', 'UPLOADED', 'ES0000', '2025-09-24 16:11:38'),
('ACT-016', 'HAG-01-001-TOP', '', 'CREATED', 'ES0000', '2025-09-24 16:15:15'),
('ACT-017', 'HAG-01-001-TOP', 'DOC-00008', 'UPLOADED', 'ES0000', '2025-09-24 16:15:15'),
('ACT-018', 'CAL-01-003-ASB', '', 'CREATED', 'ES0000', '2025-09-24 16:16:40'),
('ACT-019', 'CAL-01-003-ASB', 'DOC-00009', 'UPLOADED', 'ES0000', '2025-09-24 16:16:40'),
('ACT-020', 'CAL-01-003-ASB', 'DOC-00010', 'UPLOADED', 'ES0000', '2025-09-24 16:16:40'),
('ACT-021', 'HAG-01-002-TOP', '', 'CREATED', 'ES0000', '2025-09-24 16:20:12'),
('ACT-022', 'HAG-01-002-TOP', 'DOC-00011', 'UPLOADED', 'ES0000', '2025-09-24 16:20:12'),
('ACT-023', 'HAG-01-002-TOP', 'DOC-00012', 'UPLOADED', 'ES0000', '2025-09-24 16:20:12'),
('ACT-024', 'HAG-01-002-TOP', 'DOC-00013', 'UPLOADED', 'ES0000', '2025-09-24 16:20:12'),
('ACT-025', 'CAL-01-003-ASB', '', 'MODIFIED', 'ES0000', '2025-09-24 16:23:24'),
('ACT-026', 'CAL-01-003-ASB', '', 'MODIFIED', 'ES0000', '2025-09-24 16:24:43'),
('ACT-027', 'HAG-01-002-TOP', '', 'MODIFIED', 'ES0000', '2025-09-24 16:30:39'),
('ACT-028', 'HAG-01-002-TOP', '', 'MODIFIED', 'ES0000', '2025-09-24 16:31:34'),
('ACT-029', '', '', 'MODIFIED', 'ES0000', '2025-09-24 16:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AddressID` varchar(20) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Barangay` varchar(50) NOT NULL,
  `Municipality` varchar(50) NOT NULL,
  `Province` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `DocumentID` varchar(10) NOT NULL,
  `DocumentName` varchar(50) NOT NULL,
  `DocumentType` varchar(255) NOT NULL,
  `ProjectID` varchar(20) NOT NULL,
  `DigitalLocation` varchar(255) DEFAULT NULL,
  `DocumentStatus` varchar(20) DEFAULT NULL,
  `DocumentQR` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` varchar(10) NOT NULL,
  `EmpLName` varchar(50) NOT NULL,
  `EmpFName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `JobPosition` enum('Chief Operating Officer','Secretary','Compliance Officer','CAD Operator') NOT NULL,
  `AccountType` enum('Admin','User') NOT NULL,
  `AccountStatus` enum('Active','Inactive') NOT NULL,
  `PasswordCode` varchar(6) DEFAULT NULL,
  `CodeExpiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeID`, `EmpLName`, `EmpFName`, `Email`, `Password`, `JobPosition`, `AccountType`, `AccountStatus`, `PasswordCode`, `CodeExpiry`) VALUES
('ES0000', 'Felipe', 'Reo Roi', 'rayohsmurf@gmail.com', '$2y$10$ITufGcIu4EVh7NR.oVqrTeVHG3OVJXzkPi6ByHVyHNmuCNyRKrDaK', 'Chief Operating Officer', 'Admin', 'Active', NULL, NULL),
('ES0001', 'Balatayo', 'Leila Anne', 'benchudgugu@gmail.com', '$2y$10$Qx1FpqfzKMiJnvgM2ULZo.Be0Ov6iEd4vwlbAJlqh.3cC6dnsOsbS', 'Compliance Officer', 'User', 'Active', '257710', '2025-09-19 06:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `ProjectID` varchar(20) NOT NULL,
  `AddressID` varchar(20) NOT NULL,
  `LotNo` varchar(50) NOT NULL,
  `ClientLName` varchar(50) NOT NULL,
  `ClientFName` varchar(50) NOT NULL,
  `SurveyType` enum('Relocation Survey','Verification Survey','Subdivision Survey','Consolidation Survey','Topographic Survey','AS-Built Survey','Sketch Plan / Vicinity Map','Land Titling / Transfer','Real Estate') NOT NULL,
  `DigitalLocation` varchar(255) NOT NULL,
  `SurveyStartDate` date NOT NULL,
  `SurveyEndDate` date DEFAULT NULL,
  `Agent` varchar(255) DEFAULT NULL,
  `RequestType` enum('For Approval','Sketch Plan') NOT NULL,
  `Approval` enum('LRA','PSD','CSD') DEFAULT NULL,
  `ProjectStatus` varchar(20) NOT NULL,
  `StorageStatus` varchar(20) DEFAULT NULL,
  `ProjectQR` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`ActivityLogID`);

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`AddressID`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`DocumentID`),
  ADD KEY `fk_document_project` (`ProjectID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`ProjectID`),
  ADD KEY `fk_project_address` (`AddressID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_document_project` FOREIGN KEY (`ProjectID`) REFERENCES `project` (`ProjectID`) ON DELETE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk_project_address` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
