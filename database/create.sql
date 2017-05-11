-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              10.1.21-MariaDB - mariadb.org binary distribution
-- S.O. server:                  Win32
-- HeidiSQL Versione:            9.4.0.5169
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dump della struttura del database projectrunners
CREATE DATABASE IF NOT EXISTS `projectrunners` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `projectrunners`;

-- Dump della struttura di tabella projectrunners.activities
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdBy` int(11) DEFAULT NULL,
  `startTime` datetime NOT NULL,
  `guestUsers` int(11) NOT NULL DEFAULT '0' COMMENT 'users without account',
  `meetingPoint` int(11) DEFAULT NULL,
  `maxPlayers` int(11) NOT NULL DEFAULT '1' COMMENT 'must be equals or greater than 1 + guestUsers',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0 - pending, 1 - started, 2 - ended, -1 cancelled, -2 deleted',
  `sport` int(11) NOT NULL DEFAULT '1' COMMENT '1 - running, 2 - football, 3 bicycle, 4 - tennis',
  `fee` float NOT NULL,
  `requiredFeedback` int(11) NOT NULL DEFAULT '0' COMMENT 'user must have a percentage of positive feedback equals or greater than requiredFeedback',
  PRIMARY KEY (`id`),
  KEY `FK_activities_users` (`createdBy`),
  KEY `FK_activities_addresses` (`meetingPoint`),
  CONSTRAINT `FK_activities_addresses` FOREIGN KEY (`meetingPoint`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_users` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_bicycle
CREATE TABLE IF NOT EXISTS `activities_bicycle` (
  `id_activity` int(11) NOT NULL,
  `distance` float NOT NULL DEFAULT '4',
  `traveled` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_bicycle_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_football
CREATE TABLE IF NOT EXISTS `activities_football` (
  `id_activity` int(11) NOT NULL,
  `playersPerTeam` int(11) NOT NULL DEFAULT '5',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_football_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_joins
CREATE TABLE IF NOT EXISTS `activities_joins` (
  `id_activity` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_activity`,`id_user`),
  KEY `FK_activities_joins_users` (`id_user`),
  CONSTRAINT `FK_activities_joins_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_joins_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_running
CREATE TABLE IF NOT EXISTS `activities_running` (
  `id_activity` int(11) NOT NULL,
  `distance` float NOT NULL DEFAULT '4',
  `traveled` float NOT NULL DEFAULT '0',
  `fitness` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_running_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_tennis
CREATE TABLE IF NOT EXISTS `activities_tennis` (
  `id_activity` int(11) NOT NULL,
  `isDouble` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_tennis_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `route` varchar(48) DEFAULT NULL,
  `street_number` int(11) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `FK_addresses_users` (`createdBy`),
  CONSTRAINT `FK_addresses_users` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.log_request
CREATE TABLE IF NOT EXISTS `log_request` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `_POST` text,
  `_GET` text,
  `_SERVER` text,
  `_SESSION` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.log_response
CREATE TABLE IF NOT EXISTS `log_response` (
  `request_id` bigint(20) unsigned NOT NULL,
  `response` text,
  PRIMARY KEY (`request_id`),
  CONSTRAINT `FK_log_response_log_request` FOREIGN KEY (`request_id`) REFERENCES `log_request` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `push_devices` (
  `id_user` int(11) unsigned NOT NULL,
  `token` text NOT NULL,
  `deviceOS` tinyint(3) unsigned NOT NULL COMMENT '1 android; 2 ios; 3 windows 10;',
  `deviceId` varchar(80) NOT NULL,
  KEY `FK_push_devices_user` (`id_user`),
  CONSTRAINT `FK_push_devices_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(25) NOT NULL,
  `lastName` varchar(25) NOT NULL,
  `email` varchar(64) NOT NULL,
  `birth` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_friend
CREATE TABLE IF NOT EXISTS `users_friend` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`friend_id`),
  KEY `FK_users_friend_users_2` (`friend_id`),
  CONSTRAINT `FK_users_friend_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_friend_users_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_friend_request
CREATE TABLE IF NOT EXISTS `users_friend_request` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`friend_id`),
  KEY `FK_users_friend_request_users_2` (`friend_id`),
  CONSTRAINT `FK_users_friend_request_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_friend_request_users_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
