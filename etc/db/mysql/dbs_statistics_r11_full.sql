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


