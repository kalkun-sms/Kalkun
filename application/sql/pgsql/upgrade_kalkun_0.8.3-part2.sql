-- --------------------------------------------------------

--
-- Alter tables to be consistent with mysql & sqlite3 schema
--

BEGIN;
ALTER TABLE "plugins" ALTER COLUMN "name" SET NOT NULL;
ALTER TABLE "plugins" ALTER COLUMN "version" SET NOT NULL;

-- Make user_settings table consistent with definition in mysql & sqlite
ALTER TABLE "user_settings" ALTER COLUMN "id_user" DROP DEFAULT;

COMMIT;
