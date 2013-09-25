INSERT INTO dba.tbl_role_types VALUES ('ROLE_DOI_APPID      ','DOI Application Identifier');

-- new registry roles
INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('REGISTRY_USER','ROLE_FUNCTIONAL','Registry Data Source Admin', TRUE);
INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('REGISTRY_STAFF','ROLE_FUNCTIONAL','Registry Staff Member', TRUE);
INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('REGISTRY_SUPERUSER','ROLE_FUNCTIONAL','Registry Superuser', TRUE);
--INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('SPOTLIGHT_CMS_EDITOR','ROLE_FUNCTIONAL','Spotlight CMS Editor', TRUE);

-- senior roles inherit permissions
INSERT INTO dba.tbl_role_relations ("parent_role_id", "child_role_id") VALUES ('REGISTRY_USER', 'REGISTRY_STAFF');
INSERT INTO dba.tbl_role_relations ("parent_role_id", "child_role_id") VALUES ('REGISTRY_STAFF', 'REGISTRY_SUPERUSER');
INSERT INTO dba.tbl_role_relations ("parent_role_id", "child_role_id") VALUES ('SPOTLIGHT_CMS_EDITOR', 'REGISTRY_SUPERUSER');

-- optional, only if COSI previously installed -
INSERT INTO dba.tbl_role_relations ("parent_role_id", "child_role_id") VALUES ('REGISTRY_SUPERUSER', 'COSI_ADMIN');
INSERT INTO dba.tbl_role_relations ("parent_role_id", "child_role_id") VALUES ('REGISTRY_USER', 'ORCA_SOURCE_ADMIN');

-- missing roles
INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('PORTAL_STAFF','ROLE_FUNCTIONAL','Portal/CMS Staff Member', TRUE);
INSERT INTO dba.tbl_roles ("role_id","role_type_id","name","enabled") VALUES ('DOI_USER','ROLE_FUNCTIONAL','DOI Service User', TRUE);