ALTER TABLE `dbs_registry`.`harvests`
ADD COLUMN `task_id` MEDIUMINT(9) NULL DEFAULT NULL AFTER `importer_message`;

ALTER TABLE `dbs_registry`.`tasks`
CHANGE COLUMN `message` `message` MEDIUMTEXT NULL DEFAULT NULL ,
CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL ;

DROP VIEW `dbs_registry`.`relationships`;
CREATE
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
		`ros`.`status` = 'PUBLISHED' AND `rot`.`status` = 'PUBLISHED';

DROP VIEW `dbs_registry`.`identifier_relationships`;
CREATE
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
        `roir`.`id` AS `relation_identifier_id`,
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
		`ros`.`status` = 'PUBLISHED' AND (`rot`.`status` IS NULL OR `rot`.`status` = 'PUBLISHED');

CREATE TABLE `dbs_registry`.`registry_object_implicit_relationships` (
  `from_id` MEDIUMINT(8) NOT NULL,
  `to_id` MEDIUMINT(8) NOT NULL,
  `relation_type` VARCHAR(512) NULL,
  `relation_origin` VARCHAR(32) NULL);


create view dbs_registry.`implicit_relationships` as
  select
         ros.key as `from_key`,
         ros.group as `from_group` ,
         ros.title as `from_title` ,
         ros.class as `from_class`,
         ros.type as `from_type`,
         ros.slug as `from_slug`,
         ros.data_source_id as `from_data_source_id`,
         ros.status as `from_status`,
    roir.*,
         rot.key as `to_key`,
         rot.group as `to_group` ,
         rot.title as `to_title`,
         rot.class as `to_class`,
         rot.type as `to_type`,
         rot.slug as `to_slug`,
         rot.data_source_id as `to_data_source_id`,
         rot.status as `to_status`
  from dbs_registry.registry_object_implicit_relationships roir
    left join dbs_registry.registry_objects ros on roir.from_id = ros.registry_object_id
    left outer join dbs_registry.registry_objects rot on roir.to_id = rot.registry_object_id
  WHERE
    ros.status = 'PUBLISHED' AND rot.status = 'PUBLISHED';


ALTER TABLE `dbs_registry`.`registry_object_implicit_relationships`
ADD INDEX `FROM_TO_RELATION` (`from_id` ASC, `to_id` ASC, `relation_type` ASC);

ALTER TABLE `dbs_registry`.`registry_object_identifier_relationships`
ADD INDEX `idx_relation_type` (`relation_type` ASC);

ALTER TABLE `dbs_registry`.`registry_objects`
ADD INDEX `idx_key` (`key` ASC),
ADD INDEX `idx_class` (`class` ASC),
ADD INDEX `idx_type` (`type` ASC);

ALTER TABLE `dbs_registry`.`registry_object_relationships`
ADD INDEX `idx_relation_type` (`relation_type` ASC);

ALTER TABLE `dbs_registry`.`tasks`
ADD INDEX `idx_status` (`status` ASC),
ADD INDEX `idx_next_run` (`next_run` ASC),
ADD INDEX `idx_type` (`type` ASC);

ALTER TABLE `dbs_registry`.`registry_objects`
ADD INDEX `idx_key_id` (`registry_object_id` ASC, `key` ASC);

ALTER TABLE `dbs_registry`.`registry_object_implicit_relationships`
ADD INDEX `idx_from` (`from_id` ASC),
ADD INDEX `idx_to` (`to_id` ASC),
ADD INDEX `idx_relation_type` (`relation_type` ASC),
ADD INDEX `idx_relation_origin` (`relation_origin` ASC);

ALTER TABLE `dbs_registry`.`harvests`
ADD INDEX `idx_data_source_id` (`data_source_id` ASC);

USE `dbs_registry`;
CREATE
     OR REPLACE ALGORITHM = UNDEFINED
    SQL SECURITY DEFINER
VIEW `identifier_relationships` AS
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
        `roir`.`id` AS `relation_identifier_id`,
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
        (((`registry_object_identifier_relationships` `roir`
        LEFT JOIN `registry_objects` `ros` ON ((`roir`.`registry_object_id` = `ros`.`registry_object_id`)))
        LEFT JOIN `registry_object_identifiers` `roidn` ON (((`roir`.`related_object_identifier` = `roidn`.`identifier`)
            AND (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`))))
        LEFT JOIN `registry_objects` `rot` ON ((`roidn`.`registry_object_id` = `rot`.`registry_object_id`)))
    WHERE
        ((`ros`.`status` = 'PUBLISHED')
            AND (ISNULL(`rot`.`status`)
            OR (`rot`.`status` = 'PUBLISHED')));


CREATE
VIEW `dbs_registry`.`relationships_all_status` AS
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
  WHERE ros.status != 'DELETED' AND rot.status != 'DELETED';

CREATE
VIEW `dbs_registry`.`identifier_relationships_all_status` AS
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
    `roir`.`id` AS `relation_identifier_id`,
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
  WHERE ros.status != 'DELETED' AND rot.status != 'DELETED';