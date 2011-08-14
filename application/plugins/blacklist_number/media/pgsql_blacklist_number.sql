CREATE TABLE "plugin_blacklist_number" (
	"id_blacklist_number" serial PRIMARY KEY, 
	"phone_number" varchar(15) NOT NULL, 
	"reason" varchar(255) NOT NULL
);