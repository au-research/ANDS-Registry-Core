CREATE  TABLE `dbs_registry`.`tasks` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(256) NULL ,
  `status` VARCHAR(45) NULL ,
  `message` TEXT NULL ,
  `date_added` TIMESTAMP NULL ,
  `priority` INT NULL ,
  `params` VARCHAR(512) NULL ,
  PRIMARY KEY (`id`) );