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

ALTER TABLE `dbs_registry`.`logs` CHANGE COLUMN `id` `type_id` VARCHAR(128) NULL DEFAULT NULL  ;
ALTER TABLE `dbs_registry`.`logs` ADD COLUMN `id` BIGINT NOT NULL AUTO_INCREMENT  AFTER `date_modified` , ADD PRIMARY KEY (`id`) ;

