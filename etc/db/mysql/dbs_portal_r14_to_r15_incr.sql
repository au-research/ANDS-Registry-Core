CREATE  TABLE `dbs_portal`.`user_data` (
  `role_id` VARCHAR(255) NOT NULL ,
  `user_data` TEXT NULL ,
  PRIMARY KEY (`role_id`) );
ALTER TABLE `dbs_portal`.`user_data` CHANGE COLUMN `user_data` `user_data` LONGTEXT NULL DEFAULT NULL  ;

CREATE TABLE `dbs_portal`.`contributor_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `authorative_datasource` int(11) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `data` text,
  `date_modified` datetime DEFAULT NULL,
  `modified_who` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

CREATE  TABLE `dbs_portal`.`record_stats` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ro_id` BIGINT NULL ,
  `ro_slug` VARCHAR(255) NULL ,
  `viewed` INT NULL DEFAULT 0 ,
  `cited` INT NULL DEFAULT 0 ,
  `accessed` INT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) );