CREATE TABLE IF NOT EXISTS "plugin_sms_member" (
	"id_member" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"phone_number" TEXT NOT NULL, 
	"reg_date" DATETIME NOT NULL
);
