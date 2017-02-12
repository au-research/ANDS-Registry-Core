CREATE  TABLE `dbs_registry`.`tasks` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(256) NULL ,
  `status` VARCHAR(45) NULL ,
  `message` TEXT NULL ,
  `date_added` TIMESTAMP NULL ,
  `priority` INT NULL ,
  `params` VARCHAR(512) NULL ,
  PRIMARY KEY (`id`) );

CREATE TABLE `registry_object_citations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `service_provider` varchar(32) NOT NULL,
  `query_terms` varchar(512) DEFAULT NULL,
  `citation_data` text,
  `status` varchar(32),
  `Last_checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
