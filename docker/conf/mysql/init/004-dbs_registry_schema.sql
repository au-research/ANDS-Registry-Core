-- MySQL dump 10.13  Distrib 5.7.32, for osx10.15 (x86_64)
--
-- Host: 130.56.60.126    Database: dbs_registry
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

USE dbs_registry;

--
-- Temporary table structure for view `alt_schema_versions`
--

DROP TABLE IF EXISTS `alt_schema_versions`;
/*!50001 DROP VIEW IF EXISTS `alt_schema_versions`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `alt_schema_versions` AS SELECT 
 1 AS `id`,
 1 AS `registry_object_id`,
 1 AS `key`,
 1 AS `group`,
 1 AS `registry_object_data_source_id`,
 1 AS `class`,
 1 AS `data`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `origin`,
 1 AS `prefix`,
 1 AS `uri`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `api_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `owner_email` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_organisation` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_purpose` text COLLATE utf8_unicode_ci,
  `owner_sector` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_requests`
--

DROP TABLE IF EXISTS `api_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_key` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `params` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_api_key` (`api_key`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=9600274 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `background_tasks`
--

DROP TABLE IF EXISTS `background_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `background_tasks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  `completed` int(10) unsigned DEFAULT NULL,
  `prerequisite_task` mediumint(8) unsigned DEFAULT NULL,
  `log_message` text COLLATE utf8_unicode_ci,
  `param_1` text COLLATE utf8_unicode_ci,
  `param_2` text COLLATE utf8_unicode_ci,
  `status` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'QUEUED',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configs`
--

DROP TABLE IF EXISTS `configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(45) NOT NULL,
  `type` varchar(45) DEFAULT NULL,
  `value` blob,
  PRIMARY KEY (`id`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_source_attributes`
--

DROP TABLE IF EXISTS `data_source_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_source_attributes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_data_sources` (`data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16359 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_source_logs`
--

DROP TABLE IF EXISTS `data_source_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_source_logs` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `date_modified` int(10) unsigned DEFAULT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `class` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `harvester_error_type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_data_source` (`data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=559939 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_sources`
--

DROP TABLE IF EXISTS `data_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_sources` (
  `data_source_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_owner` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`data_source_id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  UNIQUE KEY `slug_UNIQUE` (`slug`),
  UNIQUE KEY `id_index` (`data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dci`
--

DROP TABLE IF EXISTS `dci`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dci` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `registry_object_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_data_source_id` mediumint(8) NOT NULL,
  `data` blob,
  `hash` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36305 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deleted_registry_objects`
--

DROP TABLE IF EXISTS `deleted_registry_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deleted_registry_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_data` text COLLATE utf8_unicode_ci,
  `datasource` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_data_source_id_idx` (`data_source_id`),
  KEY `key_index` (`key`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=393031 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=607 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvest_requests`
--

DROP TABLE IF EXISTS `harvest_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvest_requests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `status` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `next_harvest` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `harvest_frequency` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `oai_set` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_harvest_request_data_source` (`data_source_id`),
  KEY `fk_harvest_data_source` (`data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12361 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `harvests`
--

DROP TABLE IF EXISTS `harvests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `harvests` (
  `harvest_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(9) NOT NULL,
  `status` varchar(45) DEFAULT NULL,
  `message` text,
  `next_run` datetime DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  `mode` varchar(45) DEFAULT NULL,
  `batch_number` varchar(120) DEFAULT NULL,
  `importer_message` text,
  `task_id` mediumint(9) DEFAULT NULL,
  `summary` text,
  PRIMARY KEY (`harvest_id`),
  KEY `idx_status` (`status`),
  KEY `idx_next_run` (`next_run`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `has_related_by_ds`
--

DROP TABLE IF EXISTS `has_related_by_ds`;
/*!50001 DROP VIEW IF EXISTS `has_related_by_ds`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `has_related_by_ds` AS SELECT 
 1 AS `registry_object_id`,
 1 AS `data_source_id`,
 1 AS `r1from`,
 1 AS `r2from`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `id_cache`
--

DROP TABLE IF EXISTS `id_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id_cache` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `id_cache_2`
--

DROP TABLE IF EXISTS `id_cache_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `id_cache_2` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `identifier_relationships`
--

DROP TABLE IF EXISTS `identifier_relationships`;
/*!50001 DROP VIEW IF EXISTS `identifier_relationships`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `identifier_relationships` AS SELECT 
 1 AS `from_id`,
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `relation_origin`,
 1 AS `relation_identifier_id`,
 1 AS `relation_type`,
 1 AS `relation_to_title`,
 1 AS `relation_url`,
 1 AS `related_description`,
 1 AS `to_identifier`,
 1 AS `to_identifier_type`,
 1 AS `to_related_info_type`,
 1 AS `to_id`,
 1 AS `to_key`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `identifier_relationships_all_status`
--

DROP TABLE IF EXISTS `identifier_relationships_all_status`;
/*!50001 DROP VIEW IF EXISTS `identifier_relationships_all_status`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `identifier_relationships_all_status` AS SELECT 
 1 AS `from_id`,
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `relation_origin`,
 1 AS `relation_identifier_id`,
 1 AS `relation_type`,
 1 AS `relation_to_title`,
 1 AS `relation_url`,
 1 AS `related_description`,
 1 AS `to_identifier`,
 1 AS `to_identifier_type`,
 1 AS `to_related_info_type`,
 1 AS `to_id`,
 1 AS `to_key`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `identifier_relationships_normalised`
--

DROP TABLE IF EXISTS `identifier_relationships_normalised`;
/*!50001 DROP VIEW IF EXISTS `identifier_relationships_normalised`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `identifier_relationships_normalised` AS SELECT 
 1 AS `from_id`,
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `relation_origin`,
 1 AS `relation_identifier_id`,
 1 AS `relation_type`,
 1 AS `relation_to_title`,
 1 AS `relation_url`,
 1 AS `related_description`,
 1 AS `to_identifier`,
 1 AS `to_identifier_type`,
 1 AS `to_related_info_type`,
 1 AS `to_id`,
 1 AS `to_key`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `implicit_relationships`
--

DROP TABLE IF EXISTS `implicit_relationships`;
/*!50001 DROP VIEW IF EXISTS `implicit_relationships`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `implicit_relationships` AS SELECT 
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `from_id`,
 1 AS `to_id`,
 1 AS `relation_type`,
 1 AS `relation_origin`,
 1 AS `to_key`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `institutional_pages`
--

DROP TABLE IF EXISTS `institutional_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutional_pages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `authorative_data_source_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pk_group` (`group`),
  KEY `fk_data_source` (`authorative_data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=706 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `msg` mediumtext COLLATE utf8_unicode_ci,
  `date_modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30905 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `object_creation_date`
--

DROP TABLE IF EXISTS `object_creation_date`;
/*!50001 DROP VIEW IF EXISTS `object_creation_date`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `object_creation_date` AS SELECT 
 1 AS `registry_object_id`,
 1 AS `creation_date`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `orcid_exports`
--

DROP TABLE IF EXISTS `orcid_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orcid_exports` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `orcid_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `put_code` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  `response` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2799 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orcid_records`
--

DROP TABLE IF EXISTS `orcid_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orcid_records` (
  `orcid_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_data` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `refresh_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`orcid_id`),
  UNIQUE KEY `id_UNIQUE` (`orcid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `record_data`
--

DROP TABLE IF EXISTS `record_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `record_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `current` enum('TRUE','FALSE') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'TRUE',
  `data` mediumblob,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `scheme` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'rif',
  `hash` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unhashed',
  PRIMARY KEY (`id`),
  KEY `ro_selector` (`registry_object_id`,`current`),
  KEY `registry_object_id_UNIQUE` (`registry_object_id`,`id`),
  KEY `fk_record_data_registry_object` (`registry_object_id`,`current`),
  KEY `record_data_registry_object_id_index` (`registry_object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22062943 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_attributes`
--

DROP TABLE IF EXISTS `registry_object_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_attributes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) DEFAULT NULL,
  `value` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reg_attr` (`registry_object_id`,`attribute`),
  KEY `fk_attr_to_registry_object` (`registry_object_id`),
  KEY `idx_reg_attr_val` (`attribute`,`value`(255)) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=4213679 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_citations`
--

DROP TABLE IF EXISTS `registry_object_citations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_citations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `service_provider` varchar(32) NOT NULL,
  `query_terms` varchar(512) DEFAULT NULL,
  `citation_data` text,
  `status` varchar(32) DEFAULT NULL,
  `Last_checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_identifier_relationships`
--

DROP TABLE IF EXISTS `registry_object_identifier_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_identifier_relationships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_identifier` varchar(255) NOT NULL,
  `related_info_type` varchar(45) DEFAULT NULL,
  `related_object_identifier_type` varchar(45) NOT NULL,
  `relation_type` varchar(45) DEFAULT 'hasAssociationWith',
  `related_title` varchar(255) DEFAULT NULL,
  `related_url` varchar(255) DEFAULT NULL,
  `related_description` varchar(512) DEFAULT NULL,
  `connections_preview_div` text,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`related_object_identifier`),
  KEY `idx_registry_object_id` (`registry_object_id`),
  KEY `idx_relation_type` (`relation_type`),
  KEY `idx_related_object_identifier` (`related_object_identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=546723 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_identifier_relationships_normalised`
--

DROP TABLE IF EXISTS `registry_object_identifier_relationships_normalised`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_identifier_relationships_normalised` (
  `id` int(10) unsigned NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_identifier` varchar(255) NOT NULL,
  `related_info_type` varchar(45) DEFAULT NULL,
  `related_object_identifier_type` varchar(45) NOT NULL,
  `relation_type` varchar(45) DEFAULT 'hasAssociationWith',
  `related_title` varchar(255) DEFAULT NULL,
  `related_url` varchar(255) DEFAULT NULL,
  `related_description` varchar(512) DEFAULT NULL,
  `connections_preview_div` text,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`related_object_identifier`),
  KEY `idx_registry_object_id` (`registry_object_id`),
  KEY `idx_relation_type` (`relation_type`),
  KEY `idx_related_object_identifier` (`related_object_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_identifiers`
--

DROP TABLE IF EXISTS `registry_object_identifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_identifiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_registry_object_id` (`registry_object_id`) USING BTREE,
  KEY `idx_identifier_pairs` (`identifier`,`identifier_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=971309 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_identifiers_normalised`
--

DROP TABLE IF EXISTS `registry_object_identifiers_normalised`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_identifiers_normalised` (
  `id` int(10) unsigned NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_registry_object_id` (`registry_object_id`) USING BTREE,
  KEY `idx_identifier_pairs` (`identifier`,`identifier_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_implicit_relationships`
--

DROP TABLE IF EXISTS `registry_object_implicit_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_implicit_relationships` (
  `from_id` mediumint(8) NOT NULL,
  `to_id` mediumint(8) NOT NULL,
  `relation_type` varchar(512) CHARACTER SET utf8 DEFAULT NULL,
  `relation_origin` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  KEY `idx_from_id` (`from_id`),
  KEY `idx_to_id` (`to_id`),
  KEY `idx_relation_type_origin` (`relation_type`(255),`relation_origin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_links`
--

DROP TABLE IF EXISTS `registry_object_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `link_type` varchar(64) DEFAULT NULL,
  `link` varchar(512) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `last_checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_data_source_id_rol` (`data_source_id`),
  KEY `idx_link_rol` (`link`),
  KEY `idx_registry_object_id_rol` (`registry_object_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1468681 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_metadata`
--

DROP TABLE IF EXISTS `registry_object_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_metadata` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `attribute` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_reg_metadata` (`registry_object_id`,`attribute`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=728855 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_relationships`
--

DROP TABLE IF EXISTS `registry_object_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_relationships` (
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `related_object_class` enum('collection','service','party','activity') COLLATE utf8_unicode_ci DEFAULT NULL,
  `origin` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'EXPLICIT',
  `relation_type` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relation_description` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relation_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_related_object_id` (`registry_object_id`) USING HASH,
  KEY `idx_related_object_key` (`related_object_key`) USING HASH,
  KEY `idx_related_object_pair` (`registry_object_id`,`related_object_key`) USING HASH,
  KEY `idx_relation_type` (`relation_type`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_tags`
--

DROP TABLE IF EXISTS `registry_object_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `user` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `user_from` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tagkey_index` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=427 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_versions`
--

DROP TABLE IF EXISTS `registry_object_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_versions` (
  `version_id` mediumint(8) NOT NULL,
  `registry_object_id` mediumint(8) NOT NULL,
  PRIMARY KEY (`version_id`,`registry_object_id`),
  KEY `version_id` (`version_id`,`registry_object_id`),
  KEY `idx_registry_object_versions_registry_object_id` (`registry_object_id`),
  KEY `idx_version_id_registry_id` (`version_id`,`registry_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_objects`
--

DROP TABLE IF EXISTS `registry_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_objects` (
  `registry_object_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class` enum('collection','service','activity','party') COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_owner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not available',
  `modified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `synced_at` datetime DEFAULT NULL,
  PRIMARY KEY (`registry_object_id`),
  KEY `fk_registry_object_data_source` (`data_source_id`) USING HASH,
  KEY `slug_lookup` (`slug`(255)) USING HASH,
  KEY `key_class_index` (`key`,`class`,`status`) USING HASH,
  KEY `key_index` (`class`,`data_source_id`) USING HASH,
  KEY `idx_key` (`key`),
  KEY `idx_class` (`class`),
  KEY `idx_type` (`type`),
  KEY `idx_key_id` (`registry_object_id`,`key`),
  KEY `idx_id_dsid` (`registry_object_id`,`data_source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=667691 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `relationships`
--

DROP TABLE IF EXISTS `relationships`;
/*!50001 DROP VIEW IF EXISTS `relationships`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `relationships` AS SELECT 
 1 AS `from_id`,
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `relation_origin`,
 1 AS `relation_type`,
 1 AS `relation_description`,
 1 AS `relation_url`,
 1 AS `to_key`,
 1 AS `to_id`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `relationships_all_status`
--

DROP TABLE IF EXISTS `relationships_all_status`;
/*!50001 DROP VIEW IF EXISTS `relationships_all_status`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `relationships_all_status` AS SELECT 
 1 AS `from_id`,
 1 AS `from_key`,
 1 AS `from_group`,
 1 AS `from_title`,
 1 AS `from_class`,
 1 AS `from_type`,
 1 AS `from_slug`,
 1 AS `from_data_source_id`,
 1 AS `from_status`,
 1 AS `relation_origin`,
 1 AS `relation_type`,
 1 AS `relation_description`,
 1 AS `relation_url`,
 1 AS `to_key`,
 1 AS `to_id`,
 1 AS `to_group`,
 1 AS `to_title`,
 1 AS `to_class`,
 1 AS `to_type`,
 1 AS `to_slug`,
 1 AS `to_data_source_id`,
 1 AS `to_status`*/;
