DROP TABLE IF EXISTS `resource_owner_hosts`;
CREATE TABLE `resource_owner_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `owner` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `resource_map`;
CREATE TABLE `resource_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iri` text NOT NULL,
  `access_point_id` int(11) NOT NULL,
  `owned` boolean NOT NULL,
  `resource_type` text NOT NULL,
  `deprecated` boolean NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_resource_map_iri` (`iri`(100)),
  KEY `ix_resource_map_access_point_id` (`access_point_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
