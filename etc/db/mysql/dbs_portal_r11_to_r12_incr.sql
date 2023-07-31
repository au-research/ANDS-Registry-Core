CREATE  TABLE `dbs_portal`.`users` (
  `id` BIGINT NOT NULL ,
  `identifier` VARCHAR(128) NULL ,
  `displayName` VARCHAR(256) NULL ,
  `status` VARCHAR(45) NULL ,
  `provider` VARCHAR(45) NULL ,
  `profile` TEXT NULL ,
  PRIMARY KEY (`id`) );
ALTER TABLE `dbs_portal`.`users` CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT  ;
ALTER TABLE `dbs_portal`.`users` ADD COLUMN `access_token` TEXT NULL  AFTER `profile` ;