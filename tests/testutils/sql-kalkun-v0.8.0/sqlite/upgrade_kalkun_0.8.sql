-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
-- --> Useless for SQLite3. We can't configure it this way.
-- see application/config/
--



-- --------------------------------------------------------

--
-- Update password for "kalkun" user to "kalkun" (for version >= 0.8-dev)
-- if the password was still the default
update user
set
  password = '$2y$10$sIXe0JiaTIOsC7OOnox5t.deuJwZoawd5QKpQlSNfywziTDHpmmyy'
where password = 'f0af18413d1c9e0366d8d1273160f55d5efeddfe';
