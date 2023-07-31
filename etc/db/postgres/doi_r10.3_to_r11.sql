-- add the shared_secret column to the doi client table

ALTER TABLE doi_client ADD COLUMN shared_secret character varying(64);