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