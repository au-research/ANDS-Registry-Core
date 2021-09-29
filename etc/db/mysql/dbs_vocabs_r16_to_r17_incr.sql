
DROP TABLE IF EXISTS `access_points`;
CREATE TABLE `access_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version_id` int(11) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `portal_data` text,
  `toolkit_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

