use dbs_registry;

-- DROP TABLE `versions`;
-- DROP TABLE `registry_object_versions`;
-- DROP TABLE `schemas`;
-- DROP VIEW `alt_schema_versions`;


CREATE TABLE `schemas` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `exportable` boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`,`prefix`),
  INDEX (`id`,`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `versions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `schema_id` mediumint(8),
  `data` mediumblob NOT NULL,
  `hash` varchar(45) NOT NULL,
  `origin` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT NOW(),
  `updated_at` datetime DEFAULT NOW(),
  PRIMARY KEY (`id`),
  -- foreign key (`schema_id`) REFERENCES `schemas`.`id`,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `registry_object_versions` (
  `version_id` mediumint(8) NOT NULL,
  `registry_object_id` mediumint(8) NOT NULL,
  PRIMARY KEY (`version_id`,`registry_object_id`),
  -- foreign key (`version_id`) REFERENCES `version_id`.`id`,
  -- foreign key (`registry_object_id`) REFERENCES `registry_objects`.`id`,
  INDEX (`version_id`,`registry_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE VIEW `alt_schema_versions` AS
SELECT `v`.`id`, `ro`.`registry_object_id` ,`ro`.`key` ,`ro`.`group` ,`ro`.`data_source_id` as `registry_object_data_source_id`
,`ro`.`class`, `v`.`data`, `v`.`created_at`, `v`.`updated_at`, `v`.`origin`, `sch`.`prefix`, `sch`.`uri`
from (`versions` `v` left join `registry_object_versions` `rov` on(`v`.`id` = `rov`.`version_id`)
left join `registry_objects` `ro` on(`ro`.`registry_object_id` = `rov`.`registry_object_id`)
left join `schemas` `sch` on(`sch`.`id` = `v`.`schema_id`))
where `ro`.`status` = 'PUBLISHED' and `sch`.`exportable` = TRUE;

ALTER TABLE `dbs_dois`.`prefixes`
ADD COLUMN `is_test` TINYINT NULL DEFAULT 0 AFTER `created`;