CREATE TABLE `dbs_dois`.`bulk_requests` (
  `id` INT NOT NULL,
  `client_id` INT(10) NOT NULL,
  `bulk_id` BIGINT(20) NOT NULL,
  `status` VARCHAR(45) NULL,
  `params` TEXT NULL,
  `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `CLIENT_ID_INDEX` (`client_id` ASC),
  INDEX `BULK_ID_INDEX` (`bulk_id` ASC));
ALTER TABLE `dbs_dois`.`bulk_requests`
DROP COLUMN `bulk_id`,
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ,
DROP INDEX `BULK_ID_INDEX` ;

CREATE TABLE `dbs_dois`.`bulk` (
  `id` BIGINT NOT NULL,
  `doi` VARCHAR(256) NOT NULL,
  `target` VARCHAR(256) NULL,
  `from` VARCHAR(256) NULL,
  `to` VARCHAR(256) NULL,
  `bulk_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `BULK_ID` (`bulk_id` ASC));
ALTER TABLE `dbs_dois`.`bulk`
ADD COLUMN `status` VARCHAR(45) NULL DEFAULT 'PENDING' AFTER `bulk_id`,
ADD COLUMN `message` TEXT NULL AFTER `status`;

ALTER TABLE `dbs_dois`.`bulk`
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ;
