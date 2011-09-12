-- Kalkun upgrade from 0.3 to 0.4

ALTER TABLE "sms_used" RENAME TO "tmp_sms_used";

CREATE TABLE "sms_used" (
	"id_sms_used" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"sms_date" DATE NOT NULL, 
	"id_user" INTEGER NOT NULL, 
	"out_sms_count" INTEGER NOT NULL DEFAULT 0,
	"in_sms_count" INTEGER NOT NULL DEFAULT 0
);

INSERT INTO "sms_used"("id_sms_used", "sms_date", "id_user", "out_sms_count")
SELECT "id_sms_used", "sms_date", "id_user", "sms_count"
FROM "tmp_sms_used";

DROP TABLE "tmp_sms_used";

ALTER TABLE "pbk" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';
ALTER TABLE "pbk_groups" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';

CREATE TABLE "b8_wordlist" (
  "token" VARCHAR(255) PRIMARY KEY AUTOINCREMENT,
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