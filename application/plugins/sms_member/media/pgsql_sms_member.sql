CREATE TABLE "plugin_sms_member" (
	"id_member" serial PRIMARY KEY, 
	"phone_number" text NOT NULL, 
	"reg_date" timestamp(0) WITHOUT time zone NOT NULL
);