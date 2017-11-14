use 'dbs_registry';

CREATE TABLE `orcid_records` (
  `orcid_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_data` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `refresh_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`orcid_id`),
  UNIQUE KEY `id_UNIQUE` (`orcid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `orcid_records` (
  `orcid_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_data` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `refresh_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`orcid_id`),
  UNIQUE KEY `id_UNIQUE` (`orcid_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `dbs_registry`.`registry_object_identifier_relationships`
ADD INDEX `idx_related_object_identifier` (`related_object_identifier` ASC);