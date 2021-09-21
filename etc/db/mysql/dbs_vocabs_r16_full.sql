
DROP TABLE IF EXISTS `related`;
CREATE TABLE `related` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) DEFAULT NULL,
  `relation` varchar(45) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=utf8;


DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vocabulary_id` int(11) NOT NULL,
  `version_id` int(11) NOT NULL,
  `params` text,
  `status` varchar(45) DEFAULT NULL,
  `response` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `versions`;
CREATE TABLE `versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `release_date` timestamp NULL DEFAULT NULL,
  `vocab_id` int(11) DEFAULT NULL,
  `data` text,
  `repository_id` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `vocabularies`;
CREATE TABLE `vocabularies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `created_date` timestamp NULL DEFAULT NULL,
  `modified_date` timestamp NULL DEFAULT NULL,
  `modified_who` varchar(45) DEFAULT NULL,
  `licence` text,
  `pool_party_id` varchar(45) DEFAULT NULL,
  `data` text,
  `owner` varchar(255) DEFAULT NULL,
  `user_owner` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

