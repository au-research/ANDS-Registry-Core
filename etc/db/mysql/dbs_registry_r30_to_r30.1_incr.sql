use dbs_registry;

-- DROP TABLE `versions`;
-- DROP TABLE `registry_object_versions`;
-- DROP TABLE `schemas`;


CREATE TABLE `schemas` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` mediumint(10) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `exportable` boolean NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`,`prefix`),
  INDEX (`id`,`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `versions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `schema_id` mediumint(8),
  `data` blob NOT NULL,
  `hash` varchar(45) NOT NULL,
  `origin` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT NOW(),
  `updated_at` datetime DEFAULT NOW(),
  PRIMARY KEY (`id`),
  -- foreign key (`schema_id`) REFERENCES `schemas`.`id`,
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `registry_object_versions` (
  `version_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) NOT NULL,
  PRIMARY KEY (`version_id`,`registry_object_id`),
  -- foreign key (`version_id`) REFERENCES `version_id`.`id`,
  -- foreign key (`registry_object_id`) REFERENCES `registry_objects`.`id`,
  INDEX (`version_id`,`registry_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;