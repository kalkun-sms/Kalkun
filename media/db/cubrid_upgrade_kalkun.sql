CREATE TABLE "user_filters" (
  "id_filter" int(11) NOT NULL,
  "id_user" int(11) NOT NULL,
  "from" varchar(15) NOT NULL,
  "has_the_words" varchar(50) NOT NULL,
  "id_folder" int(11) NOT NULL,
  PRIMARY KEY ("id_filter")
);