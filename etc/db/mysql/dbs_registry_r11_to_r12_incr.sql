CREATE TABLE `logs` (
  `type` varchar(32) DEFAULT NULL,
  `id` varchar(128) DEFAULT NULL,
  `msg` text,
  `date_modified` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$

ALTER TABLE `dbs_registry`.`registry_object_tags` 
CHANGE COLUMN `id` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT  , 
ADD COLUMN `date_created` DATETIME NULL  AFTER `tag` , 
ADD COLUMN `type` VARCHAR(45) NOT NULL DEFAULT 'public'  AFTER `date_created` , 
ADD COLUMN `user` VARCHAR(256) NOT NULL  AFTER `type` , 
ADD COLUMN `user_from` VARCHAR(45) NOT NULL  AFTER `user` ;


CREATE  TABLE `dbs_registry`.`tags` (
  `name` VARCHAR(256) NOT NULL ,
  `type` VARCHAR(45) NOT NULL DEFAULT 'public' ,
  `theme` VARCHAR(45) NULL ,
  PRIMARY KEY (`name`) );

ALTER TABLE `dbs_registry`.`logs` CHANGE COLUMN `id` `type_id` VARCHAR(128) NULL DEFAULT NULL  ;
ALTER TABLE `dbs_registry`.`logs` ADD COLUMN `id` BIGINT NOT NULL AUTO_INCREMENT  AFTER `date_modified` , ADD PRIMARY KEY (`id`) ;
ALTER TABLE `dbs_registry`.`logs` CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT  FIRST ;

CREATE  TABLE `dbs_registry`.`theme_pages` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(256) NULL ,
  `slug` VARCHAR(256) NOT NULL ,
  `img_src` VARCHAR(256) NULL ,
  `description` TEXT NULL ,
  `visible` TINYINT NULL ,
  `content` TEXT NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_registry`.`theme_pages` ADD COLUMN `secret_tag` VARCHAR(256) NULL  AFTER `slug` ;

ALTER TABLE `dbs_registry`.`registry_object_relationships` CHANGE COLUMN `id` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT  ;
