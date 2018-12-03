CREATE TABLE `dci` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `registry_object_group` varchar(255) NOT NULL,
  `registry_object_data_source_id` mediumint(8) NOT NULL,
  `data` BLOB,
  `hash`varchar(45),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP VIEW `dbs_registry`.`identifier_relationships_all_status`;

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
    'IDENTIFIER' AS `relation_origin`,
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
  WHERE ros.status != 'DELETED' AND (`rot`.`status` IS NULL OR `rot`.`status` = 'PUBLISHED');