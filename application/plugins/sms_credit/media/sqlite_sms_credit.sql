CREATE TABLE "plugin_sms_credit" (
	"id_user_credit" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"id_user" INTEGER NOT NULL, 
	"id_template_credit" INTEGER NOT NULL, 
	"valid_start" DATETIME NOT NULL,
	"valid_end" DATETIME NOT NULL
);

CREATE TABLE "plugin_sms_credit_template" (
	"id_credit_template" INTEGER PRIMARY KEY AUTOINCREMENT, 
	"template_name" VARCHAR(50) NOT NULL, 
	"sms_numbers" INTEGER NOT NULL
);