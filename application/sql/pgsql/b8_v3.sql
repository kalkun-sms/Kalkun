--   Copyright (C) 2010-2019 Tobias Leupold <tobias.leupold@gmx.de>
-- Adapted for Kalkun
--
--   This file is part of the b8 package
--
--   This program is free software; you can redistribute it and/or modify it
--   under the terms of the GNU Lesser General Public License as published by
--   the Free Software Foundation in version 2.1 of the License.
--
--   This program is distributed in the hope that it will be useful, but
--   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
--   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
--   License for more details.
--
--   You should have received a copy of the GNU Lesser General Public License
--   along with this program; if not, write to the Free Software Foundation,
--   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

-- --------------------------------------------------------

--
-- Table structure for table `b8_wordlist` v3 (for b8 >= v0.6)
--

create table "b8_wordlist" (
  "token" varchar(255) primary key,
  "count_ham" bigint default null,
  "count_spam" bigint default null
);
insert into "b8_wordlist" ("token", "count_ham") values ('b8*dbversion', 3);

-- The one below is done in php, during upgrade process
--insert into "b8_wordlist" ("token", "count_ham", "count_spam") values ('b8*texts', 0, 0);
