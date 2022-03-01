
CREATE SEQUENCE "user_id_user_seq" START 2;
CREATE SEQUENCE "user_settings_id_user_seq" START 2;

CREATE TABLE "user" (
	"id_user" integer PRIMARY KEY DEFAULT nextval('user_id_user_seq'), 
	"username" varchar(12) NOT NULL,
	"realname" varchar(100) NOT NULL, 
	"password" varchar(255) NOT NULL, 
	"phone_number" varchar(15) NOT NULL, 
	"level" text NOT NULL DEFAULT 'user',
	UNIQUE("phone_number", "username"),
	CHECK ("level" IN ('admin','user'))
);

CREATE TABLE "user_settings" (
	"id_user" integer PRIMARY KEY DEFAULT nextval('user_settings_id_user_seq'), 
	"theme" varchar(10) NOT NULL DEFAULT 'blue',
	"signature" varchar(50) NOT NULL, 
	"permanent_delete" text NOT NULL DEFAULT 'false', 
	"paging" integer NOT NULL DEFAULT 10, 
	"bg_image" varchar(50) NOT NULL,
	"delivery_report" text NOT NULL DEFAULT 'default',
	"language" varchar(20) NOT NULL DEFAULT 'english',
	"conversation_sort" text NOT NULL DEFAULT 'asc',
	"country_code" varchar(2) NOT NULL DEFAULT 'US',
	CHECK ("permanent_delete" IN ('true','false')),
	CHECK ("delivery_report" IN ('default','yes','no')),
	CHECK ("conversation_sort" IN ('asc','desc'))
);

INSERT INTO "user" VALUES(1, 'kalkun', 'Kalkun SMS', '$2y$10$sIXe0JiaTIOsC7OOnox5t.deuJwZoawd5QKpQlSNfywziTDHpmmyy', '123456789', 'admin');
INSERT INTO "user_settings" VALUES (1, 'green', 'false;Put your signature here', 'false', 20, 'true;background.jpg', 'default', 'english', 'asc');


CREATE TABLE "user_outbox" (
	"id_outbox" integer PRIMARY KEY, 
	"id_user" integer NOT NULL 
);

CREATE TABLE "user_inbox" (
	"id_inbox" integer PRIMARY KEY,
	"id_user" integer NOT NULL,
	"trash" smallint NOT NULL DEFAULT 0 
);

CREATE TABLE "user_sentitems" (
	"id_sentitems" integer PRIMARY KEY,
	"id_user" integer NOT NULL,
	"trash" smallint NOT NULL DEFAULT 0 
);

CREATE SEQUENCE "user_folders_id_folder_seq" START 11;

CREATE TABLE "user_folders" (
	"id_folder" integer PRIMARY KEY DEFAULT nextval('user_folders_id_folder_seq'), 
	"name" varchar(50) NOT NULL, 
	"id_user" integer NOT NULL
);

INSERT INTO "user_folders" VALUES(1, 'inbox', 0), (2, 'outbox', 0), (3, 'sent_items', 0), (4, 'draft', 0), (5, 'Trash', 0), (6, 'Spam', 0);

CREATE TABLE "sms_used" (
	"id_sms_used" serial PRIMARY KEY, 
	"sms_date" date NOT NULL, 
	"id_user" integer NOT NULL, 
	"out_sms_count" integer NOT NULL DEFAULT 0,
	"in_sms_count" integer NOT NULL DEFAULT 0
);

ALTER TABLE "inbox" ADD COLUMN "id_folder" integer NOT NULL DEFAULT 1;
ALTER TABLE "inbox" ADD COLUMN "readed" text NOT NULL DEFAULT 'false';

ALTER TABLE "sentitems" ADD COLUMN "id_folder" integer NOT NULL DEFAULT 3;

CREATE TABLE "user_group" (
  "id_group" serial PRIMARY KEY,
  "id_pbk" integer NOT NULL,
  "id_pbk_groups" integer NOT NULL,
  "id_user" integer NOT NULL
);

CREATE TABLE "kalkun" (
  "version" text NOT NULL
);

CREATE TABLE "user_templates" (
  "id_template" serial PRIMARY KEY,
  "id_user" integer NOT NULL,
  "Name" varchar(64) NOT NULL,
  "Message" text NOT NULL
);


-- --------------------------------------------------------

--
-- Table structure for table `b8_wordlist` v3 (for b8 >= v0.6)
--

create table "b8_wordlist" (
  "token" varchar(255) primary key,
  "count_ham" bigint default null,
  "count_spam" bigint default null
);
insert into "b8_wordlist" ("token", "count_ham") values ('b8*dbversion', 3);
insert into "b8_wordlist" ("token", "count_ham", "count_spam") values ('b8*texts', 0, 0);


CREATE TABLE "plugins" (
  "plugin_id" serial PRIMARY KEY,
  "plugin_system_name" varchar(255) NOT NULL,
  "plugin_name" varchar(255) DEFAULT NULL,
  "plugin_uri" varchar(120) DEFAULT NULL,
  "plugin_version" varchar(30) DEFAULT NULL,
  "plugin_description" text,
  "plugin_author" varchar(120) DEFAULT NULL,
  "plugin_author_uri" varchar(120) DEFAULT NULL,
  "plugin_data" text,
  UNIQUE("plugin_system_name")
);

CREATE TABLE "user_forgot_password" (
  "id_user" integer PRIMARY KEY,
  "token" varchar(255) NOT NULL,
  "valid_until" timestamp(0) WITHOUT time zone NOT NULL
);

CREATE TABLE "user_filters" (
  "id_filter" serial PRIMARY KEY,
  "id_user" integer NOT NULL,
  "from" varchar(15) NOT NULL,
  "has_the_words" varchar(50) NOT NULL,
  "id_folder" integer NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
-- see: https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
--

CREATE TABLE "ci_sessions" (
    "id" varchar(128) NOT NULL,
    "ip_address" varchar(45) NOT NULL,
    "timestamp" bigint DEFAULT 0 NOT NULL,
    "data" text DEFAULT '' NOT NULL
);

CREATE INDEX "ci_sessions_timestamp" ON "ci_sessions" ("timestamp");

-- When sess_match_ip = FALSE
ALTER TABLE ci_sessions ADD PRIMARY KEY (id);
