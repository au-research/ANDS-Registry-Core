-- MySQL dump 10.13  Distrib 5.7.32, for osx10.15 (x86_64)
--
-- Host: 130.56.60.126    Database: dbs_roles
-- ------------------------------------------------------
-- Server version	5.5.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `authentication_built_in`
--
USE dbs_roles;

DROP TABLE IF EXISTS `authentication_built_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authentication_built_in` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `passphrase_sha1` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `created_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_relations`
--

DROP TABLE IF EXISTS `role_relations`;
CREATE TABLE `role_relations` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `parent_role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `child_role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5152 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role_type_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `authentication_service_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` varchar(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 't',
  `created_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  `modified_who` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SYSTEM',
  `modified_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shared_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `persistent_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth_access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oauth_data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`,`role_id`),
  KEY `roleid_index` (`role_id`),
  KEY `roletypeid_index` (`role_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7501 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
