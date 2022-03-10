-- --------------------------------------------------------
-- pbk & pbk_groups tables have been removed from gammu-smsd Databse
-- in schema version 16 (corresponding to gammu 1.37.90)
-- This will create them as they used to be created by gammu.

CREATE TABLE pbk (
  "ID" serial PRIMARY KEY,
  "GroupID" integer NOT NULL DEFAULT '-1',
  "Name" text NOT NULL,
  "Number" text NOT NULL
);

CREATE TABLE pbk_groups (
  "Name" text NOT NULL,
  "ID" serial PRIMARY KEY
);
