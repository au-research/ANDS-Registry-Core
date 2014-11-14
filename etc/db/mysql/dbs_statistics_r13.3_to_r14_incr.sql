
--
-- Table structure for table `google_statistics`
--
CREATE TABLE `google_statistics` (
  `slug` varchar(512) DEFAULT NULL,
  `key` varchar(512) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `data_source` varchar(255) DEFAULT NULL,
  `page_views` int(11) DEFAULT NULL,
  `unique_page_views` int(11) DEFAULT NULL,
  `display_title` varchar(512) DEFAULT NULL,
  `object_class` varchar(45) DEFAULT NULL,
  `day` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;