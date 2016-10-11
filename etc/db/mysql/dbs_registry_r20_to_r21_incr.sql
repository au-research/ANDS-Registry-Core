ALTER TABLE `dbs_registry`.`tasks`
ADD COLUMN `data` TEXT NULL AFTER `params`;

CREATE INDEX record_data_registry_object_id_index ON
dbs_registry.record_data (registry_object_id);