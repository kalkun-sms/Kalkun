-- --------------------------------------------------------

--
-- Table structure for table `plugin_stop_manager`
--

CREATE TABLE "plugin_stop_manager" (
    "id_stop_manager" serial PRIMARY KEY,
    "destination_number" varchar(20) NOT NULL,
    "stop_type" varchar(50) NOT NULL,
    "stop_message" varchar(2000) NOT NULL,
    "reg_date" timestamp(0) WITHOUT time zone NOT NULL
);
