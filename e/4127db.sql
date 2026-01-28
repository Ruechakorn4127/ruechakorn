-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 10:29 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `4127db`
--

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `a_id` int(6) NOT NULL,
  `a_name` varchar(255) NOT NULL,
  `a_phone` varchar(255) NOT NULL,
  `a_email` varchar(255) NOT NULL,
  `a_birthday` date NOT NULL,
  `a_address` varchar(255) NOT NULL,
  `a_education` varchar(255) NOT NULL,
  `a_major` varchar(255) NOT NULL,
  `a_institution` varchar(255) NOT NULL,
  `a_graduation_year` int(4) NOT NULL,
  `a_position` varchar(255) NOT NULL,
  `a_experience` int(4) NOT NULL,
  `a_salary` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`a_id`, `a_name`, `a_phone`, `a_email`, `a_birthday`, `a_address`, `a_education`, `a_major`, `a_institution`, `a_graduation_year`, `a_position`, `a_experience`, `a_salary`) VALUES
(1, 'สุดหล่อ', '0630217119', 'leauchakorn123@gmail.com', '2005-02-04', '127 บุรีรัมย์', 'ปริญญาตรี', 'คอม', 'มมส', 2011, 'พนักงานบัญชี', 1, '25000-30000'),
(2, 'สุดหล่อ', '0630217119', 'leauchakorn123@gmail.com', '2005-02-09', '127 บรีรัมย์', 'ปริญญาตรี', 'คอม', 'มมส', 2013, 'โปรแกรมเมอร์', 2, '50000'),
(3, 'สุดหล่อ', '0630217119', 'leauchakorn123@gmail.com', '2005-02-09', '127 บรีรัมย์', 'ปริญญาตรี', 'คอม', 'มมส', 2013, 'โปรแกรมเมอร์', 2, '50000');

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `r_id` int(7) NOT NULL,
  `r_name` varchar(255) NOT NULL,
  `r_phone` varchar(255) NOT NULL,
  `r_height` int(3) NOT NULL,
  `r_adress` text NOT NULL,
  `r_birthday` date NOT NULL,
  `r_color` varchar(100) NOT NULL,
  `r_major` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `register`
--

INSERT INTO `register` (`r_id`, `r_name`, `r_phone`, `r_height`, `r_adress`, `r_birthday`, `r_color`, `r_major`) VALUES
(1, 'ฤชากร น้คราภิบาล', '', 0, '', '0000-00-00', '', ''),
(9, 'ฤชากร นัคราภิบ่ลwdsa', '0630217119', 0, '', '0000-00-00', '', ''),
(10, 'ฤชากร นัคราภิบ่ลwdsawdsa', '123123444', 0, '', '0000-00-00', '', ''),
(11, 'ฤชากร นัคราภิบ่ลwdsawdsa', '123123444', 199, '123 sdaw 123ghgh', '0000-00-00', '#0d6efd', 'การจัดการ'),
(12, 'ฤชากร นัคราภิบ่ลwdsawdsa sudlor', '0001999455', 188, '123 west 321 east', '0000-00-00', '#0d6efd', 'คอมพิวเตอร์ธุรกิจ'),
(13, 'ต่อ สุดหล่อ', '0817251425', 199, 'ๅ/-หกฟไกฟไก', '2005-02-04', '#2d3849', 'การจัดการ'),
(14, 'ฤชากร นัคราภิบ่ล', '0630217119', 0, '123', '2004-01-28', '', '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`r_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `a_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `register`
--
ALTER TABLE `register`
  MODIFY `r_id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
