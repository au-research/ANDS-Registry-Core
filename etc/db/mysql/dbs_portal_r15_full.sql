CREATE DATABASE  IF NOT EXISTS `dbs_portal` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `dbs_portal`;
-- MySQL dump 10.13  Distrib 5.5.24, for osx10.5 (i386)
--
-- Host: devl.ands.org.au    Database: dbs_portal
-- ------------------------------------------------------
-- Server version	5.5.39-36.0-log

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
-- Table structure for table `click_stats`
--

DROP TABLE IF EXISTS `click_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `click_stats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `source_url` varchar(512) COLLATE utf8_bin NOT NULL,
  `target_url` varchar(512) COLLATE utf8_bin NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `user_agent` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `login_identifier` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `note` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17189 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contributor_pages`
--

DROP TABLE IF EXISTS `contributor_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributor_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `authorative_datasource` int(11) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `data` text,
  `date_modified` datetime DEFAULT NULL,
  `modified_who` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name_status_index` (`name`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_views`
--

DROP TABLE IF EXISTS `page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_views` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `request_uri` varchar(512) COLLATE utf8_bin NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `user_agent` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `login_identifier` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `referer` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `note` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `registry_object_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7199993 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `record_stats`
--

DROP TABLE IF EXISTS `record_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ro_id` bigint(20) DEFAULT NULL,
  `ro_slug` varchar(255) DEFAULT NULL,
  `viewed` int(11) DEFAULT '0',
  `cited` int(11) DEFAULT '0',
  `accessed` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `roid_index` (`ro_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_occurence`
--

DROP TABLE IF EXISTS `search_occurence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_occurence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term` varchar(256) COLLATE utf8_bin NOT NULL,
  `occurence` int(11) NOT NULL,
  `num_found` int(11) NOT NULL,
  `ranking` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `term_UNIQUE` (`term`),
  KEY `term_INDEX` (`term`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=16896 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_result_counts`
--

DROP TABLE IF EXISTS `search_result_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_result_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `occurrence` int(11) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `search_term` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4712 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_terms`
--

DROP TABLE IF EXISTS `search_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `term` varchar(256) COLLATE utf8_bin NOT NULL,
  `timestamp` int(11) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `user_agent` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `login_identifier` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `num_found` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=154934 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_data`
--

DROP TABLE IF EXISTS `user_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_data` (
  `role_id` varchar(255) NOT NULL,
  `user_data` longtext,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) COLLATE utf8_bin DEFAULT NULL,
  `displayName` varchar(256) COLLATE utf8_bin DEFAULT NULL,
  `status` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `provider` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `profile` text COLLATE utf8_bin,
  `access_token` text COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-24 13:05:27
