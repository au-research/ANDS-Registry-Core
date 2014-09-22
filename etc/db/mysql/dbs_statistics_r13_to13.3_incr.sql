
--
-- Table structure for table `related_publications`
--
CREATE TABLE `dbs_statistics`.`related_publications` (
  `timestamp` INT NOT NULL,
  `data_source_id` INT NULL,
  `registry_object_id` INT NULL,
  `notes` TEXT NULL,
  `title` TEXT NULL,
  `identifier` VARCHAR(45) NULL);


--
-- Table structure for table `object_counts`
--
CREATE TABLE `dbs_statistics`.`object_counts` (
  `timestamp` INT NOT NULL,
  `data_source_id` INT NULL,
  `total` INT NULL,
  `collection` INT NULL,
  `party` INT NULL,
  `activity` INT NULL,
  `service` INT NULL);