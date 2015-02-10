CREATE  TABLE `dbs_portal`.`user_data` (
  `role_id` VARCHAR(256) NOT NULL ,
  `user_data` TEXT NULL ,
  PRIMARY KEY (`role_id`) );

CREATE TABLE `contributor_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `authorative_datasource` int(11) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `data` text,
  `date_modified` datetime DEFAULT NULL,
  `modified_who` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;

CREATE  TABLE `dbs_portal`.`record_stats` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ro_id` BIGINT NULL ,
  `ro_slug` VARCHAR(256) NULL ,
  `viewed` INT NULL DEFAULT 0 ,
  `cited` INT NULL DEFAULT 0 ,
  `accessed` INT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) );