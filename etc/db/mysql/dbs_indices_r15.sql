ALTER TABLE `dbs_portal`.`record_stats` 
ADD INDEX `roid_index` (`ro_id` ASC) ;
ALTER TABLE `dbs_portal`.`contributor_pages` 
ADD INDEX `name_status_index` (`name` ASC, `status` ASC) ;
ALTER TABLE `dbs_registry`.`registry_object_tags` 
ADD INDEX `tagkey_index` (`key` ASC) ;
ALTER TABLE `dbs_roles`.`roles` 
ADD INDEX `roleid_index` (`role_id` ASC) ;
ALTER TABLE `dbs_registry`.`configs` 
ADD INDEX `configkey_index` (`key` ASC) ;
ALTER TABLE `dbs_dois`.`doi_client_domains` 
ADD INDEX `doiclientid_index` (`client_id` ASC) ;
ALTER TABLE `dbs_dois`.`doi_client` 
ADD INDEX `clientid_index` (`client_id` ASC) 
, ADD INDEX `appid_index` (`app_id` ASC) ;
ALTER TABLE `dbs_registry`.`harvests` 
ADD INDEX `statusnextrun_index` (`status` ASC, `next_run` ASC) ;
ALTER TABLE `dbs_vocabs`.`vocab_metadata` 
ADD INDEX `id_index` (`id` ASC) ;
ALTER TABLE `dbs_vocabs`.`vocab_metadata` 
ADD INDEX `recordowner_index` (`record_owner` ASC) ;
ALTER TABLE `dbs_vocabs`.`vocab_metadata` 
ADD INDEX `status_index` (`status` ASC) ;
ALTER TABLE `dbs_roles`.`roles` 
ADD INDEX `roletypeid_index` (`role_type_id` ASC) ;
ALTER TABLE `dbs_registry`.`data_sources` 
ADD UNIQUE INDEX `id_index` (`data_source_id` ASC) ;
ALTER TABLE `dbs_registry`.`configs` 
ADD INDEX `id_index` (`id` ASC) ;