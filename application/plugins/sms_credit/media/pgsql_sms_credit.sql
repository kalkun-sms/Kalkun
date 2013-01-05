CREATE TABLE "plugin_sms_credit" (
	"id_user_credit" serial PRIMARY KEY, 
	"id_user" integer NOT NULL, 
	"id_template_credit" integer NOT NULL, 
	"valid_start" timestamp(0) WITHOUT time zone NOT NULL,
	"valid_end" timestamp(0) WITHOUT time zone NOT NULL
);

CREATE TABLE "plugin_sms_credit_template" (
	"id_credit_template" serial PRIMARY KEY, 
	"template_name" varchar(50) NOT NULL, 
	"sms_numbers" integer NOT NULL
);