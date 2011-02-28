PDO driver for SQLite3 by Xintrea

This driver tested on CodeIgniter 1.7.1

For connect to SQLite3 database, use next steps.

1. Create directory /pdo in /database/drivers and copy to this directory 
   driver *.php files

2. Create SQLite3 database file, and put him to any directory.
   My database file is [APPPATH]/db/base.db

3. In application database config [APPPATH]/config/database.php
   set next settings:

...
$db['default']['hostname'] = '';
$db['default']['username'] = '';
$db['default']['password'] = '';
$db['default']['database'] = 'sqlite:'.APPPATH.'db/base.db';
$db['default']['dbdriver'] = 'pdo';
...


This is all.

My contact: xintrea@gmail.com


Changelog

v.0.01 - First working version

v.0.02 - Fix problem with working in persistent mode connection.
         In practicle, detect CI perticularity to connect to database in persistent mode.
         This connect mode used always, even since if option $db['default']['cache_on']
         set to FALSE. But SQLite architecture can not use client-site paradigm, it is
         based on file. And persistent connections dont work normally.
         For fixing this problem in this SQLite-driver, the persistent connection method
         easy calling normal connection method.
         