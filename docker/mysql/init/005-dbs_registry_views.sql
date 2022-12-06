DROP VIEW IF EXISTS `identifier_relationships`;
DROP VIEW IF EXISTS `identifier_relationships_all_status`;
DROP VIEW IF EXISTS `implicit_relationships`;
DROP VIEW IF EXISTS `relationships`;
DROP VIEW IF EXISTS `relationships_all_status`;

CREATE
SQL SECURITY DEFINER VIEW `identifier_relationships` AS
select `roir`.`registry_object_id`             AS `from_id`,
       `ros`.`key`                             AS `from_key`,
       `ros`.`group`                           AS `from_group`,
       `ros`.`title`                           AS `from_title`,
       `ros`.`class`                           AS `from_class`,
       `ros`.`type`                            AS `from_type`,
       `ros`.`slug`                            AS `from_slug`,
       `ros`.`data_source_id`                  AS `from_data_source_id`,
       `ros`.`status`                          AS `from_status`,
       'IDENTIFIER'                            AS `relation_origin`,
       `roir`.`id`                             AS `relation_identifier_id`,
       `roir`.`relation_type`                  AS `relation_type`,
       `roir`.`related_title`                  AS `relation_to_title`,
       `roir`.`related_url`                    AS `relation_url`,
       `roir`.`related_description`            AS `related_description`,
       `roir`.`related_object_identifier`      AS `to_identifier`,
       `roir`.`related_object_identifier_type` AS `to_identifier_type`,
       `roir`.`related_info_type`              AS `to_related_info_type`,
       `rot`.`registry_object_id`              AS `to_id`,
       `rot`.`key`                             AS `to_key`,
       `rot`.`group`                           AS `to_group`,
       `rot`.`title`                           AS `to_title`,
       `rot`.`class`                           AS `to_class`,
       `rot`.`type`                            AS `to_type`,
       `rot`.`slug`                            AS `to_slug`,
       `rot`.`data_source_id`                  AS `to_data_source_id`,
       `rot`.`status`                          AS `to_status`
