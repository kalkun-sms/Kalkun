 
ALTER TABLE "user_settings" ADD COLUMN "country_code" varchar(2) NOT NULL DEFAULT 'US';

CREATE TABLE "user_forgot_password" (
  "id_user" integer PRIMARY KEY,
  "token" varchar(255) NOT NULL,
  "valid_until" timestamp(0) WITHOUT time zone NOT NULL
);