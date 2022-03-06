-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
-- see: https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
--

CREATE TABLE "ci_sessions" (
    "id" varchar(128) NOT NULL,
    "ip_address" varchar(45) NOT NULL,
    "timestamp" bigint DEFAULT 0 NOT NULL,
    "data" text DEFAULT '' NOT NULL
);

CREATE INDEX "ci_sessions_timestamp" ON "ci_sessions" ("timestamp");

-- When sess_match_ip = FALSE
ALTER TABLE ci_sessions ADD PRIMARY KEY (id);


-- --------------------------------------------------------

--
-- Update password for "kalkun" user to "kalkun" (for version >= 0.8-dev)
-- if the password was still the default
UPDATE public."user" SET password = '$2y$10$sIXe0JiaTIOsC7OOnox5t.deuJwZoawd5QKpQlSNfywziTDHpmmyy'
WHERE password = 'f0af18413d1c9e0366d8d1273160f55d5efeddfe';
