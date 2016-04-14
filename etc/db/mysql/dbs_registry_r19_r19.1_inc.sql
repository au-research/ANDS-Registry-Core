ALTER TABLE dbs_registry.record_data ADD `status` varchar(56) NOT NULL DEFAULT 'SUPERSEDED';

UPDATE dbs_registry.record_data SET `status` = 'PUBLISHED' WHERE `current` = TRUE;

UPDATE dbs_registry.record_data SET `status` = 'DRAFT' WHERE `scheme` != 'rif';