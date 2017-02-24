-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: attack-db
-- Generation Time: Dec 27, 2016 at 05:28 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `area_is_adjacent`
--

DROP TABLE IF EXISTS `area_is_adjacent`;
CREATE TABLE `area_is_adjacent` (
  `id` int(11) NOT NULL,
  `id_area1` int(11) NOT NULL,
  `id_area2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `number` int(11) NOT NULL,
  `coords_small` text NOT NULL,
  `x` int(11) NOT NULL DEFAULT '0',
  `y` int(11) NOT NULL DEFAULT '0',
  `x2` int(11) NOT NULL DEFAULT '0',
  `y2` int(11) NOT NULL DEFAULT '0',
  `xres` int(11) NOT NULL DEFAULT '0',
  `yres` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '0',
  `id_type` int(11) NOT NULL,
  `economy` enum('poor','weak','normal','strong','none') NOT NULL DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `key` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `players` int(11) NOT NULL,
  `id_creator` int(11) NOT NULL,
  `password` varchar(40) DEFAULT NULL,
  `status` enum('new','started','running','done') NOT NULL DEFAULT 'new',
  `id_phase` int(11) NOT NULL DEFAULT '21',
  `round` int(11) NOT NULL DEFAULT '0',
  `processing` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_in_game_phase_info`
--

DROP TABLE IF EXISTS `user_in_game_phase_info`;
CREATE TABLE `user_in_game_phase_info` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_phase` int(11) NOT NULL,
  `is_ready` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_is_in_game`
--

DROP TABLE IF EXISTS `user_is_in_game`;
CREATE TABLE `user_is_in_game` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_color` int(11) DEFAULT NULL,
  `money` int(11) NOT NULL DEFAULT '0',
  `id_set` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `option_types`
--

DROP TABLE IF EXISTS `option_types`;
CREATE TABLE `option_types` (
  `id` int(11) NOT NULL,
  `units` int(11) NOT NULL,
  `countries` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `phases`
--

DROP TABLE IF EXISTS `phases`;
CREATE TABLE `phases` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `key` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `key` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `areas_get_resources`
--

DROP TABLE IF EXISTS `areas_get_resources`;
CREATE TABLE `areas_get_resources` (
  `id` int(11) NOT NULL,
  `id_resource` int(11) NOT NULL,
  `res_power` int(11) NOT NULL,
  `economy` enum('poor','weak','normal','strong','none') NOT NULL DEFAULT 'poor',
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `start_set_has_areas`
--

DROP TABLE IF EXISTS `start_set_has_areas`;
CREATE TABLE `start_set_has_areas` (
  `id` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  `id_optiontype` int(11) NOT NULL,
  `id_set` int(11) NOT NULL,
  `option_group` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `start_sets`
--

DROP TABLE IF EXISTS `start_sets`;
CREATE TABLE `start_sets` (
  `id` int(11) NOT NULL,
  `players` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `start_ships`
--

DROP TABLE IF EXISTS `start_ships`;
CREATE TABLE `start_ships` (
  `id` int(11) NOT NULL,
  `id_unit` int(11) NOT NULL,
  `numberof` int(11) NOT NULL,
  `players` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE `types` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `abbreviation` varchar(40) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `tanksize` int(11) DEFAULT NULL,
  `hitpoints` int(11) NOT NULL DEFAULT '1',
  `speed` int(11) NOT NULL DEFAULT '1',
  `id_type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `login` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `status` enum('inactive','active','moderator','admin','deleted') NOT NULL DEFAULT 'inactive',
  `verify` varchar(40) DEFAULT NULL,
  `token` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_areas`
--

DROP TABLE IF EXISTS `game_areas`;
CREATE TABLE `game_areas` (
  `id` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_area` int(11) NOT NULL,
  `id_resource` int(11) DEFAULT NULL,
  `productivity` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_moves`
--

DROP TABLE IF EXISTS `game_moves`;
CREATE TABLE `game_moves` (
  `id` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_phase` int(11) NOT NULL,
  `round` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_move_has_areas`
--

DROP TABLE IF EXISTS `game_move_has_areas`;
CREATE TABLE `game_move_has_areas` (
  `id` int(11) NOT NULL,
  `id_game_move` int(11) NOT NULL,
  `id_game_area` int(11) NOT NULL,
  `step` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_move_has_units`
--

DROP TABLE IF EXISTS `game_move_has_units`;
CREATE TABLE `game_move_has_units` (
  `id` int(11) NOT NULL,
  `id_game_move` int(11) NOT NULL,
  `id_game_unit` int(11) NOT NULL,
  `numberof` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `game_units`
--

DROP TABLE IF EXISTS `game_units`;
CREATE TABLE `game_units` (
  `id` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `tank` int(11) DEFAULT NULL,
  `hitpoints` int(11) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `numberof` int(11) DEFAULT NULL,
  `dive_status` enum('up','diving','silent') DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `id_game_area` int(11) DEFAULT NULL,
  `id_game_area_in_port` int(11) DEFAULT NULL,
  `id_unit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `area_is_adjacent`
--
ALTER TABLE `area_is_adjacent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_area1` (`id_area1`),
  ADD KEY `id_area2` (`id_area2`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`),
  ADD KEY `id_creator` (`id_creator`),
  ADD KEY `id_phase` (`id_phase`);

--
-- Indexes for table `user_in_game_phase_info`
--
ALTER TABLE `user_in_game_phase_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_phase` (`id_phase`),
  ADD UNIQUE KEY `user_in_game_phase` (`id_user`, `id_game`, `id_phase`);

--
-- Indexes for table `user_is_in_game`
--
ALTER TABLE `user_is_in_game`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_color` (`id_color`),
  ADD KEY `id_set` (`id_set`),
  ADD UNIQUE KEY `user_in_game` (`id_user`, `id_game`),
  ADD UNIQUE KEY `color_in_game` (`id_color`, `id_game`),
  ADD UNIQUE KEY `set_in_game` (`id_set`, `id_game`);

--
-- Indexes for table `option_types`
--
ALTER TABLE `option_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phases`
--
ALTER TABLE `phases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `areas_get_resources`
--
ALTER TABLE `areas_get_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_resource` (`id_resource`);

--
-- Indexes for table `start_set_has_areas`
--
ALTER TABLE `start_set_has_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_optiontype` (`id_optiontype`),
  ADD KEY `id_set` (`id_set`);

--
-- Indexes for table `start_sets`
--
ALTER TABLE `start_sets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `start_ships`
--
ALTER TABLE `start_ships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_unit` (`id_unit`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_type` (`id_type`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_UNIQUE` (`login`);

--
-- Indexes for table `game_areas`
--
ALTER TABLE `game_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_resource` (`id_resource`);

--
-- Indexes for table `game_moves`
--
ALTER TABLE `game_moves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_phase` (`id_phase`);

--
-- Indexes for table `game_move_has_areas`
--
ALTER TABLE `game_move_has_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_game_move` (`id_game_move`),
  ADD KEY `id_game_area` (`id_game_area`);

--
-- Indexes for table `game_move_has_units`
--
ALTER TABLE `game_move_has_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_game_move` (`id_game_move`),
  ADD KEY `id_game_unit` (`id_game_unit`);

--
-- Indexes for table `game_units`
--
ALTER TABLE `game_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_game` (`id_game`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_game_area` (`id_game_area`),
  ADD KEY `id_game_area_in_port` (`id_game_area_in_port`),
  ADD KEY `id_unit` (`id_unit`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `area_is_adjacent`
--
ALTER TABLE `area_is_adjacent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_in_game_phase_info`
--
ALTER TABLE `user_in_game_phase_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_is_in_game`
--
ALTER TABLE `user_is_in_game`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `option_types`
--
ALTER TABLE `option_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `phases`
--
ALTER TABLE `phases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `areas_get_resources`
--
ALTER TABLE `areas_get_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `start_set_has_areas`
--
ALTER TABLE `start_set_has_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `start_sets`
--
ALTER TABLE `start_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `start_ships`
--
ALTER TABLE `start_ships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `game_areas`
--
ALTER TABLE `game_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `game_moves`
--
ALTER TABLE `game_moves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `game_move_has_areas`
--
ALTER TABLE `game_move_has_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `game_move_has_units`
--
ALTER TABLE `game_move_has_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `game_units`
--
ALTER TABLE `game_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `area_is_adjacent`
--
ALTER TABLE `area_is_adjacent`
  ADD CONSTRAINT `area_is_adjacent_area1` FOREIGN KEY (`id_area1`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `area_is_adjacent_area2` FOREIGN KEY (`id_area2`) REFERENCES `areas` (`id`);
--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_type` FOREIGN KEY (`id_type`) REFERENCES `types` (`id`);
--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_creator` FOREIGN KEY (`id_creator`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `games_phase` FOREIGN KEY (`id_phase`) REFERENCES `phases` (`id`);
--
-- Constraints for table `user_in_game_phase_info`
--
ALTER TABLE `user_in_game_phase_info`
  ADD CONSTRAINT `user_in_game_phase_info_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `user_in_game_phase_info_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `user_in_game_phase_info_phase` FOREIGN KEY (`id_phase`) REFERENCES `phases` (`id`);
--
-- Constraints for table `user_is_in_game`
--
ALTER TABLE `user_is_in_game`
  ADD CONSTRAINT `user_is_in_game_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `user_is_in_game_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `user_is_in_game_color` FOREIGN KEY (`id_color`) REFERENCES `colors` (`id`),
  ADD CONSTRAINT `user_is_in_game_set` FOREIGN KEY (`id_set`) REFERENCES `start_sets` (`id`);
--
-- Constraints for table `areas_get_resources`
--
ALTER TABLE `areas_get_resources`
  ADD CONSTRAINT `areas_get_resources_resource` FOREIGN KEY (`id_resource`) REFERENCES `resources` (`id`);
--
-- Constraints for table `start_set_has_areas`
--
ALTER TABLE `start_set_has_areas`
  ADD CONSTRAINT `start_set_has_areas_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `start_set_has_areas_optiontype` FOREIGN KEY (`id_optiontype`) REFERENCES `option_types` (`id`),
  ADD CONSTRAINT `start_set_has_areas_set` FOREIGN KEY (`id_set`) REFERENCES `start_sets` (`id`);
--
-- Constraints for table `start_ships`
--
ALTER TABLE `start_ships`
  ADD CONSTRAINT `start_ships_unit` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`);
--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_type` FOREIGN KEY (`id_type`) REFERENCES `types` (`id`);
--
-- Constraints for table `game_areas`
--
ALTER TABLE `game_areas`
  ADD CONSTRAINT `game_areas_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `game_areas_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `game_areas_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id`),
  ADD CONSTRAINT `game_areas_resource` FOREIGN KEY (`id_resource`) REFERENCES `resources` (`id`);
--
-- Constraints for table `game_moves`
--
ALTER TABLE `game_moves`
  ADD CONSTRAINT `game_moves_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `game_moves_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `game_moves_phase` FOREIGN KEY (`id_phase`) REFERENCES `phases` (`id`);
--
-- Constraints for table `game_move_has_areas`
--
ALTER TABLE `game_move_has_areas`
  ADD CONSTRAINT `game_move_has_areas_game_move` FOREIGN KEY (`id_game_move`) REFERENCES `game_moves` (`id`),
  ADD CONSTRAINT `game_move_has_areas_game_area` FOREIGN KEY (`id_game_area`) REFERENCES `game_areas` (`id`);
--
-- Constraints for table `game_move_has_units`
--
ALTER TABLE `game_move_has_units`
  ADD CONSTRAINT `game_move_has_units_game_move` FOREIGN KEY (`id_game_move`) REFERENCES `game_moves` (`id`),
  ADD CONSTRAINT `game_move_has_units_game_unit` FOREIGN KEY (`id_game_unit`) REFERENCES `game_units` (`id`);
--
-- Constraints for table `game_units`
--
ALTER TABLE `game_units`
  ADD CONSTRAINT `game_units_name` UNIQUE (`id_game`, `name`),
  ADD CONSTRAINT `game_units_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `game_units_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `game_units_game_area` FOREIGN KEY (`id_game_area`) REFERENCES `game_areas` (`id`),
  ADD CONSTRAINT `game_units_game_area_in_port` FOREIGN KEY (`id_game_area_in_port`) REFERENCES `game_areas` (`id`),
  ADD CONSTRAINT `game_units_unit` FOREIGN KEY (`id_unit`) REFERENCES `units` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
