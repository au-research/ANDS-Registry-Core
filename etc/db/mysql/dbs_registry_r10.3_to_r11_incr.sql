DROP TABLE `vocab_metadata`;
DROP TABLE `harvest_parameter`;

CREATE TABLE `registry_object_identifier_relationships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `related_object_identifier` varchar(255) NOT NULL,
  `related_info_type` varchar(45) DEFAULT NULL,
  `related_object_identifier_type` varchar(45) NOT NULL,
  `relation_type` varchar(45) DEFAULT NULL,
  `related_title` varchar(255) DEFAULT NULL,
  `related_url` varchar(255) DEFAULT NULL,
  `related_description` varchar(512) DEFAULT NULL,
  `connections_preview_div` text,
  PRIMARY KEY (`id`)
);

CREATE TABLE `registry_object_identifiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `registry_object_tags` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);


/* Optionally, some database index improvements */
/*
ALTER TABLE `registry_object_relationships` 
DROP INDEX `idx_related_object_pair` 
, ADD INDEX `idx_related_object_pair` USING HASH (`registry_object_id` ASC, `related_object_key` ASC) ;

ALTER TABLE `registry_objects` 
DROP INDEX `idx_ro_class` 
, ADD INDEX `idx_ro_class` USING HASH (`class` ASC) 
, DROP INDEX `fk_registry_object_data_source` 
, ADD INDEX `fk_registry_object_data_source` USING HASH (`data_source_id` ASC) ;

ALTER TABLE `registry_objects` 
ADD INDEX `slug_lookup` USING HASH (`slug` ASC) ;

ALTER TABLE `url_mappings` 
DROP INDEX `slug_UNIQUE` 
, ADD UNIQUE INDEX `slug_UNIQUE` USING HASH (`slug` ASC) 
, DROP INDEX `idx_url_to_registry_object` 
, ADD INDEX `idx_url_to_registry_object` USING HASH (`registry_object_id` ASC) 
, DROP INDEX `fk_url_map_to_registry_object` 
, ADD INDEX `fk_url_map_to_registry_object` USING HASH (`slug` ASC, `registry_object_id` ASC) ;
*/