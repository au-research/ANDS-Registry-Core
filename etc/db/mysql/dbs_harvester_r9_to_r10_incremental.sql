ALTER TABLE `harvest` CHANGE COLUMN `resumption_token` `resumption_token` VARCHAR(1024) NULL DEFAULT NULL  ;

INSERT INTO request VALUES(DEFAULT, 'Identify');
INSERT INTO request VALUES(DEFAULT, 'ListSets');
INSERT INTO request VALUES(DEFAULT, 'ListMetadataFormats');
INSERT INTO request VALUES(DEFAULT, 'ListRecords');
INSERT INTO request VALUES(DEFAULT, 'ListIdentifiers');
INSERT INTO request VALUES(DEFAULT, 'GetRecord');