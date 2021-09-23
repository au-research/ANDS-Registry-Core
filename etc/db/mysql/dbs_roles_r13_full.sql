-- MySQL dump 10.13  Distrib 5.5.39, for Linux (x86_64)
--
-- Host: localhost    Database: dbs_roles
-- ------------------------------------------------------
-- Server version	5.5.39

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_relations`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) NOT NULL,
  `role_type_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `authentication_service_id` varchar(32) DEFAULT NULL,
  `enabled` varchar(1) NOT NULL DEFAULT 't',
  `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-08-07  2:45:29
