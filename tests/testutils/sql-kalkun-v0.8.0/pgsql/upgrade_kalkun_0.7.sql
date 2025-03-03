CREATE TABLE "user_filters" (
  "id_filter" serial PRIMARY KEY,
  "id_user" integer NOT NULL,
  "from" varchar(15) NOT NULL,
  "has_the_words" varchar(50) NOT NULL,
  "id_folder" integer NOT NULL
);