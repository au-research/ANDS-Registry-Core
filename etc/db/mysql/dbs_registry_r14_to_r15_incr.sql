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
  PRIMARY KEY (`id`),
  KEY `idx_data_source_id_roc` (`data_source_id`),
  KEY `idx_registry_object_id_roc` (`registry_object_id`),
  CONSTRAINT `fk_roc_registry_object_id` FOREIGN KEY (`registry_object_id`)
    REFERENCES `registry_objects` (`registry_object_id`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_roc_data_source_id` FOREIGN KEY (`data_source_id`)
    REFERENCES `data_sources` (`data_source_id`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
