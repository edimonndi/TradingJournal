-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 04, 2024 at 10:54 PM
-- Server version: 8.0.31
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trade_journal`
--

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE `entries` (
  `id` int NOT NULL,
  `date` date NOT NULL,
  `trade` text COLLATE utf8mb4_general_ci NOT NULL,
  `strategy` text COLLATE utf8mb4_general_ci NOT NULL,
  `mistakes` text COLLATE utf8mb4_general_ci,
  `tags` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `win` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entries`
--

INSERT INTO `entries` (`id`, `date`, `trade`, `strategy`, `mistakes`, `tags`, `win`) VALUES
(1, '2024-12-04', 'e bana kit trade ', 'me kit strategji', 'i bana kto gabime', 'swing trading', 1),
(2, '2024-12-04', 'e bana kit trade ', 'me kit strategjhi', ' me kto gabime', ' scalpin', 0),
(3, '2024-12-04', 'ni trade tshpejt crypto', 'strategji tlodht', 'ni gabim', 'swing', 1),
(4, '2024-12-04', 'trade test', 'strategy test', 'mistake test', 'scalping test', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
