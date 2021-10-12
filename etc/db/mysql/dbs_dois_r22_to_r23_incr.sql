CREATE INDEX idx_client_id  ON dbs_dois.doi_objects (`client_id`);
CREATE INDEX idx_status  ON dbs_dois.doi_objects (`status`);

CREATE INDEX idx_activity ON dbs_dois.activity_log (`activity`);
CREATE INDEX idx_result ON dbs_dois.activity_log (`result`);
CREATE INDEX idx_doi_id ON dbs_dois.activity_log (`doi_id`);
CREATE INDEX idx_timestamp ON dbs_dois.activity_log (`timestamp`);