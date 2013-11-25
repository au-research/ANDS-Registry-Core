DROP DATABASE `dbs_dois`;
CREATE DATABASE `dbs_dois`;

CREATE TABLE  `dbs_dois`.`doi_objects` (
    `doi_id` varchar(255) NOT NULL,
    `publisher` varchar(255),
    `publication_year` varchar(255),
    `language` varchar(255),
    `version` varchar(255),
    `updated_when` DATETIME,
    `status` varchar(255),
    `identifier_type` varchar(255),
    `rights` varchar(255),
    `last_metadata_update` date,
    `last_metadata_version` bigint,
    `client_id` bigint,
    `created_who` varchar(255),
    `url` varchar(255),
    `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `datacite_xml` text
);

CREATE TABLE  `dbs_dois`.`doi_client` (
    `client_id`  int(10) unsigned NOT NULL AUTO_INCREMENT,
    `client_name` varchar(255) NOT NULL,
    `client_contact_name` varchar(255),
    `ip_address` varchar(255),
    `app_id` varchar(255),
    `created_when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `client_contact_email` varchar(255),
    `datacite_prefix` varchar(255),
    `shared_secret` varchar(64),
	PRIMARY KEY (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE  `dbs_dois`.`doi_alternate_identifiers` (
    `doi_id` varchar(255),
    `alternate_identifier_type` varchar(255),
    `alternate_identifier` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_contributors` (
    `doi_id` varchar(255),
    `contributor_name` varchar(255),
    `contributor_type` varchar(255),
    `name_identifier` varchar(255),
    `name_identifier_scheme` varchar(255)
);


CREATE TABLE  `dbs_dois`.`doi_creators` (
    `doi_id` varchar(255),
    `creator_name` varchar(255),
    `name_identifier_scheme` varchar(255),
    `name_identifier` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_dates` (
    `doi_id` varchar(255),
    `date` varchar(255),
    `date_type` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_descriptions` (
    `doi_id` varchar(255),
    `description_type` varchar(255),
    `description` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_formats` (
    `doi_id` varchar(255),
    `format` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_related_identifiers` (
    `doi_id` varchar(255),
    `related_identifier` varchar(255),
    `related_identifier_type` varchar(255),
    `relation_type` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_resource_types` (
    `doi_id` varchar(255),
    `resource_type_general` varchar(255),
    `resource` varchar(255),
    `resource_description` varchar(255)
);


CREATE TABLE  `dbs_dois`.`doi_sizes` (
    `doi_id` varchar(255),
    `size` varchar(255)
);

CREATE TABLE  `dbs_dois`.`doi_subjects` (
    `doi_id` varchar(255),
    `subject` varchar(255),
    `subject_scheme` varchar(255)
);


CREATE TABLE  `dbs_dois`.`doi_titles` (
    `doi_id` varchar(255),
    `title_type` varchar(255),
    `title` varchar(255)
);

CREATE TABLE  `dbs_dois`.`activity_log` (
    `activity_id`  int(10) unsigned NOT NULL AUTO_INCREMENT,
    `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `client_id` bigint,
    `activity` varchar(255),
    `doi_id` varchar(255),
    `result` varchar(255),
    `message` varchar(255),
    PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE  `dbs_dois`.`doi_client_domains` (
    `clientdomainId`  int(10) unsigned NOT NULL AUTO_INCREMENT,
    `client_id` bigint,
    `client_domain` varchar(255),
   PRIMARY KEY (`clientdomainId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



