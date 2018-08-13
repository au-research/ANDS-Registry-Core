ALTER TABLE `dbs_dois`.`doi_client`
ADD COLUMN `test_app_id` VARCHAR(255) CHARACTER SET 'utf8' NULL AFTER `shared_secret`;

ALTER TABLE `dbs_dois`.`doi_client`
ADD COLUMN `test_shared_secret` VARCHAR(64) CHARACTER SET 'utf8' NULL AFTER `test_app_id`;