-- --------------------------------------------------------

--
-- Table structure for table `plugin_stop_manager`
--

CREATE TABLE "plugin_stop_manager" (
    "id_stop_manager" INTEGER PRIMARY KEY AUTOINCREMENT,
    "destination_number" VARCHAR(20) NOT NULL,
    "stop_type" VARCHAR(50) NOT NULL,
    "stop_message" VARCHAR(2000) NOT NULL,
    "reg_date" DATETIME NOT NULL
);
