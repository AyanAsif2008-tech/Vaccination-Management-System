-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2026 at 10:59 PM
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
-- Database: `vaccination_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Admin_ID`, `Username`, `Password`, `Name`, `Email`, `Role`) VALUES
(1, 'admin', 'admin123', 'System Administrator', 'admin@gmail.com', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `booking_appointment`
--

CREATE TABLE `booking_appointment` (
  `Booking_ID` int(11) NOT NULL,
  `Booking_Date` date NOT NULL,
  `Appointment_Date` date NOT NULL,
  `Status` varchar(30) DEFAULT 'Pending',
  `Approval_Date` date DEFAULT NULL,
  `Child_ID` int(11) NOT NULL,
  `Hospital_ID` int(11) NOT NULL,
  `Vaccine_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_appointment`
--

INSERT INTO `booking_appointment` (`Booking_ID`, `Booking_Date`, `Appointment_Date`, `Status`, `Approval_Date`, `Child_ID`, `Hospital_ID`, `Vaccine_ID`) VALUES
(1, '2026-09-09', '2026-09-10', 'Approved', '2026-09-09', 5, 7, 9);

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

CREATE TABLE `child` (
  `Child_ID` int(11) NOT NULL,
  `Child_Name` varchar(100) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Date_Of_Birth` date NOT NULL,
  `Address` text DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Parent_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child`
--

INSERT INTO `child` (`Child_ID`, `Child_Name`, `Gender`, `Date_Of_Birth`, `Address`, `Notes`, `Parent_ID`) VALUES
(5, 'rafay', 'Male', '2020-01-03', 'lyari,karachi', 'flu allergy and fever', 4),
(6, 'yasir', 'Male', '2024-02-02', 'sunrise appartment near do talwaar clifton', 'allergies', 5),
(7, 'ayan sahil', 'Male', '2025-02-01', 'kemari', 'shemale', 4);

-- --------------------------------------------------------

--
-- Table structure for table `hospital`
--

CREATE TABLE `hospital` (
  `Hospital_ID` int(11) NOT NULL,
  `Hospital_Name` varchar(150) NOT NULL,
  `Address` text NOT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospital`
--

INSERT INTO `hospital` (`Hospital_ID`, `Hospital_Name`, `Address`, `Location`, `Phone`, `Email`, `Username`, `Password`, `Status`) VALUES
(6, 'Jinnah Hospital', 'JPMC Saddar Road', 'Karachi', '02111777268', 'jinnah@gmail.org', 'Jinnah_Hospital', 'jinnah123', 'Approved'),
(7, 'Ziauddin Hospital', 'Clifton', 'Karachi', '02145897645', 'ziauddin@gmail.org', 'Zia_Uddin', '1234567', 'Approved'),
(9, 'Holy Family Hospital', 'Saddar Town', 'Karachi', '02154897636', 'holyfamily@gmail.com', 'holy_family456', '$2y$10$Mq9doAs2.t2/mKFJwaOoa.RPOarIASoauw2Ye1qzzWgonl/9sYK6.', 'Approved'),
(10, 'Burhani Hospital', 'I.I chundrigarh road', 'karachi', '02154789645', 'burhani@gmail.com', 'burhani458', '$2y$10$lfZWMtUL0QiaOt1XJxZ3M.jFzS4HEfzcTvJZu3rQIdiIJ0XZQgh9u', 'Approved'),
(11, 'South City Hospital', 'baldia town', 'karachi', '02136546894', 'southcity@gmail.com', 'south_city356', '$2y$10$njxRgA7W9j7CnYq14t3xm.OPqDIRxElzOqlP7zWhJORGXzi2WQ7nm', 'Approved'),
(12, 'Park Lane Hospital', 'clifton', 'karachi', '02145231569', 'parklain@gmail.com', 'Park_Lane1708', '$2y$10$beeC4CJDvXerpg9gfPaxgOJNpJNbSqY8vc2FRPLX5lTPiRmjQsR5G', 'Approved'),
(13, 'Kuttiyan Memon Hospital', 'Kharadar, Lyari', 'Karachi', '02198765432', 'ayanasifmemon08@gmail.com', 'kuttiyana_Memon749', '$2y$10$Z4c2/I2JKHL0o/CKp/H.u.GlE3wxGD2QRlMb73EJxpvXuyCAbGc0K', 'Approved'),
(16, 'batwa Hospital', 'near Kpt football ground', 'Karachi', '02198765432', 'batwa@gmail.com', 'batwa_Hospital', '$2y$10$JCtYCvMafAApAEbQH8eCV.LvbeShcodYlsRbBoOhoOW.ioVzUm9xi', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `parent`
--

CREATE TABLE `parent` (
  `Parent_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent`
--

INSERT INTO `parent` (`Parent_ID`, `Name`, `Email`, `Phone`, `Username`, `Password`, `Address`) VALUES
(4, 'Sahil Khan', 'sahil@gmail.com', '+923142398110', 'sahil_khan', '$2y$10$vbCEmf7BrqtRi7bDisvEAezCBQGQrNi52GfETUeE4jHc5/.MkwE2K', 'near sunrise appartment'),
(5, 'Rafay Patni', 'rafay@gmail.com', '+923009231346`', 'Rafay_Patni', '$2y$10$7BEMPdgvBPsx1VO3hjuRo.bkpOqp0A792spbZ1IvM.qqYj/pO8BzO', 'near aptech shahra e faisal'),
(6, 'Ayan Khan', 'ayan.khan@gmail.com', '+92 3001234567', 'ayankhan99', '$2y$10$02mZJZ3ChaIc4xk0hFRUDuwAu7qDDm3EVzVLJDV5moS3C7unbjfJ2', 'Block 5, Gulshan-e-Iqbal, Karachi');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_record`
--

CREATE TABLE `vaccination_record` (
  `Record_ID` int(11) NOT NULL,
  `Vaccinated_Date` date NOT NULL,
  `Status` varchar(30) DEFAULT 'Vaccinated',
  `Remarks` text DEFAULT NULL,
  `Booking_ID` int(11) NOT NULL,
  `Child_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccination_record`
--

INSERT INTO `vaccination_record` (`Record_ID`, `Vaccinated_Date`, `Status`, `Remarks`, `Booking_ID`, `Child_ID`) VALUES
(1, '2026-09-09', 'Vaccinated', 'All Good', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `vaccine`
--

CREATE TABLE `vaccine` (
  `Vaccine_ID` int(11) NOT NULL,
  `Vaccine_Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Age_Group` varchar(50) DEFAULT NULL,
  `Stock_Status` varchar(50) DEFAULT 'Available',
  `Hospital_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccine`
--

INSERT INTO `vaccine` (`Vaccine_ID`, `Vaccine_Name`, `Description`, `Age_Group`, `Stock_Status`, `Hospital_ID`) VALUES
(9, 'covid vaccine', 'safe', '4 months', 'Available', 7),
(10, 'tuberclosis', '', '4 months', 'Available', 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `booking_appointment`
--
ALTER TABLE `booking_appointment`
  ADD PRIMARY KEY (`Booking_ID`),
  ADD KEY `Child_ID` (`Child_ID`),
  ADD KEY `Hospital_ID` (`Hospital_ID`),
  ADD KEY `Vaccine_ID` (`Vaccine_ID`);

--
-- Indexes for table `child`
--
ALTER TABLE `child`
  ADD PRIMARY KEY (`Child_ID`),
  ADD KEY `Parent_ID` (`Parent_ID`);

--
-- Indexes for table `hospital`
--
ALTER TABLE `hospital`
  ADD PRIMARY KEY (`Hospital_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `parent`
--
ALTER TABLE `parent`
  ADD PRIMARY KEY (`Parent_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `vaccination_record`
--
ALTER TABLE `vaccination_record`
  ADD PRIMARY KEY (`Record_ID`),
  ADD KEY `Booking_ID` (`Booking_ID`),
  ADD KEY `Child_ID` (`Child_ID`);

--
-- Indexes for table `vaccine`
--
ALTER TABLE `vaccine`
  ADD PRIMARY KEY (`Vaccine_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_appointment`
--
ALTER TABLE `booking_appointment`
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `child`
--
ALTER TABLE `child`
  MODIFY `Child_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hospital`
--
ALTER TABLE `hospital`
  MODIFY `Hospital_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `parent`
--
ALTER TABLE `parent`
  MODIFY `Parent_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vaccination_record`
--
ALTER TABLE `vaccination_record`
  MODIFY `Record_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vaccine`
--
ALTER TABLE `vaccine`
  MODIFY `Vaccine_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_appointment`
--
ALTER TABLE `booking_appointment`
  ADD CONSTRAINT `booking_appointment_ibfk_1` FOREIGN KEY (`Child_ID`) REFERENCES `child` (`Child_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_appointment_ibfk_2` FOREIGN KEY (`Hospital_ID`) REFERENCES `hospital` (`Hospital_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_appointment_ibfk_3` FOREIGN KEY (`Vaccine_ID`) REFERENCES `vaccine` (`Vaccine_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `child_ibfk_1` FOREIGN KEY (`Parent_ID`) REFERENCES `parent` (`Parent_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vaccination_record`
--
ALTER TABLE `vaccination_record`
  ADD CONSTRAINT `vaccination_record_ibfk_1` FOREIGN KEY (`Booking_ID`) REFERENCES `booking_appointment` (`Booking_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vaccination_record_ibfk_2` FOREIGN KEY (`Child_ID`) REFERENCES `child` (`Child_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
