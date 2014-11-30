CREATE TABLE `registry_object_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registry_object_id` mediumint(8) unsigned NOT NULL,
  `data_source_id` mediumint(8) unsigned NOT NULL,
  `link_type` varchar(64) DEFAULT NULL,
  `link` varchar(512) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `last_checked` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_data_source_id_rol` (`data_source_id`),
  KEY `idx_registry_object_id_rol` (`registry_object_id`),
  KEY `idx_link_rol` (`link`),
  CONSTRAINT `fk_rol_registry_object_id` FOREIGN KEY (`registry_object_id`)
    REFERENCES `registry_objects` (`registry_object_id`)
    ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_rol_data_source_id` FOREIGN KEY (`data_source_id`)
    REFERENCES `data_sources` (`data_source_id`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
