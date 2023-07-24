-- R11 release changes
ALTER TABLE trusted_client ADD COLUMN created_when timestamp with time zone DEFAULT now();

CREATE OR REPLACE VIEW search_view AS 
SELECT encode(handles.handle, 'escape'::text) AS handle, encode(handles.data, 'escape'::text) AS data
FROM handles
WHERE handles.type = 'DESC'::bytea OR handles.type = 'URL'::bytea;

ALTER TABLE search_view
  OWNER TO pidmaster;

CREATE OR REPLACE RULE "_RETURN" AS
ON SELECT TO search_view DO INSTEAD  SELECT encode(handles.handle, 'escape'::text) AS handle, encode(handles.data, 'escape'::text) AS data
FROM handles
WHERE handles.type = 'DESC'::bytea OR handles.type = 'URL'::bytea;