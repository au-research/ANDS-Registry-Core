SET GLOBAL sql_mode='NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `dbs_registry`;
GRANT ALL PRIVILEGES ON `dbs_registry`.* TO 'webuser'@'%';

CREATE DATABASE IF NOT EXISTS `dbs_roles`;
GRANT ALL PRIVILEGES ON `dbs_roles`.* TO 'webuser'@'%';

CREATE DATABASE IF NOT EXISTS `dbs_portal`;
GRANT ALL PRIVILEGES ON `dbs_portal`.* TO 'webuser'@'%';

CREATE DATABASE IF NOT EXISTS `dbs_statistics`;
GRANT ALL PRIVILEGES ON `dbs_statistics`.* TO 'webuser'@'%';