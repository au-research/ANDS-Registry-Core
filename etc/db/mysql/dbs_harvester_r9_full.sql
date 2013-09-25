-- MySQL dump 10.13  Distrib 5.5.24, for osx10.5 (i386)
--
-- Host: ands3.anu.edu.au    Database: dbs_harvester
-- ------------------------------------------------------
-- Server version	5.5.25a

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
-- Table structure for table `fragment`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fragment` (
  `fragment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `harvest_id` varchar(256) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `date_stored` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `text` longblob,
  PRIMARY KEY (`fragment_id`),
  UNIQUE KEY `fragment_id` (`fragment_id`),
  KEY `fk_fragment_1_idx` (`harvest_id`),
  CONSTRAINT `fk_fragment_1` FOREIGN KEY (`harvest_id`) REFERENCES `harvest` (`harvest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvest`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvest` (
  `harvest_id` varchar(256) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `response_url` varchar(256) DEFAULT NULL,
  `method` varchar(32) DEFAULT NULL,
  `mode` varchar(32) DEFAULT NULL,
  `date_started` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_completed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_from` varchar(20) DEFAULT NULL,
  `date_until` varchar(20) DEFAULT NULL,
  `set` varchar(128) DEFAULT NULL,
  `resumption_token` varchar(128) DEFAULT NULL,
  `metadata_prefix` varchar(32) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `advanced_harvesting_mode` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`harvest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvest_parameter`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvest_parameter` (
  `harvest_id` varchar(256) NOT NULL DEFAULT '',
  `name` varchar(64) DEFAULT NULL,
  `value` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`value`,`harvest_id`),
  KEY `fk_harvest_parameter_1_idx` (`harvest_id`),
  CONSTRAINT `fk_harvest_parameter_1` FOREIGN KEY (`harvest_id`) REFERENCES `harvest` (`harvest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `provider`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider` (
  `provider_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_url` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `request`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request` (
  `request_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule`
--


/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule` (
  `harvest_id` varchar(256) NOT NULL,
  `last_run` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `next_run` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `frequency` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`harvest_id`),
  KEY `fk_schedule_1_idx` (`harvest_id`),
  CONSTRAINT `fk_schedule_1` FOREIGN KEY (`harvest_id`) REFERENCES `harvest` (`harvest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-03 12:55:31

INSERT INTO request VALUES(DEFAULT, 'Identify');
INSERT INTO request VALUES(DEFAULT, 'ListSets');
INSERT INTO request VALUES(DEFAULT, 'ListMetadataFormats');
INSERT INTO request VALUES(DEFAULT, 'ListRecords');
INSERT INTO request VALUES(DEFAULT, 'ListIdentifiers');
INSERT INTO request VALUES(DEFAULT, 'GetRecord');