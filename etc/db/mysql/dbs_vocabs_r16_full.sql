DROP DATABASE `dbs_vocabs`;

CREATE DATABASE `dbs_vocabs` /*!40100 DEFAULT CHARACTER SET latin1 */;


--
-- Table structure for table `related entities`
--
DROP TABLE IF EXISTS `dbs_vocabs`.`related`;
CREATE TABLE `dbs_vocabs`.`related` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) DEFAULT NULL,
  `relation` varchar(45) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `dbs_vocabs`.`versions`;

CREATE TABLE `dbs_vocabs`.`versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `release_date` timestamp NULL DEFAULT NULL,
  `vocab_id` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `vocabularies `
--

DROP TABLE IF EXISTS `dbs_vocabs`.`vocabularies`;

CREATE TABLE `dbs_vocabs`.`vocabularies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `created_date` timestamp NULL DEFAULT NULL,
  `modified_date` timestamp NULL DEFAULT NULL,
  `modified_who` varchar(45) DEFAULT NULL,
  `licence` text,
  `pool_party_id` varchar(45) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

