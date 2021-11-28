-- --------------------------------------------------------

--
-- Table structure for table "plugin_remote_access"
--
drop table if exists plugin_remote_access;
create table plugin_remote_access (
  id_remote_access integer primary key autoincrement not null,
  access_name varchar(100) not null,
  ip_address varchar(20) not null,
  token varchar(135) not null,
  status varchar(5) not null default 'true',
  constraint plugin_remote_access_status_chk
    check (status in (
      'true', 'false'
    ))
);
