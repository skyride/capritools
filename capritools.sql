-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 13, 2015 at 10:25 PM
-- Server version: 5.5.38
-- PHP Version: 5.4.4-14+deb7u14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `capritools`
--

-- --------------------------------------------------------

--
-- Table structure for table `celestials`
--

CREATE TABLE IF NOT EXISTS `celestials` (
  `itemID` bigint(20) NOT NULL,
  `itemName` varchar(200) NOT NULL,
  `typeID` int(11) NOT NULL,
  `systemID` bigint(20) NOT NULL,
  `systemName` varchar(200) NOT NULL,
  `constellationID` bigint(20) NOT NULL,
  `constellationName` varchar(200) NOT NULL,
  `regionID` bigint(20) NOT NULL,
  `regionName` varchar(200) NOT NULL,
  `typeName` varchar(200) NOT NULL,
  `mass` double NOT NULL,
  `volume` double NOT NULL,
  `groupID` int(11) NOT NULL,
  `groupName` varchar(100) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL,
  PRIMARY KEY (`itemName`),
  KEY `systemName` (`systemName`),
  KEY `constellationName` (`constellationName`),
  KEY `regionName` (`regionName`),
  KEY `typeName` (`typeName`),
  KEY `groupName` (`groupName`),
  KEY `categoryName` (`categoryName`),
  KEY `itemID` (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dscanObjects`
--

CREATE TABLE IF NOT EXISTS `dscanObjects` (
  `scan` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `distance` bigint(20) NOT NULL,
  KEY `scan` (`scan`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- --------------------------------------------------------

--
-- Table structure for table `dscanScans`
--

CREATE TABLE IF NOT EXISTS `dscanScans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `key` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=1725 ;

-- --------------------------------------------------------

--
-- Table structure for table `gates`
--

CREATE TABLE IF NOT EXISTS `gates` (
  `itemID` int(8) DEFAULT NULL,
  `itemName` varchar(17) DEFAULT NULL,
  `typeID` int(5) DEFAULT NULL,
  `systemID` int(8) DEFAULT NULL,
  `systemName` varchar(17) DEFAULT NULL,
  `constellationID` int(8) DEFAULT NULL,
  `constellationName` varchar(20) DEFAULT NULL,
  `regionID` int(8) DEFAULT NULL,
  `regionName` varchar(20) DEFAULT NULL,
  `typeName` varchar(33) DEFAULT NULL,
  `mass` bigint(12) DEFAULT NULL,
  `volume` bigint(11) DEFAULT NULL,
  `groupID` int(2) DEFAULT NULL,
  `groupName` varchar(8) DEFAULT NULL,
  `categoryID` int(1) DEFAULT NULL,
  `categoryName` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lscanScans`
--

CREATE TABLE IF NOT EXISTS `lscanScans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `key` varchar(40) NOT NULL,
  `pasteKey` varchar(40) NOT NULL,
  `total` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2044 ;

-- --------------------------------------------------------

--
-- Table structure for table `pageHits`
--

CREATE TABLE IF NOT EXISTS `pageHits` (
  `date` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `referrer` varchar(100) NOT NULL,
  `page` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pasteData`
--

CREATE TABLE IF NOT EXISTS `pasteData` (
  `id` int(11) NOT NULL,
  `sequence` int(11) NOT NULL,
  `data` varchar(1024) NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pastes`
--

CREATE TABLE IF NOT EXISTS `pastes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `key` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2838 ;

-- --------------------------------------------------------

--
-- Table structure for table `plrAlliances`
--

CREATE TABLE IF NOT EXISTS `plrAlliances` (
  `id` int(11) NOT NULL,
  `name` varchar(48) NOT NULL,
  `ticker` varchar(10) NOT NULL,
  `executor_corp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `plrChars`
--

CREATE TABLE IF NOT EXISTS `plrChars` (
  `id` int(11) NOT NULL,
  `corp` int(11) NOT NULL,
  `name` varchar(48) NOT NULL,
  PRIMARY KEY (`name`),
  KEY `corp` (`corp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `plrCorps`
--

CREATE TABLE IF NOT EXISTS `plrCorps` (
  `id` int(11) NOT NULL,
  `alliance` int(11) NOT NULL,
  `name` varchar(48) NOT NULL,
  `ticker` varchar(10) NOT NULL,
  `is_npc_corp` int(11) NOT NULL,
  `taxRate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `alliance` (`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ships`
--

CREATE TABLE IF NOT EXISTS `ships` (
  `typeID` int(5) DEFAULT NULL,
  `typeName` varchar(35) NOT NULL DEFAULT '',
  `groupID` int(4) DEFAULT NULL,
  `groupName` varchar(26) DEFAULT NULL,
  `categoryID` int(1) DEFAULT NULL,
  `categoryName` varchar(4) DEFAULT NULL,
  `mass` bigint(10) DEFAULT NULL,
  `volume` int(9) DEFAULT NULL,
  `marketGroupID` int(4) DEFAULT NULL,
  PRIMARY KEY (`typeName`),
  KEY `categoryName` (`categoryName`),
  KEY `groupName` (`groupName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shoppingLists`
--

CREATE TABLE IF NOT EXISTS `shoppingLists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `key` varchar(40) NOT NULL,
  `pasteKey` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=557 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
