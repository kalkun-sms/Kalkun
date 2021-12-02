-- --------------------------------------------------------
-- pbk & pbk_groups tables have been removed from gammu-smsd Databse
-- in schema version 16 (corresponding to gammu 1.37.90)
-- This will create them as they used to be created by gammu.

CREATE TABLE `pbk` (
  `ID` integer NOT NULL auto_increment,
  `GroupID` integer NOT NULL default '-1',
  `Name` text NOT NULL,
  `Number` text NOT NULL,
  PRIMARY KEY (`ID`)
);

CREATE TABLE `pbk_groups` (
  `Name` text NOT NULL,
  `ID` integer NOT NULL auto_increment,
  PRIMARY KEY `ID` (`ID`)
);
