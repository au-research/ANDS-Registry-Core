DROP VIEW IF EXISTS dbs_registry.identifier_relationships;
DROP VIEW IF EXISTS dbs_registry.identifier_relationships_all_status;
DROP VIEW IF EXISTS dbs_registry.identifier_relationships_normalised;
DROP VIEW IF EXISTS dbs_registry.implicit_relationships;
DROP VIEW IF EXISTS dbs_registry.relationships;
DROP VIEW IF EXISTS dbs_registry.relationships_all_status;
DROP VIEW IF EXISTS dbs_registry.has_related_by_ds;

DROP TABLE IF EXISTS dbs_registry.spatial_extents;
DROP TABLE IF EXISTS dbs_registry.id_cache;
DROP TABLE IF EXISTS dbs_registry.id_cache_2;
DROP TABLE IF EXISTS dbs_registry.registry_object_relationships;
DROP TABLE IF EXISTS dbs_registry.registry_object_identifiers;
DROP TABLE IF EXISTS dbs_registry.registry_object_identifier_relationships;
DROP TABLE IF EXISTS dbs_registry.registry_object_identifier_relationships_normalised;
DROP TABLE IF EXISTS dbs_registry.registry_object_identifiers_normalised;
DROP TABLE IF EXISTS dbs_registry.search_statistics;
DROP TABLE IF EXISTS dbs_registry.vocab_metadata;
DROP TABLE IF EXISTS dbs_registry.institutional_pages;
DROP TABLE IF EXISTS dbs_registry.background_tasks;
DROP TABLE IF EXISTS dbs_registry.record_stats;
DROP TABLE IF EXISTS dbs_registry.registry_object_implicit_relationships;
DROP TABLE IF EXISTS dbs_registry.roles;

alter table dbs_registry.configs convert to character set utf8 collate utf8_unicode_ci;
alter table dbs_registry.harvests convert to character set utf8 collate utf8_unicode_ci;
alter table dbs_registry.registry_object_links convert to character set utf8 collate utf8_unicode_ci;
alter table dbs_registry.tasks convert to character set utf8 collate utf8_unicode_ci;
alter table dbs_registry.theme_pages convert to character set utf8 collate utf8_unicode_ci;