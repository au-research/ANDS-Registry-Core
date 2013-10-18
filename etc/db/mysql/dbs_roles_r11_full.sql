CREATE DATABASE  IF NOT EXISTS `dbs_roles` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `dbs_roles`;
-- MySQL dump 10.13  Distrib 5.5.24, for osx10.5 (i386)
--
-- Host: 130.56.60.128    Database: dbs_roles
-- ------------------------------------------------------
-- Server version	5.5.33-log

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

DROP TABLE IF EXISTS `authentication_built_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authentication_built_in` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) NOT NULL,
  `passphrase_sha1` varchar(40) NOT NULL,
  `created_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `role_relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_relations` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `parent_role_id` varchar(255) NOT NULL,
  `child_role_id` varchar(255) NOT NULL,
  `created_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) NOT NULL,
  `role_type_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `authentication_service_id` varchar(32),
  `enabled` varchar(1) NOT NULL DEFAULT 't',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`role_id`, `role_type_id`, `name`) VALUES ('REGISTRY_SUPERUSER','ROLE_FUNCTIONAL','Superuser');
INSERT INTO `roles` (`role_id`, `role_type_id`, `name`, `authentication_service_id`) VALUES ('superuser', 'ROLE_USER', 'Admin User', 'AUTHENTICATION_BUILT_IN');

/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `authentication_built_in` WRITE;
/*!40000 ALTER TABLE `authentication_built_in` DISABLE KEYS */;
INSERT INTO `authentication_built_in` (`role_id` , `passphrase_sha1`) VALUES ('superuser','8e67bb26b358e2ed20fe552ed6fb832f397a507d');
/*!40000 ALTER TABLE `authentication_built_in` ENABLE KEYS */;
UNLOCK TABLES;


LOCK TABLES `role_relations` WRITE;
/*!40000 ALTER TABLE `role_relations` DISABLE KEYS */;
INSERT INTO `role_relations` (`parent_role_id` , `child_role_id`) VALUES ('REGISTRY_SUPERUSER','superuser');
/*!40000 ALTER TABLE `role_relations` ENABLE KEYS */;
UNLOCK TABLES;


