--
-- Database: "kalkun"
--

-- --------------------------------------------------------

--
-- Table structure for table "kalkun"
--

CREATE TABLE "kalkun" (
  "version" varchar(65535) NOT NULL
);

-- --------------------------------------------------------

--
-- Table structure for table "sms_used"
--

CREATE TABLE "sms_used" (
  "id_sms_used" int(11) NOT NULL AUTO_INCREMENT,
  "sms_date" date NOT NULL,
  "id_user" int(11) NOT NULL,
  "out_sms_count" int(11) NOT NULL DEFAULT '0',
  "in_sms_count" int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY ("id_sms_used")
);

-- --------------------------------------------------------

--
-- Table structure for table "user"
--

CREATE TABLE "user" (
  "id_user" int(11) NOT NULL AUTO_INCREMENT(2,1),
  "username" varchar(12) NOT NULL,
  "realname" varchar(100) NOT NULL,
  "password" varchar(255) NOT NULL,
  "phone_number" varchar(15) NOT NULL,
  "level" varchar(255) NOT NULL DEFAULT 'user',
  PRIMARY KEY ("id_user"),
  UNIQUE KEY "username" ("username"),
  UNIQUE KEY "phone_number" ("phone_number")
);

--
-- Dumping data for table "user"
--

INSERT INTO "user" ("id_user", "username", "realname", "password", "phone_number", "level") VALUES
(1, 'kalkun', 'Kalkun SMS', 'f0af18413d1c9e0366d8d1273160f55d5efeddfe', '123456789', 'admin');


-- --------------------------------------------------------

--
-- Table structure for table "user_folders"
--

CREATE TABLE "user_folders" (
  "id_folder" int(11) NOT NULL AUTO_INCREMENT,
  "name" varchar(50) NOT NULL,
  "id_user" int(11) NOT NULL,
  PRIMARY KEY ("id_folder")
);

--
-- Dumping data for table "user_folders"
--

INSERT INTO "user_folders" ("id_folder", "name", "id_user") VALUES
(1, 'inbox', 0),
(2, 'outbox', 0),
(3, 'sent_items', 0),
(4, 'draft', 0),
(5, 'Trash', 0),
(6, 'Spam', 0);

-- --------------------------------------------------------

--
-- Table structure for table "user_inbox"
--

CREATE TABLE "user_inbox" (
  "id_inbox" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  "trash" smallint NOT NULL DEFAULT '0',
  PRIMARY KEY ("id_inbox")
);

-- --------------------------------------------------------

--
-- Table structure for table "user_outbox"
--

CREATE TABLE "user_outbox" (
  "id_outbox" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  PRIMARY KEY ("id_outbox")
);

-- --------------------------------------------------------

--
-- Table structure for table "user_sentitems"
--

CREATE TABLE "user_sentitems" (
  "id_sentitems" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  "trash" smallint NOT NULL DEFAULT '0',
  PRIMARY KEY ("id_sentitems")
);

-- --------------------------------------------------------

--
-- Table structure for table "user_settings"
--

CREATE TABLE "user_settings" (
  "id_user" int(11) NOT NULL,
  "theme" varchar(10) NOT NULL DEFAULT 'blue',
  "signature" varchar(50) NOT NULL,
  "permanent_delete" varchar(255) NOT NULL DEFAULT 'false',
  "paging" int(2) NOT NULL DEFAULT '10',
  "bg_image" varchar(50) NOT NULL,
  "delivery_report" varchar(255) NOT NULL DEFAULT 'default',
  "language" varchar(20) NOT NULL DEFAULT 'english',
  "conversation_sort" varchar(255) NOT NULL DEFAULT 'asc',
  PRIMARY KEY ("id_user")
);

--
-- Dumping data for table "user_settings"
--

INSERT INTO "user_settings" ("id_user", "theme", "signature", "permanent_delete", "paging", "bg_image", "delivery_report", "language", "conversation_sort") VALUES
(1, 'green', 'false;--\nPut your signature here', 'false', 20, 'true;background.jpg', 'default' , 'english', 'asc');


-- --------------------------------------------------------

--
-- Alter table structure for table "inbox"
--

ALTER TABLE "inbox" ADD "id_folder" INT( 11 ) NOT NULL DEFAULT '1',
ADD "readed" varchar( 255 ) NOT NULL DEFAULT 'false';


-- --------------------------------------------------------

--
-- Alter table structure for table "sentitems"
--

ALTER TABLE "sentitems" ADD "id_folder" INT( 11 ) NOT NULL DEFAULT '3';


-- --------------------------------------------------------

--
-- Alter table structure for table "pbk"
--

ALTER TABLE "pbk" ADD "id_user" INT( 11 ) NOT NULL;
ALTER TABLE "pbk" ADD "is_public" varchar(255) NOT NULL DEFAULT 'false';

-- --------------------------------------------------------

--
-- Alter table structure for table "pbk_groups"
--

ALTER TABLE "pbk_groups" ADD "id_user" INT( 11 ) NOT NULL;
ALTER TABLE "pbk_groups" ADD "is_public" varchar(255) NOT NULL DEFAULT 'false';


-- --------------------------------------------------------

--
-- Table structure for table "user_group"
--


CREATE TABLE "user_group" (
  "id_group" int(11) NOT NULL AUTO_INCREMENT,
  "id_pbk" int(11) NOT NULL,
  "id_pbk_groups" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  PRIMARY KEY ("id_group")
);


-- --------------------------------------------------------

--
-- Table structure for table "user_templates"
--

CREATE TABLE "user_templates" (
  "id_template" int(11) NOT NULL AUTO_INCREMENT,
  "id_user" int(11) NOT NULL,
  "Name" varchar(64) NOT NULL,
  "Message" varchar(65535) NOT NULL,
  PRIMARY KEY ("id_template")
);


-- --------------------------------------------------------

--
-- Table structure for table "b8_wordlist"
--

CREATE TABLE "b8_wordlist" (
  "token" varchar(255) NOT NULL,
  "count" varchar(255) default NULL,
  PRIMARY KEY  ("token")
);

INSERT INTO "b8_wordlist" VALUES ('bayes*dbversion', '2');
INSERT INTO "b8_wordlist" VALUES ('bayes*texts.ham', '0');
INSERT INTO "b8_wordlist" VALUES ('bayes*texts.spam', '0');

-- --------------------------------------------------------

--
-- Table structure for table "plugins"
--

CREATE TABLE "plugins" (
  "plugin_id" bigint NOT NULL AUTO_INCREMENT,
  "plugin_system_name" varchar(255) NOT NULL,
  "plugin_name" varchar(255) NOT NULL,
  "plugin_uri" varchar(120) DEFAULT NULL,
  "plugin_version" varchar(30) NOT NULL,
  "plugin_description" varchar(65535),
  "plugin_author" varchar(120) DEFAULT NULL,
  "plugin_author_uri" varchar(120) DEFAULT NULL,
  "plugin_data" string,
  PRIMARY KEY ("plugin_id"),
  UNIQUE KEY "plugin_index" ("plugin_system_name")
);

-- --------------------------------------------------------

--
-- Table structure for table "user_forgot_password"
--

CREATE TABLE "user_forgot_password" (
  "id_user" int(11) NOT NULL,
  "token" varchar(255) NOT NULL,
  "valid_until" datetime NOT NULL,
  PRIMARY KEY ("id_user")
);


CREATE TABLE "user_filters" (
  "id_filter" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  "from" varchar(15) NOT NULL,
  "has_the_words" varchar(50) NOT NULL,
  "id_folder" int(11) NOT NULL,
  PRIMARY KEY ("id_filter")
);