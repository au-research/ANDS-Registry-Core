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

CREATE  TABLE `dbs_portal`.`record_stats` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ro_id` BIGINT NULL ,
  `ro_slug` VARCHAR(256) NULL ,
  `viewed` INT NULL DEFAULT 0 ,
  `cited` INT NULL DEFAULT 0 ,
  `accessed` INT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) );