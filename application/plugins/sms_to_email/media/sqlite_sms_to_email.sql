
CREATE TABLE "plugin_sms_to_email" (
	"id_user" INTEGER UNIQUE, 
	"email_forward" TEXT NOT NULL DEFAULT 'false', 
	"email_id" VARCHAR(64) NOT NULL,
	CHECK ("level" IN ('true','false'))
);