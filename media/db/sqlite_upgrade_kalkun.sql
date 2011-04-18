-- Kalkun upgrade from 0.2.10 to 0.3
 

CREATE TABLE "kalkun" (
  "version" TEXT NOT NULL
);

CREATE TABLE "user_templates" (
  "id_template" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_user" INTEGER NOT NULL,
  "Name" VARCHAR(64) NOT NULL,
  "Message" TEXT NOT NULL
);
