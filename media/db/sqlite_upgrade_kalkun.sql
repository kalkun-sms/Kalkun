
ALTER TABLE "user_settings" ADD COLUMN "country_code" VARCHAR(2) NOT NULL DEFAULT 'US';

CREATE TABLE "user_forgot_password" (
  "id_user" INTEGER PRIMARY KEY,
  "token" VARCHAR(255) NOT NULL,
  "valid_until" NUMERIC NOT NULL
);