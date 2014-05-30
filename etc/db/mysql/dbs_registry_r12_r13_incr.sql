CREATE  TABLE `dbs_registry`.`configs` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `key` VARCHAR(45) NOT NULL ,
  `type` VARCHAR(45) NULL ,
  `value` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_registry`.`configs` CHANGE COLUMN `value` `value` BLOB NULL DEFAULT NULL  ;

