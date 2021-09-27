ALTER TABLE `dbs_dois`.`doi_client`
ADD COLUMN `repository_symbol` varchar(255) CHARACTER SET 'utf8' NULL DEFAULT "." AFTER `datacite_prefix`;

ALTER TABLE `dbs_dois`.`doi_client`
ADD COLUMN  `in_production` TINYINT NULL DEFAULT 0 AFTER  `repository_symbol`;