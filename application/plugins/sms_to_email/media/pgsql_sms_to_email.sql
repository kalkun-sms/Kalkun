CREATE TABLE "plugin_sms_to_email" (
	"id_user" serial, 
	"email_forward" text NOT NULL DEFAULT 'false', 
	"email_id" varchar(64) NOT NULL,
	UNIQUE("id_user"),
	CHECK ("email_forward" IN ('true','false'))
);