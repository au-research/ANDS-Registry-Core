DROP TABLE IF EXISTS `dbs_dois`.`doi_alternate_identifiers`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_contributors`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_creators`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_dates`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_descriptions`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_formats`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_related_identifiers`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_sizes`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_resource_types`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_subjects`;
DROP TABLE IF EXISTS `dbs_dois`.`doi_titles`;
ALTER SCHEMA `dbs_dois`  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_unicode_ci
ALTER TABLE `dbs_dois`.`doi_objects` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;