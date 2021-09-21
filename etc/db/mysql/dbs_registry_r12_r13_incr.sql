CREATE  TABLE `configs` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `key` VARCHAR(45) NOT NULL ,
  `type` VARCHAR(45) NULL ,
  `value` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `configs` CHANGE COLUMN `value` `value` BLOB NULL DEFAULT NULL  ;

delimiter $$

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
  PRIMARY KEY (`harvest_id`)
);

ALTER TABLE `data_sources` ADD COLUMN `title` VARCHAR(512) NULL  AFTER `slug` , ADD COLUMN `record_owner` VARCHAR(512) NULL  AFTER `title` ;

ALTER TABLE `registry_object_identifier_relationships` 
ADD INDEX `idx_registry_object_id` (`registry_object_id` ASC) 
, ADD INDEX `idx_identifier` (`related_object_identifier` ASC) ;

ALTER TABLE `registry_objects` 
DROP INDEX `slug_UNIQUE` ;

ALTER TABLE `url_mappings` 
DROP INDEX `slug_UNIQUE` ;

