## Kalkun - Open Source Web-based SMS Manager
Kalkun is an open source web-based SMS (Short Message Service) manager. It uses gammu-smsd (part of gammu family) as SMS gateway engine to deliver and retrieve messages from your phone/modem.

Homepage : http://kalkun.sourceforge.net  
Documentation : http://github.com/back2arie/Kalkun/wiki/

## Requirements
You need to install and configure this first:
* apache 2.x.x
* PHP >=5.6, >=7, >=8
* PHP extensions:
  * mysql/pgsql/pdo_sqlite
  * ctype
  * hash
  * intl
  * json
  * mbstring
  * session
* PHP-CLI
* PHP Composer
* MySQL 5.5.3+ or PostgreSQL or SQLite3
* gammu-smsd (make sure it is already running and configured)

## Installation

Kalkun uses the database that is created by gammu with all the tables created by gammy. Kalkun then add tables and some columns. So there is no such case where one has a gammu database separate from the kalkun database. Both software share the same database.

### Debian, ubuntu & related

You are lucky, the package maintainer did most of the configuration & setup job for you.

1. You can download the latest binaries & source packages in deb format on the
github actions page:
    1. go to https://github.com/kalkun-sms/Kalkun/actions?query=workflow%3APackaging++
    1. filter the branch you wish to have the packages for
    1. click on the header of the 1st workflow to get the most recent
    1. download the "Debian packages" artifact
    1. Then install the packages you wish with `apt install ./kalkun_*.deb`
    1. The archive also contains some dependencies that you may need if your distribution doesn't ship them. Command to install kalkun and the dependencies would be:
    ```
    apt install \
      ./php-*.deb \
      kalkun_*.deb
    ```
    1. Optionally, you may install plugins.
1. Finally read /usr/share/doc/kalkun/README.Debian.gz

### Manual Installation

1. Extract to web root folder (eg: /var/www/html => Ubuntu)
1. Run `composer install` from there
1. If you haven't created the gammu database yet, create it. It is shared by gammu & kalkun. Here we name it `kalkun`, but by default, gammu may name it `smsd`.

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
          1. Launch http://localhost/kalkun/index.php/install, and follow instruction there
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
  * After install is finished, you may need to remove install file.
  * To improve security, it's higly recommended to change "encryption_key" in application/config/config.php
    - See https://codeigniter.com/userguide3/libraries/encryption.html#setting-your-encryption-key. On unix/linux you may run
    ```
    php -r 'echo bin2hex(random_bytes(16)), "\n";'
    ```
    Write the value in application/config/config.php and enclose it in a hex2bin() function.

### Migration Note (to kalkun 0.8)
  * During migration to codeigniter 3 (done with version 0.8 of Kalkun), it was strongly advised to switch to the Encryption Library for security reasons. This required to change the default encryption key. The Encryption library was used in "sms to wordpress" and "sms to xmpp" plugins. You need to recreate the configuration of these plugins so that they continue to work.
  The password you may have stored with the older version can't be recovered with the new encryption key.

### Launch Kalkun
Open up your browser and go to http://localhost/kalkun

Default account for the Web Interface (you can change it after you login):  
`username = kalkun`  
`password = kalkun`

Enjoy...:)
