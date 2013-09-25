ALTER TABLE `dbs_portal`.`search_terms` ADD COLUMN `num_found` INT NOT NULL  AFTER `login_identifier` ;
ALTER TABLE `dbs_portal`.`search_occurence` ADD COLUMN `num_found` INT(11) NOT NULL  AFTER `occurence` , ADD COLUMN `ranking` INT(11) NOT NULL  AFTER `num_found` ;
