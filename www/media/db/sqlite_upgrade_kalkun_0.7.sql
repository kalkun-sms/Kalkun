CREATE TABLE "user_filters" (
  "id_filter" INTEGER PRIMARY KEY AUTOINCREMENT,
  "id_user" INTEGER NOT NULL,
  "from" VARCHAR(15) NOT NULL,
  "has_the_words" VARCHAR(50) NOT NULL,
  "id_folder" INTEGER NOT NULL
);