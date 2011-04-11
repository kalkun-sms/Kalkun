-- Kalkun upgrade from 0.2.9 to 0.2.10
 
ALTER TABLE "user" ADD COLUMN "email_id" VARCHAR(64) NOT NULL;

ALTER TABLE "user_settings" ADD COLUMN "email_forward" TEXT NOT NULL DEFAULT 'false';


CREATE TABLE "user_group" (
  "id_group" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_pbk" INTEGER NOT NULL,
  "id_pbk_groups" INTEGER NOT NULL,
  "id_user" INTEGER NOT NULL,
);

CREATE TABLE "plugin" (
  "id_plugin" INTEGER PRIMARY KEY AUTOINCREMENT,
  "plugin_name" VARCHAR(50) NOT NULL,
  "plugin_status" TEXT NOT NULL DEFAULT 'false',
);
