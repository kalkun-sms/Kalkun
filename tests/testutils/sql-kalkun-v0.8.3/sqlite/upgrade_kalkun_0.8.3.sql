-- --------------------------------------------------------

--
-- Upgrade plugins table to conform to the new CI3 plugin system
--

BEGIN TRANSACTION;
ALTER TABLE "plugins" RENAME COLUMN "plugin_system_name" TO "system_name";
ALTER TABLE "plugins" RENAME COLUMN "plugin_name" TO "name";
ALTER TABLE "plugins" RENAME COLUMN "plugin_uri" TO "uri";
ALTER TABLE "plugins" RENAME COLUMN "plugin_version" TO "version";
ALTER TABLE "plugins" RENAME COLUMN "plugin_description" TO "description";
ALTER TABLE "plugins" RENAME COLUMN "plugin_author" TO "author";
ALTER TABLE "plugins" RENAME COLUMN "plugin_author_uri" TO "author_uri";
ALTER TABLE "plugins" RENAME COLUMN "plugin_data" TO "data";
ALTER TABLE "plugins" ADD COLUMN "status" smallint(1) NOT NULL DEFAULT 1;
COMMIT;
