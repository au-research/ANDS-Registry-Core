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
  PRIMARY KEY (`id`);

CREATE TABLE `registry_object_identifiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `identifier_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`);

CREATE TABLE `registry_object_tags` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);