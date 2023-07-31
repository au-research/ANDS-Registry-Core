ALTER TABLE `dbs_registry`.`tasks`
ADD COLUMN `type` VARCHAR(45) NULL COMMENT '' AFTER `name`,
ADD COLUMN `next_run` TIMESTAMP NULL DEFAULT NULL COMMENT '' AFTER `date_added`,
ADD COLUMN `frequency` VARCHAR(45) NULL COMMENT '' AFTER `priority`,
ADD COLUMN `last_run` TIMESTAMP NULL DEFAULT NULL COMMENT '' AFTER `next_run`;


ALTER TABLE `dbs_registry`.`api_keys`
ADD COLUMN `owner_sector` VARCHAR(45) NULL COMMENT '' AFTER `owner_purpose`,
ADD COLUMN `owner_ip` VARCHAR(45) NULL COMMENT '' AFTER `owner_sector`;

ALTER TABLE `dbs_registry`.`registry_object_metadata`
CHANGE COLUMN `value` `value` MEDIUMTEXT NULL DEFAULT NULL COMMENT '' ;

ALTER TABLE `dbs_registry`.`registry_object_identifier_relationships`
ADD COLUMN `notes` TEXT NULL AFTER `connections_preview_div`;

ALTER TABLE `dbs_registry`.`registry_objects`
ADD COLUMN `type` VARCHAR(45) NULL COMMENT '' AFTER `class`;

UPDATE dbs_registry.registry_objects ro
INNER JOIN dbs_registry.registry_object_attributes roa ON (roa.registry_object_id = ro.registry_object_id AND roa.`attribute` = 'type')
SET ro.`type` = roa.`value`;