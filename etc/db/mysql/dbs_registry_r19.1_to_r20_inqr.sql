USE `dbs_registry`;

ALTER TABLE registry_objects ADD `group` varchar(512) NOT NULL default 'not available';

UPDATE registry_objects ro
  INNER JOIN registry_object_attributes roa ON (roa.registry_object_id = ro.registry_object_id AND roa.`attribute` = 'group')
SET ro.`group` = roa.`value`;

DELETE FROM registry_object_attributes WHERE attribute = 'group';

create view `relationships` AS
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
        ((`registry_object_relationships` `ror`
        LEFT JOIN `registry_objects` `ros` ON ((`ror`.`registry_object_id` = `ros`.`registry_object_id`)))
        LEFT JOIN `registry_objects` `rot` ON ((`ror`.`related_object_key` = `rot`.`key`)))


create view `identifier_relationships` as
  select
    roir.registry_object_id as `from_id`,
        ros.key as `from_key`,
         ros.group as `from_group` ,
         ros.title as `from_title` ,
         ros.class as `from_class`,
         ros.type as `from_type`,
         ros.slug as `from_slug`,
         ros.data_source_id as `from_data_source_id`,
         ros.status as `from_status`,

        roir.relation_type as `relation_type`,
        roir.related_title as `relation_to_title`,
        roir.related_url as `relation_url`,
        roir.related_description as `related_description`,

        roir.related_object_identifier as `to_identifier`,
        roir.related_object_identifier_type as `to_identifier_type`,
        roir.related_info_type as `to_related_info_type`,

         rot.registry_object_id as `to_id`,
         rot.key as `to_key`,
         rot.group as `to_group` ,
         rot.title as `to_title`,
         rot.class as `to_class`,
         rot.type as `to_type`,
         rot.slug as `to_slug`,
         rot.data_source_id as `to_data_source_id`,
         rot.status as `to_status`
  from registry_object_identifier_relationships roir
    left join .registry_objects ros on roir.registry_object_id = ros.registry_object_id
    left outer join registry_object_identifiers roidn on roir.related_object_identifier = roidn.identifier and roir.related_object_identifier_type = roidn.identifier_type
    left outer join registry_objects rot on roidn.registry_object_id = rot.registry_object_id;

