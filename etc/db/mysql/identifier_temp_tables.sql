use dbs_registry;
-- these Identifier 'normalised' tables and view is used to detect relationships that are modified by the normalisation process
-- eg they were not matched under previous conditions but they are found to be matching after normalisation took effect
CREATE TABLE `dbs_registry`.`registry_object_identifiers_normalised` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`registry_object_id` mediumint(8) unsigned NOT NULL,
`identifier` varchar(255) NOT NULL,
`identifier_type` varchar(45) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx_registry_object_id` (`registry_object_id`) USING BTREE,
KEY `idx_identifier_pairs` (`identifier`,`identifier_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `dbs_registry`.`registry_object_identifier_relationships_normalised` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`registry_object_id` mediumint(8) unsigned NOT NULL,
`related_object_identifier` varchar(255) NOT NULL,
`related_info_type` varchar(45) DEFAULT NULL,
`related_object_identifier_type` varchar(45) NOT NULL,
`relation_type` varchar(45) DEFAULT 'hasAssociationWith',
`related_title` varchar(512) DEFAULT NULL,
`related_url` varchar(255) DEFAULT NULL,
`related_description` varchar(512) DEFAULT NULL,
`connections_preview_div` text,
`notes` text,
PRIMARY KEY (`id`),
KEY `idx_identifier` (`related_object_identifier`),
KEY `idx_registry_object_id` (`registry_object_id`),
KEY `idx_relation_type` (`relation_type`),
KEY `idx_related_object_identifier` (`related_object_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `dbs_registry`.`registry_object_identifier_relationships` MODIFY COLUMN `related_title` varchar(512) DEFAULT NULL;

CREATE VIEW `dbs_registry`.`identifier_relationships_normalised` AS
select `roir`.`registry_object_id`
AS `from_id`,`ros`.`key`
AS `from_key`,`ros`.`group`
AS `from_group`,`ros`.`title`
AS `from_title`,`ros`.`class`
AS `from_class`,`ros`.`type`
AS `from_type`,`ros`.`slug`
AS `from_slug`,`ros`.`data_source_id`
AS `from_data_source_id`,`ros`.`status`
AS `from_status`,'IDENTIFIER'
AS `relation_origin`,`roir`.`id`
AS `relation_identifier_id`,`roir`.`relation_type`
AS `relation_type`,`roir`.`related_title`
AS `relation_to_title`,`roir`.`related_url`
AS `relation_url`,`roir`.`related_description`
AS `related_description`,`roir`.`related_object_identifier`
AS `to_identifier`,`roir`.`related_object_identifier_type`
AS `to_identifier_type`,`roir`.`related_info_type`
AS `to_related_info_type`,`rot`.`registry_object_id`
AS `to_id`,`rot`.`key`
AS `to_key`,`rot`.`group`
AS `to_group`,`rot`.`title`
AS `to_title`,`rot`.`class`
AS `to_class`,`rot`.`type`
AS `to_type`,`rot`.`slug`
AS `to_slug`,`rot`.`data_source_id`
AS `to_data_source_id`,`rot`.`status`
AS `to_status`
from (((`dbs_registry`.`registry_object_identifier_relationships_normalised` `roir`
    left join `dbs_registry`.`registry_objects` `ros` on((`roir`.`registry_object_id` = `ros`.`registry_object_id`)))
    left join `dbs_registry`.`registry_object_identifiers_normalised` `roidn` on(((`roir`.`related_object_identifier` = `roidn`.`identifier`)
        and (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`))))
         left join `dbs_registry`.`registry_objects` `rot` on((`roidn`.`registry_object_id` = `rot`.`registry_object_id`)))
where ((`ros`.`status` = 'PUBLISHED') and (isnull(`rot`.`status`) or (`rot`.`status` = 'PUBLISHED')));

-- after the "php ands.php run normaliseIdentifier" script is successfully ran,
-- the tables and the view can be dropped

-- DROP TABLE `dbs_registry`.`registry_object_identifier_relationships_normalised`;
-- DROP TABLE `dbs_registry`.`registry_object_identifiers_normalised`;
-- DROP VIEW `dbs_registry`.`identifier_relationships_normalised`;
