CREATE TABLE "plugin_blacklist_number" (
	"id_blacklist_number" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"phone_number" VARCHAR(15) NOT NULL, 
	"reason" VARCHAR(255) NOT NULL
);