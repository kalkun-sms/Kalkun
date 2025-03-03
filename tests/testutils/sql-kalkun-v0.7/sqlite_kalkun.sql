CREATE TABLE "user" (
	"id_user" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"username" VARCHAR(12) NOT NULL UNIQUE,
	"realname" VARCHAR(100) NOT NULL, 
	"password" VARCHAR(255) NOT NULL, 
	"phone_number" VARCHAR(15) NOT NULL UNIQUE,
	"level" TEXT NOT NULL DEFAULT 'user',
	CHECK ("level" IN ('admin','user'))
);

INSERT INTO "user" VALUES(1, 'kalkun', 'Kalkun SMS', 'f0af18413d1c9e0366d8d1273160f55d5efeddfe', '123456789', 'admin');

CREATE TABLE "user_settings" (
	"id_user" INTEGER PRIMARY KEY  NOT NULL, 
	"theme" VARCHAR(10) NOT NULL DEFAULT 'blue',
	"signature" VARCHAR(50) NOT NULL, 
	"permanent_delete" TEXT NOT NULL DEFAULT 'false', 
	"paging" INTEGER NOT NULL DEFAULT 10, 
	"bg_image" VARCHAR(50) NOT NULL,
	"delivery_report" TEXT NOT NULL DEFAULT 'default',
	"language" VARCHAR(20) NOT NULL DEFAULT 'english',
	"conversation_sort" TEXT NOT NULL DEFAULT 'asc',
	"country_code" VARCHAR(2) NOT NULL DEFAULT 'US',
	CHECK ("permanent_delete" IN ('true','false')),
	CHECK ("delivery_report" IN ('default','yes','no')),
	CHECK ("conversation_sort" IN ('asc','desc'))
);

INSERT INTO "user_settings" VALUES (1, 'green', 'false;--\nPut your signature here', 'false', 20, 'true;background.jpg', 'default', 'english', 'asc');

CREATE TABLE "user_outbox" (
	"id_outbox" INTEGER PRIMARY KEY  NOT NULL, 
	"id_user" INTEGER NOT NULL 
);

CREATE TABLE "user_inbox" (
	"id_inbox" INTEGER PRIMARY KEY  NOT NULL,
	"id_user" INTEGER NOT NULL,
	"trash" BOOL NOT NULL  DEFAULT 0 
);

CREATE TABLE "user_sentitems" (
	"id_sentitems" INTEGER PRIMARY KEY  NOT NULL,
	"id_user" INTEGER NOT NULL,
	"trash" BOOL NOT NULL  DEFAULT 0 
);

CREATE TABLE "user_folders" (
	"id_folder" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"name" VARCHAR(50) NOT NULL, 
	"id_user" INTEGER NOT NULL 
);

INSERT INTO "user_folders" VALUES(1, 'inbox', 0);
INSERT INTO "user_folders" VALUES(2, 'outbox', 0);
INSERT INTO "user_folders" VALUES(3, 'sent_items', 0);
INSERT INTO "user_folders" VALUES(4, 'draft', 0);
INSERT INTO "user_folders" VALUES(5, 'Trash', 0);
INSERT INTO "user_folders" VALUES(6, 'Spam', 0);
INSERT INTO "user_folders" VALUES(10, 'Reserved', 0);

CREATE TABLE "sms_used" (
	"id_sms_used" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"sms_date" DATE NOT NULL, 
	"id_user" INTEGER NOT NULL, 
	"out_sms_count" INTEGER NOT NULL DEFAULT 0,
	"in_sms_count" INTEGER NOT NULL DEFAULT 0
);

ALTER TABLE "inbox" ADD COLUMN "id_folder" INTEGER NOT NULL DEFAULT 1;
ALTER TABLE "inbox" ADD COLUMN "readed" TEXT NOT NULL DEFAULT 'false';

ALTER TABLE "sentitems" ADD COLUMN "id_folder" INTEGER NOT NULL DEFAULT 3;

ALTER TABLE "pbk" ADD COLUMN "id_user" INTEGER NULL;
ALTER TABLE "pbk" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';

ALTER TABLE "pbk_groups" ADD COLUMN "id_user" INTEGER NULL;
ALTER TABLE "pbk_groups" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';

CREATE TABLE "user_group" (
  "id_group" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_pbk" INTEGER NOT NULL,
  "id_pbk_groups" INTEGER NOT NULL,
  "id_user" INTEGER NOT NULL
);

CREATE TABLE "kalkun" (
  "version" TEXT NOT NULL
);

CREATE TABLE "user_templates" (
  "id_template" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_user" INTEGER NOT NULL,
  "Name" VARCHAR(64) NOT NULL,
  "Message" TEXT NOT NULL
);

CREATE TABLE "b8_wordlist" (
  "token" VARCHAR(255) PRIMARY KEY,
  "count" VARCHAR(255) DEFAULT NULL
);

INSERT INTO "b8_wordlist" VALUES('bayes*dbversion', '2');
INSERT INTO "b8_wordlist" VALUES('bayes*texts.ham', '0');
INSERT INTO "b8_wordlist" VALUES('bayes*texts.spam', '0');

CREATE TABLE "plugins" (
  "plugin_id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "plugin_system_name" VARCHAR(255) NOT NULL UNIQUE,
  "plugin_name" VARCHAR(255) NOT NULL,
  "plugin_uri" VARCHAR(120) DEFAULT NULL,
  "plugin_version" VARCHAR(30) NOT NULL,
  "plugin_description" TEXT,
  "plugin_author" VARCHAR(120) DEFAULT NULL,
  "plugin_author_uri" VARCHAR(120) DEFAULT NULL,
  "plugin_data" TEXT
);

CREATE TABLE "user_forgot_password" (
  "id_user" INTEGER PRIMARY KEY,
  "token" VARCHAR(255) NOT NULL,
  "valid_until" NUMERIC NOT NULL
);

CREATE TABLE "user_filters" (
  "id_filter" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_user" INTEGER NOT NULL,
  "from" VARCHAR(15) NOT NULL,
  "has_the_words" VARCHAR(50) NOT NULL,
  "id_folder" INTEGER NOT NULL
);