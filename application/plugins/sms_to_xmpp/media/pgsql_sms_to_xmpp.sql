CREATE TABLE "plugin_sms_to_xmpp" (
	"id_user" integer PRIMARY KEY, 
	"xmpp_host" varchar(100) NOT NULL, 
	"xmpp_port" varchar(5) NOT NULL,
	"xmpp_username" varchar(50) NOT NULL,
	"xmpp_password" varchar(255) NOT NULL,
	"xmpp_server" varchar(100) NOT NULL
);