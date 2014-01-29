CREATE TABLE `logs` (
  `type` varchar(32) DEFAULT NULL,
  `id` varchar(128) DEFAULT NULL,
  `msg` text,
  `date_modified` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$

