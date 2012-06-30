CREATE TABLE "plugin_sms_to_xmpp" (
	"id_user" INTEGER PRIMARY KEY, 
	"xmpp_host" VARCHAR(100) NOT NULL, 
	"xmpp_port" VARCHAR(5) NOT NULL,
	"xmpp_username" VARCHAR(50) NOT NULL,
	"xmpp_password" VARCHAR(255) NOT NULL,
	"xmpp_server" VARCHAR(100) NOT NULL
);