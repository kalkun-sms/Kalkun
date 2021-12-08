## Kalkun - Open Source Web-based SMS Manager
Kalkun is an open source web-based SMS (Short Message Service) manager. It uses gammu-smsd (part of gammu family) as SMS gateway engine to deliver and retrieve messages from your phone/modem.

Homepage : http://kalkun.sourceforge.net  
Documentation : http://github.com/back2arie/Kalkun/wiki/

## Requirements
You need to install and configure this first:
* apache 2.x.x
* PHP 7.x.x (CodeIgniter3 requires 5.6 (or at very least 5.3.6) but we suggest >=7. If you use php>=5.6 and it works, please report back so that we update the minimal requirements)
* PHP extensions:
  * mysql/pgsql/pdo_sqlite
  * session
  * hash
  * json
  * mbstring
  * APC or APCu
* PHP-CLI
* PHP Composer
* MySQL 5.5.3+ or PostgreSQL or SQLite3
* gammu-smsd (make sure it is already running and configured)

## Installation

1. Extract to web root folder (eg: /var/www/html => Ubuntu)
1. Run `composer install` 
1. Create database named kalkun

   For MySQL (you may do it with mysql console or phpMyAdmin)
     ```
     # mysql > CREATE DATABASE kalkun;
     # mysql > quit
     ```
   For PostgreSQL
    ```
    CREATE USER username WITH password 'password' NOCREATEDB NOCREATEROLE;
    CREATE DATABASE kalkun WITH OWNER = username;
    ```
1. Edit database config (application/config/database.php)  
   Change database value to 'kalkun'.  
   username and password depends on your database configuration.  
   If you use a specific port with PostgreSQL, you may also need to set
   `$db['default']['port'] = "5432";`

1. Import gammu database schema (it's included on gammu source, eg. `gammu/docs/sql/mysql.sql`).

    For MySQL : 
    ```
    mysql kalkun - u username -p < gammu/docs/sql/mysql.sql
    ```
    For PostgreSQL : 
    ```
    psql -U username -h localhost kalkun < gammu/docs/sql/pgsql.sql
    ```
    For PostgreSQL & debian:
    ```
    gunzip -c /usr/share/doc/gammu-smsd/examples/pgsql.sql.gz | psql -U username -h localhost kalkun
    ```
1. Configure daemon (to manage inbox and autoreply)
   -  Set path on gammu-smsd configuration at runonreceive directive, e.g:
      ```
      [smsd]
      runonreceive = /var/www/html/kalkun/scripts/daemon.sh
      ```
      or, if you use Windows:
      ```
      [smsd]
      runonreceive = C:\xampp\htdocs\kalkun\scripts\daemon.bat
      ```
   - set correct path (php-cli path and daemon.php path) on daemon.sh or daemon.bat
   - set correct path (php-cli path and outbox_queue.php path) on outbox_queue.sh or outbox_queue.bat
   - make sure that the daemon & outbox_queue scripts are executable
   - Change URI path in daemon.php & outbox_queue.php. Default is (http://localhost/kalkun)
1. Configure Kalkun
    - _There are 2 ways to configure_
        - Graphic Install (this will also check that all the dependencies are installed)  
          1. Launch http://your-location/kalkun/index.php/install, and follow instruction there
          1. Finally delete file /var/www/html/kalkun/*install* in case the installer didn't do so.
        - Manual Install 
          1. Import sql file located in kalkun/media/db/ to kalkun database.
        
             For MySQL
             ```
             mysql -u username -p kalkun < /var/www/html/kalkun/media/db/mysql_kalkun.sql
             ```
             For PostgreSQL
             ```
             psql -U username -h localhost kalkun < /var/www/html/kalkun/media/db/pgsql_kalkun.sql
             ```
           2. Delete the file /var/www/html/kalkun/*install* 
           
              `rm /var/www/html/kalkun/install`
## IMPORTANT
  * After install is finished, you need to remove install folder.
  * To improve security, it's higly recommended to change "encryption_key" in application/config/config.php
    - See https://codeigniter.com/userguide3/libraries/encryption.html#setting-your-encryption-key. On unix/linux you may run
    ```
    php -r 'echo bin2hex(random_bytes(16)), "\n";'
    ```
    Write the value in application/config/config.php and enclose it in a hex2bin() function.

### Migration Note (to kalkun 0.8)
  * During migration to codeigniter 3 (done with version 0.8 of Kalkun), it was strongly advised to switch to the Encryption Library for security reasons. This required to change the default encryption key. The Encryption library was used in "sms to wordpress" and "sms to xmpp" plugins. You need to recreate the configuration of these plugins so that they continue to work.

### Launch Kalkun
Open up your browser and go to http://your-location/kalkun

Default account for the Web Interface (you can change it after you login):  
`username = kalkun`  
`password = kalkun`

Enjoy...:)
