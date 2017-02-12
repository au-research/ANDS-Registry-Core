ALTER TABLE `dbs_roles`.`roles` ADD COLUMN `shared_token` VARCHAR(255) NULL  AFTER `last_login` , ADD COLUMN `persistent_id` VARCHAR(255) NULL  AFTER `shared_token` ;
