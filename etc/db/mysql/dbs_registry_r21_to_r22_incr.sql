ALTER TABLE `dbs_registry`.`harvests`
ADD COLUMN `task_id` MEDIUMINT(9) NULL DEFAULT NULL AFTER `importer_message`;

ALTER TABLE `dbs_registry`.`tasks`
CHANGE COLUMN `message` `message` MEDIUMTEXT NULL DEFAULT NULL ,
CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL ;

DROP VIEW `dbs_registry`.`relationships`;
CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `dbs_registry`.`relationships` AS
    SELECT
        `ror`.`registry_object_id` AS `from_id`,
        `ros`.`key` AS `from_key`,
        `ros`.`group` AS `from_group`,
        `ros`.`title` AS `from_title`,
        `ros`.`class` AS `from_class`,
        `ros`.`type` AS `from_type`,
        `ros`.`slug` AS `from_slug`,
        `ros`.`data_source_id` AS `from_data_source_id`,
        `ros`.`status` AS `from_status`,
        `ror`.`origin` AS `relation_origin`,
        `ror`.`relation_type` AS `relation_type`,
        `ror`.`relation_description` AS `relation_description`,
        `ror`.`relation_url` AS `relation_url`,
        `ror`.`related_object_key` AS `to_key`,
        `rot`.`registry_object_id` AS `to_id`,
        `rot`.`group` AS `to_group`,
        `rot`.`title` AS `to_title`,
        `rot`.`class` AS `to_class`,
        `rot`.`type` AS `to_type`,
        `rot`.`slug` AS `to_slug`,
        `rot`.`data_source_id` AS `to_data_source_id`,
        `rot`.`status` AS `to_status`
    FROM
        ((`dbs_registry`.`registry_object_relationships` `ror`
        LEFT JOIN `dbs_registry`.`registry_objects` `ros` ON ((`ror`.`registry_object_id` = `ros`.`registry_object_id`)))
        LEFT JOIN `dbs_registry`.`registry_objects` `rot` ON ((`ror`.`related_object_key` = `rot`.`key`)))
	WHERE
		`ros`.`status` = 'PUBLISHED' AND `rot`.`status` = 'PUBLISHED'

DROP VIEW `dbs_registry`.`identifier_relationships`;
CREATE
    ALGORITHM = UNDEFINED
    DEFINER = `root`@`%`
    SQL SECURITY DEFINER
VIEW `dbs_registry`.`identifier_relationships` AS
    SELECT
        `roir`.`registry_object_id` AS `from_id`,
        `ros`.`key` AS `from_key`,
        `ros`.`group` AS `from_group`,
        `ros`.`title` AS `from_title`,
        `ros`.`class` AS `from_class`,
        `ros`.`type` AS `from_type`,
        `ros`.`slug` AS `from_slug`,
        `ros`.`data_source_id` AS `from_data_source_id`,
        `ros`.`status` AS `from_status`,
        `roir`.`relation_type` AS `relation_type`,
        `roir`.`related_title` AS `relation_to_title`,
        `roir`.`related_url` AS `relation_url`,
        `roir`.`related_description` AS `related_description`,
        `roir`.`related_object_identifier` AS `to_identifier`,
        `roir`.`related_object_identifier_type` AS `to_identifier_type`,
        `roir`.`related_info_type` AS `to_related_info_type`,
        `rot`.`registry_object_id` AS `to_id`,
        `rot`.`key` AS `to_key`,
        `rot`.`group` AS `to_group`,
        `rot`.`title` AS `to_title`,
        `rot`.`class` AS `to_class`,
        `rot`.`type` AS `to_type`,
        `rot`.`slug` AS `to_slug`,
        `rot`.`data_source_id` AS `to_data_source_id`,
        `rot`.`status` AS `to_status`
    FROM
        (((`dbs_registry`.`registry_object_identifier_relationships` `roir`
        LEFT JOIN `dbs_registry`.`registry_objects` `ros` ON ((`roir`.`registry_object_id` = `ros`.`registry_object_id`)))
        LEFT JOIN `dbs_registry`.`registry_object_identifiers` `roidn` ON (((`roir`.`related_object_identifier` = `roidn`.`identifier`)
            AND (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`))))
        LEFT JOIN `dbs_registry`.`registry_objects` `rot` ON ((`roidn`.`registry_object_id` = `rot`.`registry_object_id`)))
	WHERE
		`ros`.`status` = 'PUBLISHED' AND (`rot`.`status` IS NULL OR `rot`.`status` = 'PUBLISHED')

