CREATE TABLE "plugin_server_alert" (
	"id_server_alert" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"alert_name" VARCHAR(100) NOT NULL, 
	"ip_address" VARCHAR(20) NOT NULL,
	"port_number" INTEGER NOT NULL,
	"timeout" INTEGER NOT NULL DEFAULT '30',
	"phone_number" VARCHAR(15) NOT NULL,
	"respond_message" VARCHAR(135) NOT NULL,
	"status" TEXT NOT NULL DEFAULT 'true',
	"release_code" VARCHAR(8) NOT NULL,
	CHECK ("status" IN ('true','false'))
);