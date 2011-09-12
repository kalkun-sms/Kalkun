-- Kalkun upgrade from 0.3 to 0.4

ALTER TABLE "sms_used" RENAME COLUMN "sms_count" TO "out_sms_count" integer NOT NULL DEFAULT '0';
ALTER TABLE "sms_used" ADD COLUMN "in_sms_count" integer NOT NULL DEFAULT '0';

ALTER TABLE "pbk" ADD COLUMN "is_public" text NOT NULL DEFAULT 'false';
ALTER TABLE "pbk_groups" ADD COLUMN "is_public" text NOT NULL DEFAULT 'false';

CREATE TABLE "b8_wordlist" (
  "token" varchar(255) serial PRIMARY KEY,
  "count" varchar(255) DEFAULT NULL
);

INSERT INTO "b8_wordlist" VALUES('bayes*dbversion', '2');
INSERT INTO "b8_wordlist" VALUES('bayes*texts.ham', '0');
INSERT INTO "b8_wordlist" VALUES('bayes*texts.spam', '0');

CREATE TABLE "plugins" (
  "plugin_id" serial PRIMARY KEY,
  "plugin_system_name" varchar(255) NOT NULL,
  "plugin_name" varchar(255) NOT NULL,
  "plugin_uri" varchar(120) DEFAULT NULL,
  "plugin_version" varchar(30) NOT NULL,
  "plugin_description" text,
  "plugin_author" varchar(120) DEFAULT NULL,
  "plugin_author_uri" varchar(120) DEFAULT NULL,
  "plugin_data" text,
  UNIQUE("plugin_system_name")
);