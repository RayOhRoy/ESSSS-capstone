-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 03:24 PM
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
  `ActivityLogID` varchar(50) NOT NULL,
  `ProjectID` varchar(20) NOT NULL,
  `DocumentID` varchar(10) NOT NULL,
  `Status` enum('CREATED','VIEWED','MODIFIED','DELETED','RETRIEVED','STORED','UPLOADED') NOT NULL,
  `EmployeeID` varchar(10) NOT NULL,
  `Time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`ActivityLogID`, `ProjectID`, `DocumentID`, `Status`, `EmployeeID`, `Time`) VALUES
('ACT-001', 'HAG-01-001-SUB', '', 'CREATED', 'ES0000', '2025-11-02 05:39:38'),
('ACT-002', 'HAG-01-001-SUB', 'DOC-00001', 'UPLOADED', 'ES0000', '2025-11-02 05:39:38'),
('ACT-003', 'HAG-01-001-SUB', 'DOC-00002', 'UPLOADED', 'ES0000', '2025-11-02 05:39:38'),
('ACT-004', 'HAG-01-001-SUB', 'DOC-00003', 'UPLOADED', 'ES0000', '2025-11-02 05:39:38'),
('ACT-005', 'HAG-01-002-VER', '', 'CREATED', 'ES0000', '2025-11-02 07:37:57'),
('ACT-006', 'HAG-01-002-VER', 'DOC-00004', 'UPLOADED', 'ES0000', '2025-11-02 07:37:57'),
('ACT-007', 'HAG-01-002-VER', 'DOC-00005', 'UPLOADED', 'ES0000', '2025-11-02 07:37:57'),
('ACT-008', 'HAG-01-001-SUB', '', 'RETRIEVED', 'ES0000', '2025-11-02 12:21:09'),
('ACT-009', 'HAG-01-001-SUB', 'DOC-00001', 'RETRIEVED', 'ES0000', '2025-11-02 12:21:09'),
('ACT-010', 'HAG-01-001-SUB', 'DOC-00002', 'RETRIEVED', 'ES0000', '2025-11-02 12:21:09'),
('ACT-011', 'HAG-01-001-SUB', 'DOC-00003', 'RETRIEVED', 'ES0000', '2025-11-02 12:21:09'),
('ACT-012', 'HAG-01-001-SUB', '', 'STORED', 'ES0000', '2025-11-02 12:21:30'),
('ACT-013', 'HAG-01-001-SUB', 'DOC-00001', 'STORED', 'ES0000', '2025-11-02 12:21:30'),
('ACT-014', 'HAG-01-001-SUB', 'DOC-00002', 'STORED', 'ES0000', '2025-11-02 12:21:30'),
('ACT-015', 'HAG-01-001-SUB', 'DOC-00003', 'STORED', 'ES0000', '2025-11-02 12:21:30');

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

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`AddressID`, `Address`, `Barangay`, `Municipality`, `Province`) VALUES
('HAG-001', '', 'San Pascual', 'Hagonoy', 'Bulacan'),
('HAG-002', '', 'Mercado', 'Hagonoy', 'Bulacan');

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

--
-- Dumping data for table `document`
--

INSERT INTO `document` (`DocumentID`, `DocumentName`, `DocumentType`, `ProjectID`, `DigitalLocation`, `DocumentStatus`, `DocumentQR`) VALUES
('DOC-00001', 'HAG-01-001-SUB-Original-Plan', 'Original Plan', 'HAG-01-001-SUB', '../uploads/HAG-01-001/Original-Plan/Original-Plan.pdf', 'Stored', 'uploads/HAG-01-001/Original-Plan/HAG-01-001-Original-Plan-QR.png'),
('DOC-00002', 'HAG-01-001-SUB-Certified-Title', 'Certified Title', 'HAG-01-001-SUB', '../uploads/HAG-01-001/Certified-Title/Certified-Title.pdf', 'Stored', 'uploads/HAG-01-001/Certified-Title/HAG-01-001-Certified-Title-QR.png'),
('DOC-00003', 'HAG-01-001-SUB-Reference-Plan', 'Reference Plan', 'HAG-01-001-SUB', NULL, 'Stored', 'uploads/HAG-01-001/Reference-Plan/HAG-01-001-Reference-Plan-QR.png'),
('DOC-00004', 'HAG-01-002-VER-Original-Plan', 'Original Plan', 'HAG-01-002-VER', NULL, 'Stored', 'uploads/HAG-01-002/Original-Plan/HAG-01-002-Original-Plan-QR.png'),
('DOC-00005', 'HAG-01-002-VER-Certified-Title', 'Certified Title', 'HAG-01-002-VER', NULL, 'Stored', 'uploads/HAG-01-002/Certified-Title/HAG-01-002-Certified-Title-QR.png');

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
('ES0000', 'Felipe', 'Reo Roi', 'rayohsmurf@gmail.com', '$2y$10$3wOzbGo7nAXlVTtLlPlbteWWdN0/DNCgtoIqvQe7vGq89K1JXuttW', 'Chief Operating Officer', 'Admin', 'Active', NULL, NULL),
('ES0001', 'Balatayo', 'Leila Anne', 'rayohroy@gmail.com', '$2y$10$edcGZBIF/.yoKdxJcp/WSuA9YUAfcxg6KTcQa.03nK33XhJ/bXzjW', 'Secretary', 'User', 'Active', '699810', '2025-11-01 11:17:31');

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
-- Dumping data for table `project`
--

INSERT INTO `project` (`ProjectID`, `AddressID`, `LotNo`, `ClientLName`, `ClientFName`, `SurveyType`, `DigitalLocation`, `SurveyStartDate`, `SurveyEndDate`, `Agent`, `RequestType`, `Approval`, `ProjectStatus`, `StorageStatus`, `ProjectQR`) VALUES
('HAG-01-001-SUB', 'HAG-001', 'LOT-123-532', 'FELIPE', 'REO ROI', 'Subdivision Survey', 'uploads/HAG-01-001', '2025-11-02', '0000-00-00', '', 'For Approval', 'PSD', 'COMPLETED', 'Stored', 'uploads/HAG-01-001/HAG-01-001-QR.png'),
('HAG-01-002-VER', 'HAG-002', 'DASD', 'DASDS', 'DSADSD', 'Verification Survey', 'uploads/HAG-01-002', '2025-11-02', '0000-00-00', '', 'For Approval', 'PSD', 'COMPLETED', 'Stored', 'uploads/HAG-01-002/HAG-01-002-QR.png');

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
  ADD CONSTRAINT `fk_document_project` FOREIGN KEY (`ProjectID`) REFERENCES `project` (`ProjectID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk_project_address` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `delete_old_activity_log` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-06 19:22:34' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM activity_log
  WHERE Time < NOW() - INTERVAL 30 DAY$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
