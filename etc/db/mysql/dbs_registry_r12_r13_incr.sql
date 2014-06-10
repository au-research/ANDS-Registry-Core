CREATE  TABLE `dbs_registry`.`configs` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `key` VARCHAR(45) NOT NULL ,
  `type` VARCHAR(45) NULL ,
  `value` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_registry`.`configs` CHANGE COLUMN `value` `value` BLOB NULL DEFAULT NULL  ;

CREATE  TABLE `dbs_registry`.`harvests` (
  `harvest_id` MEDIUMINT NOT NULL AUTO_INCREMENT ,
  `data_source_id` MEDIUMINT NOT NULL ,
  `status` VARCHAR(45) NULL ,
  `message` BLOB NULL ,
  `next_run` DATETIME NULL ,
  `last_run` DATETIME NULL ,
  `mode` VARCHAR(45) NULL ,
  `batch_number` VARCHAR(120) NULL ,
  PRIMARY KEY (`harvest_id`) );

ALTER TABLE `dbs_registry`.`data_sources` ADD COLUMN `title` VARCHAR(512) NULL  AFTER `slug` , ADD COLUMN `record_owner` VARCHAR(512) NULL  AFTER `title` ;