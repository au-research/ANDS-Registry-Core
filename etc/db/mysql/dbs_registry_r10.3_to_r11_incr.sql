DROP TABLE vocab_metadata;
DROP TABLE harvest_parameter;


CREATE  TABLE `registry_object_identifier_relationships` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `registry_object_id` MEDIUMINT UNSIGNED NOT NULL ,
  `related_object_key` VARCHAR(255) NULL ,
  `related_object_identifier` VARCHAR(255) NOT NULL ,
  `related_info_type` VARCHAR(45) NULL ,
  `related_object_identifier_type` VARCHAR(45) NOT NULL ,
  `relation_type` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) );

CREATE  TABLE `registry_object_identifiers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `registry_object_id` MEDIUMINT UNSIGNED NOT NULL ,
  `identifier` VARCHAR(255) NOT NULL ,
  `identifier_type`  VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) );

CREATE TABLE `registry_object_tags` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14855 DEFAULT CHARSET=utf8

