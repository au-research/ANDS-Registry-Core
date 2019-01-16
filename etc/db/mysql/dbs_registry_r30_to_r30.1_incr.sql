USE dbs_registry;


CREATE TABLE `alt_schema_versions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) NOT NULL,
  `registry_object_group` varchar(255) NOT NULL,
  `registry_object_key` varchar(255) NOT NULL,
  `registry_object_data_source_id` mediumint(8) NOT NULL,
  `schema` varchar(45) NOT NULL,
  `data` blob NOT NULL,
  `hash` varchar(45) NOT NULL,
  `created_at` datetime DEFAULT NOW(),
  `updated_at` datetime DEFAULT NOW(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  INDEX (`registry_object_id`, `registry_object_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;