-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              10.1.22-MariaDB - mariadb.org binary distribution
-- S.O. server:                  Win32
-- HeidiSQL Versione:            9.4.0.5174
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `createdBy` int(11) unsigned DEFAULT NULL,
  `startTime` datetime NOT NULL,
  `guestUsers` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'users without account',
  `meetingPoint` int(11) unsigned DEFAULT NULL,
  `maxPlayers` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'must be equals or greater than 1 + guestUsers',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - pending, 1 - started, 2 - ended, -1 cancelled, -2 deleted',
  `sport` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1 - running, 2 - football, 3 bicycle, 4 - tennis',
  `fee` float unsigned NOT NULL,
  `currency` varchar(4) NOT NULL DEFAULT 'EUR',
  `requiredFeedback` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'user must have a percentage of positive feedback equals or greater than requiredFeedback',
  `isOrganizerMode` bit(1) NOT NULL DEFAULT b'0',
  `isPrivate` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  KEY `FK_activities_users` (`createdBy`),
  KEY `FK_activities_addresses` (`meetingPoint`),
  CONSTRAINT `FK_activities_addresses` FOREIGN KEY (`meetingPoint`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_users` FOREIGN KEY (`createdBy`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_bicycle
CREATE TABLE IF NOT EXISTS `activities_bicycle` (
  `id_activity` int(11) unsigned NOT NULL,
  `distance` float unsigned NOT NULL DEFAULT '4',
  `traveled` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_bicycle_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_chat
CREATE TABLE IF NOT EXISTS `activities_chat` (
  `id_activity` int(11) unsigned NOT NULL,
  `id_user` int(11) unsigned DEFAULT NULL,
  `message` varchar(200) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  KEY `FK_activities_chat_activities` (`id_activity`),
  KEY `FK_activities_chat_users` (`id_user`),
  CONSTRAINT `FK_activities_chat_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_chat_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_football
CREATE TABLE IF NOT EXISTS `activities_football` (
  `id_activity` int(11) unsigned NOT NULL,
  `playersPerTeam` tinyint(3) unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_football_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_joins
CREATE TABLE IF NOT EXISTS `activities_joins` (
  `id_activity` int(11) unsigned NOT NULL,
  `id_user` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_activity`,`id_user`),
  KEY `FK_activities_joins_users` (`id_user`),
  CONSTRAINT `FK_activities_joins_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_joins_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_running
CREATE TABLE IF NOT EXISTS `activities_running` (
  `id_activity` int(11) unsigned NOT NULL,
  `distance` float unsigned NOT NULL DEFAULT '4',
  `traveled` float unsigned NOT NULL DEFAULT '0',
  `fitness` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_running_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.activities_tennis
CREATE TABLE IF NOT EXISTS `activities_tennis` (
  `id_activity` int(11) unsigned NOT NULL,
  `isDouble` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id_activity`),
  CONSTRAINT `FK_activities_tennis_activities` FOREIGN KEY (`id_activity`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `route` varchar(48) DEFAULT NULL,
  `street_number` smallint(5) unsigned DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `createdBy` int(11) unsigned NOT NULL,
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
  `executionTime` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`request_id`),
  CONSTRAINT `FK_log_response_log_request` FOREIGN KEY (`request_id`) REFERENCES `log_request` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.push_devices
CREATE TABLE IF NOT EXISTS `push_devices` (
  `id_user` int(11) unsigned NOT NULL,
  `token` text NOT NULL,
  `deviceOS` tinyint(3) unsigned NOT NULL COMMENT '1 android; 2 ios; 3 windows 10;',
  `deviceId` varchar(80) NOT NULL,
  KEY `FK_push_devices_user` (`id_user`),
  CONSTRAINT `FK_push_devices_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.upload_image_request
CREATE TABLE IF NOT EXISTS `upload_image_request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requestHash` varchar(32) NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `checksum` varchar(32) NOT NULL,
  `type` varchar(10) NOT NULL,
  `filename` varchar(32) NOT NULL,
  `album_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_type_filename` (`userId`,`type`,`filename`),
  KEY `FK_upload_image_request_user_album` (`album_id`),
  CONSTRAINT `FK_upload_image_request_user_album` FOREIGN KEY (`album_id`) REFERENCES `users_album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_upload_image_request_users` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(25) NOT NULL,
  `lastName` varchar(25) NOT NULL,
  `email` varchar(64) NOT NULL,
  `birth` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `timezone` varchar(32) NOT NULL DEFAULT 'Europe/London',
  `defaultLocation` int(11) unsigned DEFAULT NULL,
  `notifyNearbyActivities` bit(1) NOT NULL DEFAULT b'0',
  `sex` bit(2) NOT NULL DEFAULT b'0' COMMENT '0 - male 1 - female',
  `coins` int(10) unsigned NOT NULL DEFAULT '10',
  `experience` int(10) unsigned NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '1',
  `private` bit(1) NOT NULL DEFAULT b'0',
  `image` varchar(32) DEFAULT NULL,
  `banned` bit(1) NOT NULL DEFAULT b'0',
  `ban_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `FK_users_addresses` (`defaultLocation`),
  CONSTRAINT `FK_users_addresses` FOREIGN KEY (`defaultLocation`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_album
CREATE TABLE IF NOT EXISTS `users_album` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_name` (`userId`,`name`),
  CONSTRAINT `FK_user_album_users` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_album_pictures
CREATE TABLE IF NOT EXISTS `users_album_pictures` (
  `album_id` int(10) unsigned NOT NULL,
  `picture_file` varchar(32) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` tinytext,
  PRIMARY KEY (`album_id`,`picture_file`),
  CONSTRAINT `FK_user_album_pictures_user_album` FOREIGN KEY (`album_id`) REFERENCES `users_album` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_friend
CREATE TABLE IF NOT EXISTS `users_friend` (
  `user_id` int(11) unsigned NOT NULL,
  `friend_id` int(11) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`friend_id`),
  KEY `FK_users_friend_users_2` (`friend_id`),
  CONSTRAINT `FK_users_friend_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_friend_users_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- L’esportazione dei dati non era selezionata.
-- Dump della struttura di tabella projectrunners.users_friend_request
CREATE TABLE IF NOT EXISTS `users_friend_request` (
  `user_id` int(11) unsigned NOT NULL,
  `friend_id` int(11) unsigned NOT NULL,
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
