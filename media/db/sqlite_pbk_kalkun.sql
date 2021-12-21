-- --------------------------------------------------------

--
-- Alter table structure for table `pbk`
--

ALTER TABLE "pbk" ADD COLUMN "id_user" INTEGER NULL;
ALTER TABLE "pbk" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';


-- --------------------------------------------------------

--
-- Alter table structure for table `pbk_groups`
--

ALTER TABLE "pbk_groups" ADD COLUMN "id_user" INTEGER NULL;
ALTER TABLE "pbk_groups" ADD COLUMN "is_public" TEXT NOT NULL DEFAULT 'false';
