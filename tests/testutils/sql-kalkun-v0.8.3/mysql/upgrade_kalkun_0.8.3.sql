-- --------------------------------------------------------

--
-- Upgrade plugins table to conform to the new CI3 plugin system
--

START TRANSACTION;
ALTER TABLE `plugins`
  RENAME COLUMN `plugin_system_name` TO `system_name`,
  RENAME COLUMN `plugin_name` TO `name`,
  RENAME COLUMN `plugin_uri` TO `uri`,
  RENAME COLUMN `plugin_version` TO `version`,
  RENAME COLUMN `plugin_description` TO `description`,
  RENAME COLUMN `plugin_author` TO `author`,
  RENAME COLUMN `plugin_author_uri` TO `author_uri`,
  RENAME COLUMN `plugin_data` TO `data`,
  ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT '1'
  AFTER `name`;
COMMIT;
