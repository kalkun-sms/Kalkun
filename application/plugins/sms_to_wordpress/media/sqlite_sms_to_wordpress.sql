CREATE TABLE "plugin_sms_to_wordpress" (
	"id_user" INTEGER PRIMARY KEY, 
	"wp_username" VARCHAR(50) NOT NULL, 
	"wp_password" VARCHAR(255) NOT NULL,
	"wp_url" VARCHAR(100) NOT NULL
);