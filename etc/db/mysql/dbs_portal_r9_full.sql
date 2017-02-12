
CREATE  TABLE `page_views` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `request_uri` VARCHAR(512) NOT NULL ,
  `timestamp` INT NOT NULL ,
  `ip_address` VARCHAR(45) NULL ,
  `user_agent` VARCHAR(512) NULL ,
  `login_identifier` VARCHAR(256) NULL ,
  `referer` VARCHAR(512) NULL ,
  `note` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) ) ENGINE=MyISAM;



CREATE  TABLE `click_stats` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `source_url` VARCHAR(512) NOT NULL ,
  `target_url` VARCHAR(512) NOT NULL ,
  `timestamp` INT NOT NULL ,
  `ip_address` VARCHAR(45) NULL ,
  `user_agent` VARCHAR(512) NULL ,
  `login_identifier` VARCHAR(256) NULL ,
  `note` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) ) ENGINE=MyISAM;



CREATE  TABLE `search_terms` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `term` VARCHAR(256) NOT NULL ,
  `timestamp` INT NOT NULL ,
  `ip_address` VARCHAR(45) NULL ,
  `user_agent` VARCHAR(256) NULL ,
  `login_identifier` VARCHAR(256) NULL ,
  PRIMARY KEY (`id`) ) ENGINE=MyISAM;



CREATE  TABLE `search_occurence` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `term` VARCHAR(256) NOT NULL ,
  `occurence` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `term_UNIQUE` (`term` ASC) ) ENGINE=MyISAM;
ALTER TABLE `search_occurence` 
ADD INDEX `term_INDEX` USING HASH (`term` ASC) ;
