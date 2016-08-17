USE `dbs_registry`;

ALTER TABLE `tasks` CHANGE COLUMN `params` `params` longtext;

ALTER TABLE registry_objects ADD `group` varchar(512) NOT NULL default 'not available';

UPDATE registry_objects ro
  INNER JOIN registry_object_attributes roa ON (roa.registry_object_id = ro.registry_object_id AND roa.`attribute` = 'group')
SET ro.`group` = roa.`value`;

DELETE FROM registry_object_attributes WHERE attribute = 'group';

create view `relationships` as
  select ros.key as `origin_key`,
    ros.group as `origin_group` ,
    ros.title as `origin_title` ,
    ros.class as `origin_class`,
    ros.type as `origin_type`,
    ros.slug as `origin_slug`,
    ros.data_source_id as `origin_data_source_id`,
    ros.status as `origin_status`,
    ror.*,
    rot.registry_object_id as `target_registry_object_id`,
    rot.group as `target_group` ,
    rot.title as `target_title`,
    rot.class as `target_class`,
    rot.type as `target_type`,
    rot.slug as `target_slug`,
    rot.data_source_id as `target_data_source_id`,
    rot.status as `target_status`
  from registry_object_relationships ror
    left join registry_objects ros on ror.registry_object_id = ros.registry_object_id
  left outer join registry_objects rot on ror.related_object_key = rot.`key`;


create view `identifier_relationships` as
  select ros.key as `origin_key`,
         ros.group as `origin_group` ,
         ros.title as `origin_title` ,
         ros.class as `origin_class`,
         ros.type as `origin_type`,
         ros.slug as `origin_slug`,
         ros.data_source_id as `origin_data_source_id`,
         ros.status as `origin_status`,
    roir.*,
         rot.registry_object_id as `target_registry_object_id`,
         rot.key as `related_object_key`,
         rot.group as `target_group` ,
         rot.title as `target_title`,
         rot.class as `target_class`,
         rot.type as `target_type`,
         rot.slug as `target_slug`,
         rot.data_source_id as `target_data_source_id`,
         rot.status as `target_status`
  from registry_object_identifier_relationships roir
    left join .registry_objects ros on roir.registry_object_id = ros.registry_object_id
    left outer join registry_object_identifiers roidn on roir.related_object_identifier = roidn.identifier and roir.related_object_identifier_type = roidn.identifier_type
    left outer join registry_objects rot on roidn.registry_object_id = rot.registry_object_id;

