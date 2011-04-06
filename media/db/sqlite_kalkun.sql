CREATE TABLE "user" (
	"id_user" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"username" VARCHAR(12) NOT NULL UNIQUE,
	"realname" VARCHAR(100) NOT NULL, 
	"password" VARCHAR(255) NOT NULL, 
	"phone_number" VARCHAR(15) NOT NULL UNIQUE,
     "email_id" VARCHAR(64) NOT NULL, 
	"level" TEXT NOT NULL DEFAULT 'user',
	CHECK ("level" IN ('admin','user'))
);

INSERT INTO "user" VALUES(1, 'kalkun', 'Kalkun SMS', 'f0af18413d1c9e0366d8d1273160f55d5efeddfe', '123456789', 'yourname@domain.com', 'admin');

CREATE TABLE "user_settings" (
	"id_user" INTEGER PRIMARY KEY  NOT NULL, 
	"theme" VARCHAR(10) NOT NULL DEFAULT 'blue',
	"signature" VARCHAR(50) NOT NULL, 
	"permanent_delete" TEXT NOT NULL DEFAULT 'false', 
	"paging" INTEGER NOT NULL DEFAULT 10, 
	"bg_image" VARCHAR(50) NOT NULL,
	"delivery_report" TEXT NOT NULL DEFAULT 'default',
    "email_forward" TEXT NOT NULL DEFAULT 'false',
	"language" VARCHAR(20) NOT NULL DEFAULT 'english',
	"conversation_sort" TEXT NOT NULL DEFAULT 'asc',
	CHECK ("permanent_delete" IN ('true','false')),
	CHECK ("delivery_report" IN ('default','yes','no')),
    CHECK ("email_forward" IN ('true','false')),
	CHECK ("conversation_sort" IN ('asc','desc'))
);

INSERT INTO "user_settings" VALUES (1, 'green', 'false;--\nPut your signature here', 'false', 20, 'true;background.jpg', 'default', 'false', 'english', 'asc');

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


CREATE TABLE "sms_used" (
	"id_sms_used" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"sms_date" DATE NOT NULL, 
	"id_user" INTEGER NOT NULL, 
	"sms_count" INTEGER NOT NULL DEFAULT 0
);


CREATE TABLE "member" (
	"id_member" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"phone_number" TEXT NOT NULL, 
	"reg_date" DATETIME NOT NULL
);


ALTER TABLE "inbox" ADD COLUMN "id_folder" INTEGER NOT NULL DEFAULT 1;
ALTER TABLE "inbox" ADD COLUMN "readed" TEXT NOT NULL DEFAULT 'false';

ALTER TABLE "sentitems" ADD COLUMN "id_folder" INTEGER NOT NULL DEFAULT 3;

ALTER TABLE "pbk" ADD COLUMN "id_user" INTEGER NULL;
ALTER TABLE "pbk_groups" ADD COLUMN "id_user" INTEGER NULL;