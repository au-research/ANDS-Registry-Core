-- type attribute didn't get update or created for new records since it was moved to core attributes
-- so before the r19.1 bugfix run this query to update the missing values

UPDATE dbs_registry.registry_objects ro
  INNER JOIN dbs_registry.registry_object_attributes roa ON (ro.`type` is null and roa.registry_object_id = ro.registry_object_id AND roa.`attribute` = 'type')
SET ro.`type` = roa.`value`;