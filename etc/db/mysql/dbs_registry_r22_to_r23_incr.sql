CREATE INDEX idx_api_key ON dbs_registry.api_requests (`api_key`);
CREATE INDEX idx_timestamp ON dbs_registry.api_requests (`timestamp`);
CREATE INDEX idx_key ON dbs_registry.configs (`key`);

CREATE INDEX idx_from_id ON dbs_registry.registry_object_implicit_relationships (`from_id`);
CREATE INDEX idx_to_id ON dbs_registry.registry_object_implicit_relationships (`to_id`);
CREATE INDEX idx_relation_type_origin ON dbs_registry.registry_object_implicit_relationships (`relation_type`, `relation_origin`);

CREATE INDEX idx_status ON dbs_registry.harvests (`status`);
CREATE INDEX idx_next_run ON dbs_registry.harvests (`next_run`);