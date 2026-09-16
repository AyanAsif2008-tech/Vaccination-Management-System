-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2026 at 02:00 PM
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
(2, '2026-09-14', '2026-09-14', 'Approved', '2026-09-14', 9, 19, 11),
(3, '2026-09-16', '2026-09-16', 'Approved', '2026-09-16', 11, 19, 11);

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
(9, 'Fariha Tariq', 'Female', '2024-08-12', 'House A-12, Block 4, Gulshan-e-Iqbal, Karachi', '', 8),
(11, 'Sahil Tariq', 'Male', '2023-10-20', 'House A-12, Block 4, Gulshan-e-Iqbal, Karachi', '', 8);

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
(18, 'Aga Khan University Hospital', 'Stadium Road', 'Karachi', '02111911911', 'agakhan@gmail.com', 'aga_khan749', '$2y$10$BCko9wjygoMNY2g10YzUu.GndHjEagmzPREAip/C5kdlQch9byPC.', 'Approved'),
(19, 'Liaquat National Hospital', 'Gulshan-e-Iqbal', 'Karachi', '02134412000', 'liaquat@gmail.com', 'liaquat_946', '$2y$10$umeNGp8az9sLHeP7zNat0.lZasRs3kDYv5sGdbLyGu.dl0c1gJD2q', 'Approved'),
(20, 'Ziauddin Hospital', 'North Nazimabad', 'Karachi', '02136648237', 'ziauddin@gmail.com', 'ziauddin_738', '$2y$10$H9K460BKogts5OBtK191hOIunXs13JUwOoNZd3lwwT/TXw0CG/ZHm', 'Approved'),
(21, 'Patel Hospital', 'Gulshan-e-Iqbal', 'Karachi', '02134968661', 'ayanasifmemon08@gmail.com', 'patel_297', '$2y$10$.roiEOHe.X0FHLabJFB6I.r8qlMyP.JfjDBvbMY/2g1ppJccJsRbO', 'Approved'),
(22, 'Indus Hospital', 'Korangi', 'Karachi', '02111111880', 'indus@gmail.com', 'indus_647', '$2y$10$pn4wpDtJaYwrGBcLgxH2tucNf4tz6a8.M1MEE4MPlz2T2GB7SJFb2', 'Approved'),
(23, 'Jinnah Hospital', 'Rafiqui Sarwar Shaheed Road', 'Karachi', '02195684654', 'jinnah@gmail.com', 'jinnah_597', 'jinnah123', 'Approved');

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
(8, 'Muhammad Tariq', 'tariq@gmail.com', '+923001234567', 'tariq_258', '$2y$10$HQazNIdIpFETlE8cJ.IYAeq.C6GM2hbK.VQhUXArb6r1iot5I5W/2', 'House A-12, Block 4, Gulshan-e-Iqbal, Karachi'),
(11, 'Asif', 'asif@gmail.com', '+923001234567', 'asif_924', '$2y$10$/2Qsu0SB9d/BNwsrVMSZWujMwRJADfDKd82RKcmo8I0MpZ2BswALq', '');

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
(2, '2026-09-14', 'Vaccinated', 'be careful', 2, 9),
(3, '2026-09-16', 'Vaccinated', '', 3, 11);

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
(11, 'covid vaccine', 'safest vaccine for covid in karachi', '1-4 years', 'Limited', 19);

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
  MODIFY `Booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `child`
--
ALTER TABLE `child`
  MODIFY `Child_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hospital`
--
ALTER TABLE `hospital`
  MODIFY `Hospital_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `parent`
--
ALTER TABLE `parent`
  MODIFY `Parent_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vaccination_record`
--
ALTER TABLE `vaccination_record`
  MODIFY `Record_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vaccine`
--
ALTER TABLE `vaccine`
  MODIFY `Vaccine_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
