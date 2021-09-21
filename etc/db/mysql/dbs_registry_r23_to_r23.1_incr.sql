CREATE TABLE `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `scholix` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `scholix_identifier` varchar(255) NOT NULL,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `registry_object_class` varchar(255) NOT NULL,
  `registry_object_group` varchar(255) NOT NULL,
  `registry_object_data_source_id` mediumint(8) NOT NULL,
  `data` BLOB,
  `hash`varchar(45),
  `created_at` DATETIME,
  `updated_at` DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `scholix_identifier_UNIQUE` (`scholix_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `registry_objects` MODIFY `title` VARCHAR(512);
ALTER TABLE `registry_objects` MODIFY `slug` VARCHAR(512);