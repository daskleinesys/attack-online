-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: attack-db
-- Generation Time: Jan 24, 2017 at 12:33 PM
-- Server version: 10.1.20-MariaDB-1~jessie
-- PHP Version: 5.6.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attack`
--

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `lastname`, `login`) VALUES
(-1, 'NEUTRAL_COUNTRY', 'NEUTRAL_COUNTRY', 'NEUTRAL_COUNTRY');

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `key`) VALUES
(1, 'red', 'red'),
(2, 'blue', 'blue'),
(3, 'green', 'green'),
(4, 'orange', 'orange'),
(5, 'purple', 'purple'),
(6, 'yellow', 'yellow');

--
-- Dumping data for table `option_types`
--

INSERT INTO `option_types` (`id`, `units`, `countries`) VALUES
(1, 4, 1),
(2, 3, 1),
(3, 2, 1),
(4, 2, 2),
(5, 2, 3),
(6, 2, 4);

--
-- Dumping data for table `phases`
--

INSERT INTO `phases` (`id`, `name`, `key`) VALUES
(1, 'Move Troops', 'landmove'),
(2, 'Move Fleets', 'seamove'),
(3, 'Trade Routes', 'traderoutes'),
(4, 'Arrange Troops', 'troopsmove'),
(5, 'Reinforcements', 'production'),
(21, 'Game Start', 'startgame'),
(22, 'Select Start-Areas', 'selectstart'),
(23, 'Place Fleets', 'setships');

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `key`) VALUES
(1, 'Öl', 'oil'),
(2, 'Transport', 'transport'),
(3, 'Industrie', 'industry'),
(4, 'Mineralien', 'minerals'),
(5, 'Population', 'population');

--
-- Dumping data for table `areas_get_resources`
--

INSERT INTO `areas_get_resources` (`id`, `id_resource`, `res_power`, `economy`, `count`) VALUES
(1, 1, 1, 'poor', 1),
(2, 1, 1, 'weak', 6),
(3, 1, 2, 'weak', 6),
(4, 1, 3, 'normal', 5),
(5, 1, 4, 'strong', 3),
(6, 2, 1, 'poor', 1),
(7, 2, 2, 'poor', 4),
(8, 2, 2, 'weak', 3),
(9, 2, 3, 'weak', 1),
(10, 2, 3, 'normal', 4),
(11, 2, 4, 'strong', 2),
(12, 3, 1, 'poor', 1),
(13, 3, 2, 'poor', 4),
(14, 3, 2, 'weak', 3),
(15, 3, 3, 'weak', 2),
(16, 3, 3, 'normal', 3),
(17, 3, 4, 'strong', 2),
(18, 4, 1, 'poor', 1),
(19, 4, 2, 'poor', 4),
(20, 4, 2, 'weak', 3),
(21, 4, 3, 'weak', 2),
(22, 4, 3, 'normal', 3),
(23, 4, 4, 'strong', 2),
(24, 5, 1, 'poor', 14);

--
-- Dumping data for table `start_sets`
--

INSERT INTO `start_sets` (`id`, `players`) VALUES
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 6),
(8, 6),
(9, 6),
(10, 6),
(11, 6),
(12, 6);

--
-- Dumping data for table `start_set_has_areas`
--

INSERT INTO `start_set_has_areas` (`id`, `id_area`, `id_optiontype`, `id_set`, `option_group`) VALUES
(1, 24, 1, 1, 1),
(2, 3, 2, 1, 2),
(3, 79, 3, 1, 3),
(4, 36, 4, 1, 4),
(5, 37, 4, 1, 4),
(6, 38, 4, 1, 4),
(7, 61, 4, 1, 5),
(8, 62, 4, 1, 5),
(9, 63, 4, 1, 5),
(10, 28, 1, 2, 1),
(11, 6, 2, 2, 2),
(12, 68, 4, 2, 3),
(13, 69, 4, 2, 3),
(14, 66, 4, 2, 3),
(15, 17, 4, 2, 4),
(16, 19, 4, 2, 4),
(17, 20, 4, 2, 4),
(18, 54, 3, 2, 5),
(19, 55, 3, 2, 5),
(20, 10, 1, 3, 1),
(21, 80, 3, 3, 3),
(22, 23, 2, 3, 2),
(23, 27, 2, 3, 2),
(24, 40, 4, 3, 4),
(25, 41, 4, 3, 4),
(26, 42, 4, 3, 4),
(27, 44, 4, 3, 5),
(28, 47, 4, 3, 5),
(29, 50, 4, 3, 5),
(30, 25, 1, 4, 1),
(31, 22, 1, 4, 1),
(32, 49, 2, 4, 2),
(33, 2, 3, 4, 3),
(34, 15, 4, 4, 4),
(35, 16, 4, 4, 4),
(36, 14, 4, 4, 4),
(37, 78, 4, 4, 5),
(38, 76, 4, 4, 5),
(39, 77, 4, 4, 5),
(40, 26, 1, 5, 1),
(41, 43, 2, 5, 2),
(42, 48, 2, 5, 2),
(43, 12, 4, 5, 3),
(44, 13, 4, 5, 3),
(45, 65, 5, 5, 4),
(46, 64, 5, 5, 4),
(47, 67, 5, 5, 4),
(48, 70, 5, 5, 4),
(49, 9, 1, 6, 1),
(50, 11, 1, 6, 1),
(51, 75, 2, 6, 2),
(52, 57, 4, 6, 3),
(53, 58, 4, 6, 3),
(54, 56, 4, 6, 3),
(55, 35, 5, 6, 4),
(56, 30, 5, 6, 4),
(57, 34, 5, 6, 4),
(58, 31, 5, 6, 4),
(59, 33, 5, 6, 4),
(60, 24, 1, 7, 1),
(61, 3, 2, 7, 2),
(62, 79, 3, 7, 3),
(63, 36, 4, 7, 4),
(64, 37, 4, 7, 4),
(65, 38, 4, 7, 4),
(66, 61, 4, 7, 5),
(67, 62, 4, 7, 5),
(68, 63, 4, 7, 5),
(69, 28, 1, 8, 1),
(70, 6, 2, 8, 2),
(71, 68, 4, 8, 3),
(72, 69, 4, 8, 3),
(73, 66, 4, 8, 3),
(74, 17, 4, 8, 4),
(75, 19, 4, 8, 4),
(76, 20, 4, 8, 4),
(77, 54, 3, 8, 5),
(78, 55, 3, 8, 5),
(79, 10, 1, 9, 1),
(80, 80, 3, 9, 3),
(81, 23, 2, 9, 2),
(82, 27, 2, 9, 2),
(83, 40, 4, 9, 4),
(84, 41, 4, 9, 4),
(85, 42, 4, 9, 4),
(86, 44, 4, 9, 5),
(87, 47, 4, 9, 5),
(88, 50, 4, 9, 5),
(89, 25, 1, 10, 1),
(90, 22, 1, 10, 1),
(91, 49, 2, 10, 2),
(92, 2, 3, 10, 3),
(93, 15, 4, 10, 4),
(94, 16, 4, 10, 4),
(95, 14, 4, 10, 4),
(96, 78, 4, 10, 5),
(97, 76, 4, 10, 5),
(98, 77, 4, 10, 5),
(99, 26, 1, 11, 1),
(100, 43, 2, 11, 2),
(101, 48, 2, 11, 2),
(102, 12, 4, 11, 3),
(103, 13, 4, 11, 3),
(104, 65, 5, 11, 4),
(105, 64, 5, 11, 4),
(106, 67, 5, 11, 4),
(107, 70, 5, 11, 4),
(108, 9, 1, 12, 1),
(109, 11, 1, 12, 1),
(110, 75, 2, 12, 2),
(111, 57, 4, 12, 3),
(112, 58, 4, 12, 3),
(113, 56, 4, 12, 3),
(114, 35, 5, 12, 4),
(115, 30, 5, 12, 4),
(116, 34, 5, 12, 4),
(117, 31, 5, 12, 4),
(118, 33, 5, 12, 4);

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `abbreviation`, `price`, `tanksize`, `hitpoints`, `speed`, `id_type`) VALUES
(1, 'Infanterie', 'Inf', 2, NULL, 1, 1, 1),
(2, 'Artillerie', 'Art', 3, NULL, 1, 1, 1),
(3, 'Panzer', 'Pan', 4, NULL, 1, 2, 1),
(4, 'Flieger', 'Flug', 5, NULL, 1, 2, 3),
(5, 'U-Boot(e)', NULL, 3, 4, 2, 1, 2),
(6, 'Zerstörer', NULL, 3, 3, 2, 1, 2),
(7, 'Schlachtschiff(e)', NULL, 5, 7, 4, 1, 2),
(8, 'Flugzeugträger', NULL, 7, 7, 4, 1, 2);

--
-- Dumping data for table `start_ships`
--

INSERT INTO `start_ships` (`id`, `id_unit`, `numberof`, `players`) VALUES
(1, 7, 2, 2),
(2, 6, 4, 2),
(3, 8, 2, 2),
(4, 5, 4, 2),
(5, 7, 2, 6),
(6, 6, 4, 6),
(7, 8, 2, 6),
(8, 5, 4, 6);

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `players`, `id_creator`, `password`, `status`, `id_phase`, `round`, `processing`) VALUES
(0, 'DUMMY', 0, -1, 'dummy', 'done', 21, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