SET character_set_client = @saved_cs_client;

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
  `authentication_service_id` varchar(32) NOT NULL,
  `enabled` varchar(1) NOT NULL DEFAULT 't',
  `created_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_who` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `modified_when` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shared_token` varchar(255) DEFAULT NULL,
  `persistent_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `oauth_access_token` varchar(255) DEFAULT NULL,
  `oauth_data` text,
  PRIMARY KEY (`id`,`role_id`),
  KEY `roleid_index` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schemas`
--

DROP TABLE IF EXISTS `schemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schemas` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `exportable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`prefix`),
  KEY `id` (`id`,`prefix`),
  KEY `idx_schemas_prefix` (`prefix`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scholix`
--

DROP TABLE IF EXISTS `scholix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scholix` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `scholix_identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `registry_object_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_data_source_id` mediumint(8) NOT NULL,
  `data` blob,
  `hash` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `scholix_identifier_UNIQUE` (`scholix_identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=12781 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_statistics`
--

DROP TABLE IF EXISTS `search_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_statistics` (
  `search_term` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `occurence` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`search_term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `user_agent` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spatial_extents`
--

DROP TABLE IF EXISTS `spatial_extents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spatial_extents` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `coordinates` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_spatial_extent_registry_object` (`registry_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `name` varchar(255) NOT NULL,
  `type` varchar(45) NOT NULL DEFAULT 'public',
  `theme` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `message` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `next_run` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_run` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `priority` int(11) DEFAULT NULL,
  `frequency` varchar(45) DEFAULT NULL,
  `params` text,
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `idx_next_run` (`next_run`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=125921 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `theme_pages`
--

DROP TABLE IF EXISTS `theme_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `theme_pages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) DEFAULT NULL,
  `slug` varchar(256) NOT NULL,
  `secret_tag` varchar(256) DEFAULT NULL,
  `img_src` varchar(256) DEFAULT NULL,
  `description` text,
  `visible` tinyint(4) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `url_mappings`
--

DROP TABLE IF EXISTS `url_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_mappings` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registry_object_id` mediumint(8) unsigned DEFAULT NULL,
  `search_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slug_INDEX` (`slug`) USING HASH,
  KEY `idx_url_to_registry_object` (`registry_object_id`) USING HASH,
  KEY `fk_url_map_to_registry_object` (`slug`,`registry_object_id`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=347447 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `schema_id` mediumint(8) DEFAULT NULL,
  `data` mediumblob NOT NULL,
  `hash` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `origin` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `idx_versions_id` (`id`),
  KEY `idx_versions_schema_id` (`schema_id`),
  KEY `idx_version_id_schema_id` (`id`,`schema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38779 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vocab_metadata`
--

DROP TABLE IF EXISTS `vocab_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocab_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(512) DEFAULT NULL,
  `description` varchar(12000) DEFAULT NULL,
  `publisher` varchar(512) DEFAULT NULL,
  `contact_name` varchar(512) DEFAULT NULL,
  `contact_email` varchar(512) DEFAULT NULL,
  `contact_number` varchar(45) DEFAULT NULL,
  `website` varchar(512) DEFAULT NULL,
  `revision_cycle` varchar(45) DEFAULT NULL,
  `notes` varchar(3000) DEFAULT NULL,
  `language` varchar(512) DEFAULT NULL,
  `information_sources` varchar(512) DEFAULT NULL,
  `record_owner` varchar(512) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `subjects` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_index` (`id`),
  KEY `recordowner_index` (`record_owner`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `alt_schema_versions`
--

/*!50001 DROP VIEW IF EXISTS `alt_schema_versions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `alt_schema_versions` AS select 1 AS `id`,1 AS `registry_object_id`,1 AS `key`,1 AS `group`,1 AS `registry_object_data_source_id`,1 AS `class`,1 AS `data`,1 AS `created_at`,1 AS `updated_at`,1 AS `origin`,1 AS `prefix`,1 AS `uri` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `has_related_by_ds`
--

/*!50001 DROP VIEW IF EXISTS `has_related_by_ds`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `has_related_by_ds` AS select 1 AS `registry_object_id`,1 AS `data_source_id`,1 AS `r1from`,1 AS `r2from` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `identifier_relationships`
--

/*!50001 DROP VIEW IF EXISTS `identifier_relationships`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `identifier_relationships` AS select 1 AS `from_id`,1 AS `from_key`,1 AS `from_group`,1 AS `from_title`,1 AS `from_class`,1 AS `from_type`,1 AS `from_slug`,1 AS `from_data_source_id`,1 AS `from_status`,1 AS `relation_origin`,1 AS `relation_identifier_id`,1 AS `relation_type`,1 AS `relation_to_title`,1 AS `relation_url`,1 AS `related_description`,1 AS `to_identifier`,1 AS `to_identifier_type`,1 AS `to_related_info_type`,1 AS `to_id`,1 AS `to_key`,1 AS `to_group`,1 AS `to_title`,1 AS `to_class`,1 AS `to_type`,1 AS `to_slug`,1 AS `to_data_source_id`,1 AS `to_status` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `identifier_relationships_all_status`
--

/*!50001 DROP VIEW IF EXISTS `identifier_relationships_all_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `identifier_relationships_all_status` AS select 1 AS `from_id`,1 AS `from_key`,1 AS `from_group`,1 AS `from_title`,1 AS `from_class`,1 AS `from_type`,1 AS `from_slug`,1 AS `from_data_source_id`,1 AS `from_status`,1 AS `relation_origin`,1 AS `relation_identifier_id`,1 AS `relation_type`,1 AS `relation_to_title`,1 AS `relation_url`,1 AS `related_description`,1 AS `to_identifier`,1 AS `to_identifier_type`,1 AS `to_related_info_type`,1 AS `to_id`,1 AS `to_key`,1 AS `to_group`,1 AS `to_title`,1 AS `to_class`,1 AS `to_type`,1 AS `to_slug`,1 AS `to_data_source_id`,1 AS `to_status` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `identifier_relationships_normalised`
--

/*!50001 DROP VIEW IF EXISTS `identifier_relationships_normalised`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`webuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `identifier_relationships_normalised` AS select `roir`.`registry_object_id` AS `from_id`,`ros`.`key` AS `from_key`,`ros`.`group` AS `from_group`,`ros`.`title` AS `from_title`,`ros`.`class` AS `from_class`,`ros`.`type` AS `from_type`,`ros`.`slug` AS `from_slug`,`ros`.`data_source_id` AS `from_data_source_id`,`ros`.`status` AS `from_status`,'IDENTIFIER' AS `relation_origin`,`roir`.`id` AS `relation_identifier_id`,`roir`.`relation_type` AS `relation_type`,`roir`.`related_title` AS `relation_to_title`,`roir`.`related_url` AS `relation_url`,`roir`.`related_description` AS `related_description`,`roir`.`related_object_identifier` AS `to_identifier`,`roir`.`related_object_identifier_type` AS `to_identifier_type`,`roir`.`related_info_type` AS `to_related_info_type`,`rot`.`registry_object_id` AS `to_id`,`rot`.`key` AS `to_key`,`rot`.`group` AS `to_group`,`rot`.`title` AS `to_title`,`rot`.`class` AS `to_class`,`rot`.`type` AS `to_type`,`rot`.`slug` AS `to_slug`,`rot`.`data_source_id` AS `to_data_source_id`,`rot`.`status` AS `to_status` from (((`registry_object_identifier_relationships_normalised` `roir` left join `registry_objects` `ros` on((`roir`.`registry_object_id` = `ros`.`registry_object_id`))) left join `registry_object_identifiers_normalised` `roidn` on(((`roir`.`related_object_identifier` = `roidn`.`identifier`) and (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`)))) left join `registry_objects` `rot` on((`roidn`.`registry_object_id` = `rot`.`registry_object_id`))) where ((`ros`.`status` = 'PUBLISHED') and (isnull(`rot`.`status`) or (`rot`.`status` = 'PUBLISHED'))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `implicit_relationships`
--

/*!50001 DROP VIEW IF EXISTS `implicit_relationships`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `implicit_relationships` AS select 1 AS `from_key`,1 AS `from_group`,1 AS `from_title`,1 AS `from_class`,1 AS `from_type`,1 AS `from_slug`,1 AS `from_data_source_id`,1 AS `from_status`,1 AS `from_id`,1 AS `to_id`,1 AS `relation_type`,1 AS `relation_origin`,1 AS `to_key`,1 AS `to_group`,1 AS `to_title`,1 AS `to_class`,1 AS `to_type`,1 AS `to_slug`,1 AS `to_data_source_id`,1 AS `to_status` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `object_creation_date`
--

/*!50001 DROP VIEW IF EXISTS `object_creation_date`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`webuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `object_creation_date` AS select `record_data`.`registry_object_id` AS `registry_object_id`,from_unixtime(min(`record_data`.`timestamp`)) AS `creation_date` from `record_data` group by `record_data`.`registry_object_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `relationships`
--

/*!50001 DROP VIEW IF EXISTS `relationships`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`webuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `relationships` AS select `ror`.`registry_object_id` AS `from_id`,`ros`.`key` AS `from_key`,`ros`.`group` AS `from_group`,`ros`.`title` AS `from_title`,`ros`.`class` AS `from_class`,`ros`.`type` AS `from_type`,`ros`.`slug` AS `from_slug`,`ros`.`data_source_id` AS `from_data_source_id`,`ros`.`status` AS `from_status`,`ror`.`origin` AS `relation_origin`,`ror`.`relation_type` AS `relation_type`,`ror`.`relation_description` AS `relation_description`,`ror`.`relation_url` AS `relation_url`,`ror`.`related_object_key` AS `to_key`,`rot`.`registry_object_id` AS `to_id`,`rot`.`group` AS `to_group`,`rot`.`title` AS `to_title`,`rot`.`class` AS `to_class`,`rot`.`type` AS `to_type`,`rot`.`slug` AS `to_slug`,`rot`.`data_source_id` AS `to_data_source_id`,`rot`.`status` AS `to_status` from ((`registry_object_relationships` `ror` left join `registry_objects` `ros` on((`ror`.`registry_object_id` = `ros`.`registry_object_id`))) left join `registry_objects` `rot` on((`ror`.`related_object_key` = `rot`.`key`))) where ((`ros`.`status` = 'PUBLISHED') and (`rot`.`status` = 'PUBLISHED')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `relationships_all_status`
--

/*!50001 DROP VIEW IF EXISTS `relationships_all_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`webuser`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `relationships_all_status` AS select `ror`.`registry_object_id` AS `from_id`,`ros`.`key` AS `from_key`,`ros`.`group` AS `from_group`,`ros`.`title` AS `from_title`,`ros`.`class` AS `from_class`,`ros`.`type` AS `from_type`,`ros`.`slug` AS `from_slug`,`ros`.`data_source_id` AS `from_data_source_id`,`ros`.`status` AS `from_status`,`ror`.`origin` AS `relation_origin`,`ror`.`relation_type` AS `relation_type`,`ror`.`relation_description` AS `relation_description`,`ror`.`relation_url` AS `relation_url`,`ror`.`related_object_key` AS `to_key`,`rot`.`registry_object_id` AS `to_id`,`rot`.`group` AS `to_group`,`rot`.`title` AS `to_title`,`rot`.`class` AS `to_class`,`rot`.`type` AS `to_type`,`rot`.`slug` AS `to_slug`,`rot`.`data_source_id` AS `to_data_source_id`,`rot`.`status` AS `to_status` from ((`registry_object_relationships` `ror` left join `registry_objects` `ros` on((`ror`.`registry_object_id` = `ros`.`registry_object_id`))) left join `registry_objects` `rot` on((`ror`.`related_object_key` = `rot`.`key`))) where ((`ros`.`status` <> 'DELETED') and (`rot`.`status` <> 'DELETED')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-06-08 14:42:22
