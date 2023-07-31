
ALTER TABLE `registry_object_relationships` ADD COLUMN `relation_type` VARCHAR(512) NULL  AFTER `origin` , ADD COLUMN `relation_description` VARCHAR(512) NULL  AFTER `relation_type` ;
ALTER TABLE `registry_objects` DROP INDEX `key_UNIQUE` ;

ALTER TABLE `url_mappings` ADD COLUMN 
  `search_title` varchar(255) DEFAULT NULL AFTER `registry_object_id`;


CREATE TABLE `api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(32) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `api_key` varchar(45) DEFAULT NULL,
  `service` varchar(45) DEFAULT NULL,
  `params` varchar(255) DEFAULT NULL,
  `timestamp` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8;

ALTER TABLE `api_keys` DROP COLUMN `owner_token`;
ALTER TABLE `api_keys` ADD COLUMN `created` bigint(20) DEFAULT NULL;
INSERT INTO `api_keys` VALUES ('api','services@ands.org.au','ANDS -Internal API requests','ANDS -Internal API requests',0);

CREATE TABLE `deleted_registry_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `deleted` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `record_data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_data_source_id_idx` (`data_source_id`),
  CONSTRAINT `fk_data_source_id` FOREIGN KEY (`data_source_id`) REFERENCES `data_sources` (`data_source_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

ALTER TABLE `url_mappings` DROP FOREIGN KEY `fk_url_map_to_registry_object` ;
ALTER TABLE `url_mappings` CHANGE COLUMN `registry_object_id` `registry_object_id` MEDIUMINT(8) UNSIGNED NULL  ;

ALTER TABLE `data_source_logs` CHANGE COLUMN `class` `class` varchar(45) NULL;
ALTER TABLE `data_source_logs` ADD COLUMN `harvester_error_type` varchar(45) NULL;


ALTER TABLE `registry_objects` 
ADD INDEX `key_index` USING HASH (`key` ASC) 
, ADD INDEX `key_class_index` USING HASH (`key` ASC, `class` ASC) ;
ALTER TABLE `url_mappings` 
ADD INDEX `slug_INDEX` USING HASH (`slug` ASC) ;
ALTER TABLE `registry_object_metadata` 
DROP INDEX `idx_reg_metadata` 
, ADD INDEX `idx_reg_metadata` USING HASH (`registry_object_id` ASC, `attribute` ASC) 
, DROP INDEX `fk_metadata_to_registry_object` ;
ALTER TABLE `registry_object_relationships` 
DROP INDEX `fk_registry_object_relationships` 
, ADD INDEX `idx_related_object_id` USING HASH (`registry_object_id` ASC) 
, ADD INDEX `idx_related_object_key` USING HASH (`related_object_key` ASC) ;
ALTER TABLE `record_data` ADD COLUMN 
  `hash` varchar(45) NOT NULL DEFAULT 'unhashed' AFTER `scheme`;

ALTER TABLE `data_source_attributes` CHANGE COLUMN `value` `value` VARCHAR(512) NULL DEFAULT NULL  ;
ALTER TABLE `registry_object_attributes` CHANGE COLUMN `value` `value` VARCHAR(512) NULL DEFAULT NULL  ;

ALTER TABLE `registry_object_attributes` 
  ADD INDEX `idx_reg_attr_val` USING HASH (`attribute` ASC, `value`(255) ASC) ;


