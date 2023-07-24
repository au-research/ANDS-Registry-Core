CREATE DATABASE `dbs_statistics` /*!40100 DEFAULT CHARACTER SET utf8 */


CREATE TABLE `citations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `collection_count` int(11) DEFAULT '0',
  `fullCitation_count` int(11) DEFAULT '0',
  `citationMetadata_count` int(11) DEFAULT '0',
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `identifiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_source_id` int(11) NOT NULL,
  `doi` int(11) NOT NULL DEFAULT '0',
  `orcid` int(11) NOT NULL DEFAULT '0',
  `handle` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_source_id` int(11) NOT NULL,
  `collection_party` int(11) NOT NULL DEFAULT '0',
  `collection_arc` int(11) NOT NULL DEFAULT '0',
  `collection_nhmrc` int(11) NOT NULL DEFAULT '0',
  `collection_other` int(11) NOT NULL DEFAULT '0',
  `researcher_collection` int(11) NOT NULL DEFAULT '0',
  `party_activity` int(11) NOT NULL DEFAULT '0',
  `arc_collection` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


