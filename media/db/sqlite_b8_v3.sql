-- --------------------------------------------------------

--
-- Table structure for table `b8_wordlist` v3 (for b8 >= v0.6)
--

create table "b8_wordlist" (
  "token" varchar(255) PRIMARY KEY,
  "count_ham" bigint default null,
  "count_spam" bigint default null
);
insert into b8_wordlist (token, count_ham) values ('b8*dbversion', 3);

-- The one below is done in php, during upgrade process
--insert into b8_wordlist (token, count_ham, count_spam) values ('b8*texts', 0, 0);
