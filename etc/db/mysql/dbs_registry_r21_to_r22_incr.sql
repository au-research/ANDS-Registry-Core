ALTER TABLE `dbs_registry`.`harvests`
ADD COLUMN `task_id` MEDIUMINT(9) NULL DEFAULT NULL AFTER `importer_message`;

ALTER TABLE `dbs_registry`.`tasks`
CHANGE COLUMN `message` `message` MEDIUMTEXT NULL DEFAULT NULL ,
CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL ;