from (((`registry_object_identifier_relationships` `roir` left join `registry_objects` `ros` on ((`roir`.`registry_object_id` = `ros`.`registry_object_id`))) left join `registry_object_identifiers` `roidn` on ((
        (`roir`.`related_object_identifier` = `roidn`.`identifier`) and
        (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`))))
         left join `registry_objects` `rot` on ((`roidn`.`registry_object_id` = `rot`.`registry_object_id`)))
where ((`ros`.`status` = 'PUBLISHED') and (isnull(`rot`.`status`) or (`rot`.`status` = 'PUBLISHED')));
CREATE
SQL SECURITY DEFINER VIEW `identifier_relationships_all_status` AS
select `roir`.`registry_object_id`             AS `from_id`,
       `ros`.`key`                             AS `from_key`,
       `ros`.`group`                           AS `from_group`,
       `ros`.`title`                           AS `from_title`,
       `ros`.`class`                           AS `from_class`,
       `ros`.`type`                            AS `from_type`,
       `ros`.`slug`                            AS `from_slug`,
       `ros`.`data_source_id`                  AS `from_data_source_id`,
       `ros`.`status`                          AS `from_status`,
       'IDENTIFIER'                            AS `relation_origin`,
       `roir`.`id`                             AS `relation_identifier_id`,
       `roir`.`relation_type`                  AS `relation_type`,
       `roir`.`related_title`                  AS `relation_to_title`,
       `roir`.`related_url`                    AS `relation_url`,
       `roir`.`related_description`            AS `related_description`,
       `roir`.`related_object_identifier`      AS `to_identifier`,
       `roir`.`related_object_identifier_type` AS `to_identifier_type`,
       `roir`.`related_info_type`              AS `to_related_info_type`,
       `rot`.`registry_object_id`              AS `to_id`,
       `rot`.`key`                             AS `to_key`,
       `rot`.`group`                           AS `to_group`,
       `rot`.`title`                           AS `to_title`,
       `rot`.`class`                           AS `to_class`,
       `rot`.`type`                            AS `to_type`,
       `rot`.`slug`                            AS `to_slug`,
       `rot`.`data_source_id`                  AS `to_data_source_id`,
       `rot`.`status`                          AS `to_status`
from (((`registry_object_identifier_relationships` `roir` left join `registry_objects` `ros` on ((`roir`.`registry_object_id` = `ros`.`registry_object_id`))) left join `registry_object_identifiers` `roidn` on ((
        (`roir`.`related_object_identifier` = `roidn`.`identifier`) and
        (`roir`.`related_object_identifier_type` = `roidn`.`identifier_type`))))
         left join `registry_objects` `rot` on ((`roidn`.`registry_object_id` = `rot`.`registry_object_id`)))
where ((`ros`.`status` <> 'DELETED') and (isnull(`rot`.`status`) or (`rot`.`status` = 'PUBLISHED')));
CREATE
SQL SECURITY DEFINER VIEW `implicit_relationships` AS
select `ros`.`key`              AS `from_key`,
       `ros`.`group`            AS `from_group`,
       `ros`.`title`            AS `from_title`,
       `ros`.`class`            AS `from_class`,
       `ros`.`type`             AS `from_type`,
       `ros`.`slug`             AS `from_slug`,
       `ros`.`data_source_id`   AS `from_data_source_id`,
       `ros`.`status`           AS `from_status`,
       `roir`.`from_id`         AS `from_id`,
       `roir`.`to_id`           AS `to_id`,
       `roir`.`relation_type`   AS `relation_type`,
       `roir`.`relation_origin` AS `relation_origin`,
       `rot`.`key`              AS `to_key`,
       `rot`.`group`            AS `to_group`,
       `rot`.`title`            AS `to_title`,
       `rot`.`class`            AS `to_class`,
       `rot`.`type`             AS `to_type`,
       `rot`.`slug`             AS `to_slug`,
       `rot`.`data_source_id`   AS `to_data_source_id`,
       `rot`.`status`           AS `to_status`
from ((`registry_object_implicit_relationships` `roir` left join `registry_objects` `ros` on ((`roir`.`from_id` = `ros`.`registry_object_id`)))
         left join `registry_objects` `rot` on ((`roir`.`to_id` = `rot`.`registry_object_id`)))
where ((`ros`.`status` = 'PUBLISHED') and (`rot`.`status` = 'PUBLISHED'));

CREATE
SQL SECURITY DEFINER VIEW `relationships` AS
select `ror`.`registry_object_id`   AS `from_id`,
       `ros`.`key`                  AS `from_key`,
       `ros`.`group`                AS `from_group`,
       `ros`.`title`                AS `from_title`,
       `ros`.`class`                AS `from_class`,
       `ros`.`type`                 AS `from_type`,
       `ros`.`slug`                 AS `from_slug`,
       `ros`.`data_source_id`       AS `from_data_source_id`,
       `ros`.`status`               AS `from_status`,
       `ror`.`origin`               AS `relation_origin`,
       `ror`.`relation_type`        AS `relation_type`,
       `ror`.`relation_description` AS `relation_description`,
       `ror`.`relation_url`         AS `relation_url`,
       `ror`.`related_object_key`   AS `to_key`,
       `rot`.`registry_object_id`   AS `to_id`,
       `rot`.`group`                AS `to_group`,
       `rot`.`title`                AS `to_title`,
       `rot`.`class`                AS `to_class`,
       `rot`.`type`                 AS `to_type`,
       `rot`.`slug`                 AS `to_slug`,
       `rot`.`data_source_id`       AS `to_data_source_id`,
       `rot`.`status`               AS `to_status`
from ((`registry_object_relationships` `ror` left join `registry_objects` `ros` on ((`ror`.`registry_object_id` = `ros`.`registry_object_id`)))
         left join `registry_objects` `rot` on ((`ror`.`related_object_key` = `rot`.`key`)))
where ((`ros`.`status` = 'PUBLISHED') and (`rot`.`status` = 'PUBLISHED'));

CREATE
SQL SECURITY DEFINER VIEW `relationships_all_status` AS
select `ror`.`registry_object_id`   AS `from_id`,
       `ros`.`key`                  AS `from_key`,
       `ros`.`group`                AS `from_group`,
       `ros`.`title`                AS `from_title`,
       `ros`.`class`                AS `from_class`,
       `ros`.`type`                 AS `from_type`,
       `ros`.`slug`                 AS `from_slug`,
       `ros`.`data_source_id`       AS `from_data_source_id`,
       `ros`.`status`               AS `from_status`,
       `ror`.`origin`               AS `relation_origin`,
       `ror`.`relation_type`        AS `relation_type`,
       `ror`.`relation_description` AS `relation_description`,
       `ror`.`relation_url`         AS `relation_url`,
       `ror`.`related_object_key`   AS `to_key`,
       `rot`.`registry_object_id`   AS `to_id`,
       `rot`.`group`                AS `to_group`,
       `rot`.`title`                AS `to_title`,
       `rot`.`class`                AS `to_class`,
       `rot`.`type`                 AS `to_type`,
       `rot`.`slug`                 AS `to_slug`,
       `rot`.`data_source_id`       AS `to_data_source_id`,
       `rot`.`status`               AS `to_status`
from ((`registry_object_relationships` `ror` left join `registry_objects` `ros` on ((`ror`.`registry_object_id` = `ros`.`registry_object_id`)))
         left join `registry_objects` `rot` on ((`ror`.`related_object_key` = `rot`.`key`)))
where ((`ros`.`status` <> 'DELETED') and (`rot`.`status` <> 'DELETED'));