ALTER TABLE `deleted_registry_objects` ADD COLUMN `group` VARCHAR(255) NULL  AFTER `record_data`;
ALTER TABLE `deleted_registry_objects` ADD COLUMN `class` VARCHAR(45) NULL  AFTER `record_data`;
ALTER TABLE `deleted_registry_objects` ADD COLUMN `datasource` VARCHAR(255) NULL  AFTER `record_data`;