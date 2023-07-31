ALTER TABLE `dbs_roles`.`roles` ADD COLUMN `oauth_access_token` VARCHAR(255) NULL DEFAULT NULL  AFTER `email` , ADD COLUMN `oauth_data` TEXT NULL DEFAULT NULL  AFTER `oauth_access_token` ;
