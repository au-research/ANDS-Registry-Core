ALTER TABLE `dbs_registry`.`harvests`
ADD COLUMN `task_id` MEDIUMINT(9) NULL DEFAULT NULL AFTER `importer_message`;