ALTER TABLE `dbs_registry`.`tasks`
ADD COLUMN `type` VARCHAR(45) NULL COMMENT '' AFTER `name`,
ADD COLUMN `next_run` TIMESTAMP NULL DEFAULT NULL COMMENT '' AFTER `date_added`,
ADD COLUMN `frequency` VARCHAR(45) NULL COMMENT '' AFTER `priority`,
ADD COLUMN `last_run` TIMESTAMP NULL DEFAULT NULL COMMENT '' AFTER `next_run`;


ALTER TABLE `dbs_registry`.`api_keys`
ADD COLUMN `owner_sector` VARCHAR(45) NULL COMMENT '' AFTER `owner_purpose`,
ADD COLUMN `owner_ip` VARCHAR(45) NULL COMMENT '' AFTER `owner_sector`;
