CREATE DATABASE  IF NOT EXISTS `dbs_registry` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `dbs_registry`;
-- MySQL dump 10.13  Distrib 5.5.24, for osx10.5 (i386)
--
-- Host: devl.ands.org.au    Database: dbs_registry
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
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `api_key` varchar(32) NOT NULL,
  `owner_email` varchar(45) DEFAULT NULL,
  `owner_organisation` varchar(45) DEFAULT NULL,
  `owner_purpose` text,
  `created` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`api_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_requests`
--

DROP TABLE IF EXISTS `api_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `api_key` varchar(45) DEFAULT NULL,
  `service` varchar(45) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `timestamp` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1426735 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `background_tasks`
--

DROP TABLE IF EXISTS `background_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `background_tasks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(45) NOT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  `completed` int(10) unsigned DEFAULT NULL,
  `prerequisite_task` mediumint(8) unsigned DEFAULT NULL,
  `log_message` text,
  `param_1` text,
  `param_2` text,
  `status` varchar(45) NOT NULL DEFAULT 'QUEUED',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
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
  KEY `configkey_index` (`key`),
  KEY `id_index` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
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
  `attribute` varchar(32) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `fk_data_sources` (`data_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8969 DEFAULT CHARSET=utf8;
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
  `type` varchar(45) DEFAULT NULL,
  `log` text,
  `class` varchar(45) DEFAULT NULL,
  `harvester_error_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_data_source` (`data_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=195262 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_sources`
--

DROP TABLE IF EXISTS `data_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_sources` (
  `data_source_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(512) DEFAULT NULL,
  `record_owner` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`data_source_id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  UNIQUE KEY `slug_UNIQUE` (`slug`),
  UNIQUE KEY `id_index` (`data_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=202 DEFAULT CHARSET=utf8;
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
  `key` varchar(255) NOT NULL,
  `deleted` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `record_data` text,
  `datasource` varchar(255) DEFAULT NULL,
  `class` varchar(45) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_data_source_id_idx` (`data_source_id`),
  KEY `key_index` (`key`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=255249 DEFAULT CHARSET=utf8;
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
  `status` varchar(45) DEFAULT NULL,
  `next_harvest` varchar(30) DEFAULT NULL,
  `harvest_frequency` varchar(45) DEFAULT NULL,
  `oai_set` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_harvest_request_data_source` (`data_source_id`),
  KEY `fk_harvest_data_source` (`data_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12361 DEFAULT CHARSET=utf8;
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
  PRIMARY KEY (`harvest_id`),
  KEY `statusnextrun_index` (`status`,`next_run`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutional_pages`
--

DROP TABLE IF EXISTS `institutional_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutional_pages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `authorative_data_source_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pk_group` (`group`),
  KEY `fk_data_source` (`authorative_data_source_id`)
) ENGINE=MyISAM AUTO_INCREMENT=683 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) DEFAULT NULL,
  `type_id` varchar(128) DEFAULT NULL,
  `msg` text,
  `date_modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4900 DEFAULT CHARSET=latin1;
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
  `current` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
  `data` mediumblob,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `scheme` varchar(45) NOT NULL DEFAULT 'rif',
  `hash` varchar(45) NOT NULL DEFAULT 'unhashed',
  PRIMARY KEY (`id`),
  KEY `ro_selector` (`registry_object_id`,`current`),
  KEY `registry_object_id_UNIQUE` (`registry_object_id`,`id`),
  KEY `fk_record_data_registry_object` (`registry_object_id`,`current`)
) ENGINE=MyISAM AUTO_INCREMENT=6728221 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=6887732 DEFAULT CHARSET=utf8;
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
  `relation_type` varchar(45) DEFAULT NULL,
  `related_title` varchar(255) DEFAULT NULL,
  `related_url` varchar(255) DEFAULT NULL,
  `related_description` varchar(512) DEFAULT NULL,
  `connections_preview_div` text,
  PRIMARY KEY (`id`),
  KEY `idx_registry_object_id` (`registry_object_id`),
  KEY `idx_identifier` (`related_object_identifier`)
) ENGINE=MyISAM AUTO_INCREMENT=109148 DEFAULT CHARSET=latin1;
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
) ENGINE=MyISAM AUTO_INCREMENT=2575837 DEFAULT CHARSET=latin1;
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
  KEY `idx_registry_object_id_rol` (`registry_object_id`),
  KEY `idx_link_rol` (`link`(255))
) ENGINE=InnoDB AUTO_INCREMENT=867376 DEFAULT CHARSET=utf8;
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
  `attribute` varchar(32) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_reg_metadata` (`registry_object_id`,`attribute`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=1484556 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_relationships`
--

DROP TABLE IF EXISTS `registry_object_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_relationships` (
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_key` varchar(255) NOT NULL,
  `related_object_class` enum('collection','service','party','activity') DEFAULT NULL,
  `origin` varchar(32) NOT NULL DEFAULT 'EXPLICIT',
  `relation_type` varchar(512) DEFAULT NULL,
  `relation_description` varchar(512) DEFAULT NULL,
  `relation_url` varchar(255) DEFAULT NULL,
  KEY `idx_related_object_id` (`registry_object_id`) USING HASH,
  KEY `idx_related_object_key` (`related_object_key`) USING HASH,
  KEY `idx_related_object_pair` (`registry_object_id`,`related_object_key`) USING HASH
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `registry_object_tags`
--

DROP TABLE IF EXISTS `registry_object_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_object_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `type` varchar(45) NOT NULL DEFAULT 'public',
  `user` varchar(256) NOT NULL,
  `user_from` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tagkey_index` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=14912 DEFAULT CHARSET=latin1;
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
  `key` varchar(255) NOT NULL,
  `class` enum('collection','service','activity','party') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(45) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `record_owner` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`registry_object_id`),
  KEY `fk_registry_object_data_source` (`data_source_id`) USING HASH,
  KEY `slug_lookup` (`slug`) USING HASH,
  KEY `key_class_index` (`key`,`class`,`status`) USING HASH,
  KEY `key_index` (`class`,`data_source_id`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=533063 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_statistics`
--

DROP TABLE IF EXISTS `search_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_statistics` (
  `search_term` varchar(255) NOT NULL,
  `occurence` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`search_term`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
  `coordinates` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_spatial_extent_registry_object` (`registry_object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `name` varchar(256) NOT NULL,
  `type` varchar(45) NOT NULL DEFAULT 'public',
  `theme` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
  `status` varchar(45) DEFAULT NULL,
  `message` text,
  `date_added` timestamp NULL DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `params` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5292 DEFAULT CHARSET=utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `url_mappings`
--

DROP TABLE IF EXISTS `url_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_mappings` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `registry_object_id` mediumint(8) unsigned DEFAULT NULL,
  `search_title` varchar(255) DEFAULT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  `updated` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slug_INDEX` (`slug`) USING HASH,
  KEY `idx_url_to_registry_object` (`registry_object_id`) USING HASH,
  KEY `fk_url_map_to_registry_object` (`slug`,`registry_object_id`) USING HASH
) ENGINE=MyISAM AUTO_INCREMENT=792396 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-24 12:26:20
