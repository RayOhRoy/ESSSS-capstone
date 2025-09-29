-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 03:36 PM
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
('ACT-001', 'CAL-01-001-ASB', '', 'CREATED', 'ES0000', '2025-09-25 07:58:18'),
('ACT-002', 'CAL-01-001-ASB', 'DOC-00001', 'UPLOADED', 'ES0000', '2025-09-25 07:58:18'),
('ACT-003', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:04:37'),
('ACT-004', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:04:45'),
('ACT-005', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:04:59'),
('ACT-006', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:05:09'),
('ACT-007', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:08:00'),
('ACT-008', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:08:08'),
('ACT-009', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:08:20'),
('ACT-010', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:08:54'),
('ACT-011', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:09:26'),
('ACT-012', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:09:43'),
('ACT-013', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:10:01'),
('ACT-014', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:12:07'),
('ACT-015', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 08:12:51'),
('ACT-016', 'CAL-01-001-CON', '', 'MODIFIED', 'ES0000', '2025-09-25 08:13:35'),
('ACT-017', 'CAL-01-001-SUB', '', 'MODIFIED', 'ES0000', '2025-09-25 12:00:49'),
('ACT-018', 'CAL-01-001-SKE', '', 'MODIFIED', 'ES0000', '2025-09-25 12:07:38'),
('ACT-019', 'CAL-01-001-REL', '', 'MODIFIED', 'ES0000', '2025-09-25 12:09:52'),
('ACT-020', 'CAL-01-002-CON', '', 'CREATED', 'ES0000', '2025-09-25 12:10:56'),
('ACT-021', 'CAL-01-002-CON', 'DOC-00002', 'UPLOADED', 'ES0000', '2025-09-25 12:10:56'),
('ACT-022', 'CAL-01-002-CON', 'DOC-00003', 'UPLOADED', 'ES0000', '2025-09-25 12:10:56'),
('ACT-023', 'CAL-01-002-CON', 'DOC-00004', 'UPLOADED', 'ES0000', '2025-09-25 12:10:56'),
('ACT-024', 'CAL-01-002-CON', 'DOC-00005', 'UPLOADED', 'ES0000', '2025-09-25 12:10:56'),
('ACT-025', 'CAL-01-002-LAN', '', 'MODIFIED', 'ES0000', '2025-09-25 12:12:06'),
('ACT-026', 'CAL-01-002-LAN', '', 'MODIFIED', 'ES0000', '2025-09-25 12:13:26'),
('ACT-027', 'CAL-01-002-SUB', '', 'MODIFIED', 'ES0000', '2025-09-25 12:22:00'),
('ACT-028', 'CAL-01-002-REA', '', 'MODIFIED', 'ES0000', '2025-09-25 12:22:23'),
('ACT-029', 'CAL-01-002-CON', '', 'MODIFIED', 'ES0000', '2025-09-25 12:24:39'),
('ACT-030', 'CAL-01-002-VER', '', 'MODIFIED', 'ES0000', '2025-09-25 12:25:03'),
('ACT-031', 'CAL-01-002-SUB', '', 'MODIFIED', 'ES0000', '2025-09-25 12:30:24'),
('ACT-032', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:31:53'),
('ACT-033', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:34:13'),
('ACT-034', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:34:19'),
('ACT-035', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:35:02'),
('ACT-036', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:35:08'),
('ACT-037', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:35:14'),
('ACT-038', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:35:39'),
('ACT-039', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:36:18'),
('ACT-040', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:36:25'),
('ACT-041', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:36:32'),
('ACT-042', 'CAL-01-002-CON', '', 'MODIFIED', 'ES0000', '2025-09-25 12:37:07'),
('ACT-043', 'CAL-01-002-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:37:15'),
('ACT-044', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:39:02'),
('ACT-045', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:39:54'),
('ACT-046', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:42:31'),
('ACT-047', 'CAL-01-001-AS-', '', 'MODIFIED', 'ES0000', '2025-09-25 12:43:08'),
('ACT-048', 'CAL-01-001-VER', '', 'MODIFIED', 'ES0000', '2025-09-25 12:44:11'),
('ACT-049', 'CAL-01-001-ASB', '', 'MODIFIED', 'ES0000', '2025-09-25 12:44:17'),
('ACT-050', 'HAG-01-001-VER', '', 'CREATED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-051', 'HAG-01-001-VER', 'DOC-00001', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-052', 'HAG-01-001-VER', 'DOC-00002', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-053', 'HAG-01-001-VER', 'DOC-00003', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-054', 'HAG-01-001-VER', 'DOC-00004', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-055', 'HAG-01-001-VER', 'DOC-00005', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-056', 'HAG-01-001-VER', 'DOC-00006', 'UPLOADED', 'ES0000', '2025-09-25 12:46:13'),
('ACT-057', 'HAG-01-001-ASB', '', 'MODIFIED', 'ES0000', '2025-09-25 12:46:25'),
('ACT-058', 'HAG-01-001-VER', '', 'MODIFIED', 'ES0000', '2025-09-28 12:57:04'),
('ACT-059', 'HAG-01-002-', '', 'CREATED', 'ES0000', '2025-09-28 12:59:19'),
('ACT-060', 'HAG-01-001-TOP', '', 'MODIFIED', 'ES0000', '2025-09-28 13:05:51'),
('ACT-061', 'HAG-01-002-ASB', '', 'CREATED', 'ES0000', '2025-09-28 13:18:10'),
('ACT-062', 'HAG-01-002-ASB', 'DOC-00007', 'UPLOADED', 'ES0000', '2025-09-28 13:18:10'),
('ACT-063', 'HAG-01-003-ASB', '', 'CREATED', 'ES0000', '2025-09-29 13:03:23'),
('ACT-064', 'HAG-01-003-ASB', 'DOC-00008', 'UPLOADED', 'ES0000', '2025-09-29 13:03:23'),
('ACT-065', 'HAG-01-003-ASB', 'DOC-00009', 'UPLOADED', 'ES0000', '2025-09-29 13:03:23'),
('ACT-066', 'CAL-01-001-TOP', '', 'CREATED', 'ES0000', '2025-09-29 13:06:13'),
('ACT-067', 'CAL-01-001-TOP', 'DOC-00010', 'UPLOADED', 'ES0000', '2025-09-29 13:06:13'),
('ACT-068', 'CAL-01-001-TOP', 'DOC-00011', 'UPLOADED', 'ES0000', '2025-09-29 13:06:13');

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
('CAL-001', '', 'Calizon', 'Calumpit', 'Bulacan'),
('CAL-002', '', 'Balite', 'Calumpit', 'Bulacan'),
('CAL-003', '', 'Bugyon', 'Calumpit', 'Bulacan'),
('HAG-001', '', 'Iba', 'Hagonoy', 'Bulacan'),
('HAG-002', '', 'Iba', 'Hagonoy', 'Bulacan'),
('HAG-003', '', 'Carillo', 'Hagonoy', 'Bulacan'),
('HAG-004', '', 'Carillo', 'Hagonoy', 'Bulacan');

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
('DOC-00001', 'HAG-01-001-VER-Original-Plan', 'Original Plan', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Original-Plan/HAG-01-001-VER-Original-Plan-QR.png'),
('DOC-00002', 'HAG-01-001-VER-Title', 'Title', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Title/HAG-01-001-VER-Title-QR.png'),
('DOC-00003', 'HAG-01-001-VER-Reference-Plan', 'Reference Plan', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Reference-Plan/HAG-01-001-VER-Reference-Plan-QR.png'),
('DOC-00004', 'HAG-01-001-VER-Lot-Data', 'Lot Data', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Lot-Data/HAG-01-001-VER-Lot-Data-QR.png'),
('DOC-00005', 'HAG-01-001-VER-Tax-Declaration', 'Tax Declaration', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Tax-Declaration/HAG-01-001-VER-Tax-Declaration-QR.png'),
('DOC-00006', 'HAG-01-001-VER-Blueprint', 'Blueprint', 'HAG-01-001-TOP', NULL, 'Stored', 'uploads/HAG-01-001-VER/Blueprint/HAG-01-001-VER-Blueprint-QR.png'),
('DOC-00007', 'HAG-01-002-ASB-Original-Plan', 'Original Plan', 'HAG-01-002-ASB', '../uploads/HAG-01-002-ASB/Original-Plan/Original-Plan-1.docx', 'Stored', 'uploads/HAG-01-002-ASB/Original-Plan/HAG-01-002-ASB-Original-Plan-QR.png'),
('DOC-00010', 'CAL-01-001-TOP-Cadastral-Map', 'Cadastral Map', 'CAL-01-001-TOP', NULL, 'Stored', 'uploads/CAL-01-001-TOP/Cadastral-Map/CAL-01-001-TOP-Cadastral-Map-QR.png'),
('DOC-00011', 'CAL-01-001-TOP-Technical-Description', 'Technical Description', 'CAL-01-001-TOP', NULL, 'Stored', 'uploads/CAL-01-001-TOP/Technical-Description/CAL-01-001-TOP-Technical-Description-QR.png');

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
('ES0000', 'Felipe', 'Reo Roi', 'rayohsmurf@gmail.com', '$2y$10$qYi0V.5rYhMbQayQNAi5oOKUNect8Y05.98FMAHrFTRlqEp5Ng/5m', 'Chief Operating Officer', 'Admin', 'Active', NULL, NULL),
('ES0001', 'Balatayo', 'Leila Anne', 'benchudgugu@gmail.com', '$2y$10$Qx1FpqfzKMiJnvgM2ULZo.Be0Ov6iEd4vwlbAJlqh.3cC6dnsOsbS', 'Compliance Officer', 'User', 'Active', '921946', '2025-09-28 05:15:25');

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
('CAL-01-001-TOP', 'CAL-003', 'LOT-3123-213123', 'CHIM-CHIM', 'ATHAN', 'Topographic Survey', 'uploads/CAL-01-001-TOP', '2025-09-29', '0000-00-00', '', 'For Approval', 'CSD', 'FOR SIGN', 'Stored', 'uploads/CAL-01-001-TOP/CAL-01-001-TOP-QR.png'),
('HAG-01-001-TOP', 'HAG-001', 'DFSFD', 'DS', 'SFSDFDF', 'Topographic Survey', 'uploads/HAG-01-001-TOP', '2025-09-25', '0000-00-00', '', 'For Approval', 'PSD', 'FOR PRINT', 'Stored', 'uploads/HAG-01-001-TOP/HAG-01-001-TOP-QR.png'),
('HAG-01-002-ASB', 'HAG-003', 'DSADA', 'SDSADS', 'DASDD', 'AS-Built Survey', 'uploads/HAG-01-002-ASB', '2025-09-28', '0000-00-00', '', 'For Approval', 'PSD', 'FOR ENTRY (CSD)', 'Stored', 'uploads/HAG-01-002-ASB/HAG-01-002-ASB-QR.png'),
('HAG-01-003-ASB', 'HAG-004', 'S', 'RERR', 'RERE', 'AS-Built Survey', 'uploads/HAG-01-003-ASB', '2025-09-29', '0000-00-00', '', 'For Approval', 'CSD', 'FOR ENTRY (PSD)', 'Stored', 'uploads/HAG-01-003-ASB/HAG-01-003-ASB-QR.png');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
