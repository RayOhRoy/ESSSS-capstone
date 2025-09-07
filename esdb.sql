-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2025 at 06:48 PM
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
  `ProjectID` varchar(10) NOT NULL,
  `DocumentID` varchar(10) NOT NULL,
  `Status` enum('CREATED','VIEWED','MODIFIED','DELETED','RETRIEVED','RETURNED','UPLOADED') NOT NULL,
  `EmployeeID` varchar(10) NOT NULL,
  `Time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AddressID` varchar(10) NOT NULL,
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
  `ProjectID` varchar(10) NOT NULL,
  `DigitalLocation` varchar(255) DEFAULT NULL,
  `DocumentStatus` enum('STORED','RELEASED') DEFAULT NULL,
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
('ESSSS0000', 'Felipe', 'Reo Roi', 'rayohsmurf@gmail.com', '$2y$10$N4crIaDWNknpnL90zVq6keHnTbof4ag.0LjcPaaTNWQosqj6Hdj8O', 'Chief Operating Officer', 'Admin', 'Active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `ProjectID` varchar(10) NOT NULL,
  `AddressID` varchar(10) NOT NULL,
  `LotNo` varchar(10) NOT NULL,
  `ClientLName` varchar(50) NOT NULL,
  `ClientFName` varchar(50) NOT NULL,
  `SurveyType` enum('Relocation Survey','Verification Survey','Subdivision Survey','Consolidation Survey','Topographic Survey','AS-Built Survey','Sketch Plan / Vicinity Map','Land Titling / Transfer','Real Estate') NOT NULL,
  `DigitalLocation` varchar(255) NOT NULL,
  `SurveyStartDate` date NOT NULL,
  `SurveyEndDate` date NOT NULL,
  `Agent` varchar(255) NOT NULL,
  `RequestType` enum('For Approval','Sketch Plan','Title') NOT NULL,
  `Approval` enum('LRA','PSD','CSD') DEFAULT NULL,
  `ApprovalStatus` enum('PRINT','APPROVAL','ENTRY') NOT NULL,
  `ApprovalStartDate` date NOT NULL,
  `ApprovalEndDate` date NOT NULL,
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
  ADD CONSTRAINT `fk_document_project` FOREIGN KEY (`ProjectID`) REFERENCES `project` (`ProjectID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk_project_address` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
