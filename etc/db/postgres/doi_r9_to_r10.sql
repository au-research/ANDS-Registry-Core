-- JUST TO BE SURE
ALTER TABLE doi_alternate_identifiers DROP CONSTRAINT "doi_id";
ALTER TABLE doi_contributors DROP CONSTRAINT "doi_id";
ALTER TABLE doi_creators DROP CONSTRAINT "doi_id";
ALTER TABLE doi_dates DROP CONSTRAINT "doi_id";
ALTER TABLE doi_descriptions DROP CONSTRAINT "doi_id";
ALTER TABLE doi_formats DROP CONSTRAINT "doi_id";
ALTER TABLE doi_related_identifiers DROP CONSTRAINT "doi_id";
ALTER TABLE doi_resource_types DROP CONSTRAINT "doi_id";
ALTER TABLE doi_sizes DROP CONSTRAINT "doi_id";
ALTER TABLE doi_subjects DROP CONSTRAINT "doi_id";

-- DELETE TEST DOI METADATA (We shouldn't use them by now !!)

delete from doi_alternate_identifiers where doi_id like '10.5072/%';
delete from doi_contributors where doi_id like '10.5072/%';
delete from doi_creators where doi_id like '10.5072/%';
delete from doi_dates where doi_id like '10.5072/%';
delete from doi_descriptions where doi_id like '10.5072/%';
delete from doi_formats where doi_id like '10.5072/%';
delete from doi_related_identifiers where doi_id like '10.5072/%';
delete from doi_resource_types where doi_id like '10.5072/%';
delete from doi_sizes where doi_id like '10.5072/%';
delete from doi_subjects where doi_id like '10.5072/%';