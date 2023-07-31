use dbs_dois;

DROP TABLE `doi_client_prefixes`;

CREATE TABLE `doi_client_prefixes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) NOT NULL,
  `prefix_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`),
  KEY `doi_client_prefixes_index` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE `prefixes`;

CREATE TABLE `prefixes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prefix_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datacite_id` VARCHAR(45),
  `created` DATETIME NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  KEY `prefixes_index` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- collect all currently used prefixes
INSERT IGNORE INTO prefixes (`prefix_value`)
SELECT distinct(datacite_prefix) from doi_client;

-- match them with their clients
INSERT IGNORE INTO doi_client_prefixes (`client_id`, `prefix_id`)
select doi_client.client_id , prefixes.id
from doi_client, prefixes
where doi_client.datacite_prefix = prefixes.prefix_value;

-- we shopuld drop column as well (but we can leave it for now)
-- ALTER TABLE `doi_client` DROP COLUMN `datacite_prefix`;


-- select * from prefixes;

-- select * from doi_client_prefixes;

SET SQL_SAFE_UPDATES=0;
-- set all currently used prefixes as non-active
update doi_client_prefixes set active = false;

-- todo fetch a bunch of new prefixes and assign them to clients