CREATE TABLE "plugin_server_alert" (
	"id_server_alert" serial INTEGER PRIMARY KEY, 
	"alert_name" varchar(100) NOT NULL, 
	"ip_address" varchar(20) NOT NULL,
	"port_number" integer NOT NULL,
	"timeout" integer NOT NULL DEFAULT '30',
	"phone_number" varchar(15) NOT NULL,
	"respond_message" varchar(135) NOT NULL,
	"status" text NOT NULL DEFAULT 'true',
	"release_code" varchar(8) NOT NULL,
	CHECK ("status" IN ('true','false'))
);