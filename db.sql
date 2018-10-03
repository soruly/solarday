-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2016 at 06:15 PM
-- Server version: 10.1.16-MariaDB
-- PHP Version: 7.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

CREATE TABLE `blog` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `archive` smallint(5) UNSIGNED NOT NULL,
  `category` tinyint(3) UNSIGNED NOT NULL,
  `private` tinyint(1) NOT NULL,
  `time` datetime NOT NULL COMMENT 'UTC',
  `title` tinytext,
  `blog` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_archive`
--

CREATE TABLE `blog_archive` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` tinytext NOT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_category`
--

CREATE TABLE `blog_category` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` tinytext NOT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `blog_view`
-- (See below for the actual view)
--
CREATE TABLE `blog_view` (
`id` smallint(5) unsigned
,`archive` smallint(5) unsigned
,`archive_name` tinytext
,`category` tinyint(3) unsigned
,`category_name` tinytext
,`private` tinyint(1)
,`time` datetime
,`title` tinytext
,`blog` longtext
);

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE `photo` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `filename` tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `name` varchar(32) NOT NULL,
  `value` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `blog_view`
--
DROP TABLE IF EXISTS `blog_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`blog`@`localhost` SQL SECURITY DEFINER VIEW `blog_view`  AS  select `id` AS `id`,`archive` AS `archive`,`blog_archive`.`name` AS `archive_name`,`category` AS `category`,`blog_category`.`name` AS `category_name`,`private` AS `private`,`time` AS `time`,`title` AS `title`,`blog` AS `blog` from ((`blog` join `blog_archive` on((`archive` = `blog_archive`.`id`))) join `blog_category` on((`category` = `blog_category`.`id`))) order by `id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_archive`
--
ALTER TABLE `blog_archive`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_category`
--
ALTER TABLE `blog_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `photo`
--
ALTER TABLE `photo`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
