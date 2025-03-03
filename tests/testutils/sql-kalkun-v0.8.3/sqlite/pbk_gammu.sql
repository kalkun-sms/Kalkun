-- --------------------------------------------------------
-- pbk & pbk_groups tables have been removed from gammu-smsd Databse
-- in schema version 16 (corresponding to gammu 1.37.90)
-- This will create them as they used to be created by gammu.

CREATE TABLE pbk (
  ID INTEGER PRIMARY KEY AUTOINCREMENT,
  GroupID INTEGER NOT NULL DEFAULT '-1',
  Name TEXT NOT NULL,
  Number TEXT NOT NULL
);

CREATE TABLE pbk_groups (
  Name TEXT NOT NULL,
  ID INTEGER PRIMARY KEY AUTOINCREMENT
);
