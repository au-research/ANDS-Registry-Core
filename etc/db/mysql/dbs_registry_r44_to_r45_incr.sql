use dbs_registry;

ALTER TABLE `dbs_registry`.`orcid_records`
    CHANGE COLUMN `record_data` `record_data` LONGTEXT NULL DEFAULT NULL ;