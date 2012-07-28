CREATE TABLE "plugin_sms_to_wordpress" (
	"id_user" integer PRIMARY KEY, 
	"wp_username" varchar(50) NOT NULL, 
	"wp_password" varchar(255) NOT NULL,
	"wp_url" varchar(100) NOT NULL
);