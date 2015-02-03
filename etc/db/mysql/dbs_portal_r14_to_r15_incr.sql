CREATE  TABLE `dbs_portal`.`user_data` (
  `role_id` VARCHAR(256) NOT NULL ,
  `user_data` TEXT NULL ,
  PRIMARY KEY (`role_id`) );

CREATE  TABLE `dbs_portal`.`contributor_pages` (
  `id` INT NOT NULL ,
  `name` VARCHAR(256) NULL ,
  `authorative_datasource` INT NULL ,
  `status` VARCHAR(45) NULL ,
  `data` TEXT NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_portal`.`contributor_pages` CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT  ;
ALTER TABLE `dbs_portal`.`contributor_pages` ADD COLUMN `date_modified` DATETIME NULL  AFTER `data` ;