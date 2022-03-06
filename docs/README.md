## Kalkun - Open Source Web-based SMS Manager
Kalkun is an open source web-based SMS (Short Message Service) manager. It uses gammu-smsd (part of gammu family) as SMS gateway engine to deliver and retrieve messages from your phone/modem.

* **Homepage** : http://kalkun.sourceforge.net
* **Documentation** : https://github.com/kalkun-sms/Kalkun/wiki/

### Features
* Support for MySQL, PostgreSQL and SQLite3
* [Multi user](https://github.com/kalkun-sms/Kalkun/wiki/Multi-user) & [Multi modem](https://github.com/kalkun-sms/Kalkun/wiki/Multi-modem) support
* Usual folders: inbox, outbox, sentitems, spam, trash & personal folders.
* [Conversation view](https://github.com/kalkun-sms/Kalkun/wiki/Conversation): SMS are grouped by phone number
* [Spam filter](https://github.com/kalkun-sms/Kalkun/wiki/Spam-filter) based on [b8](https://nasauber.de/opensource/b8/)
* Phonebook, contacts, groups
* Various "compose SMS" options:
    - to a number, contact or a group
    - schedule to specific date/time or with a delay
    - reply, forward, resend
    - [SMS merge](https://github.com/kalkun-sms/Kalkun/wiki/SMS-merge)
    - [SMS template](https://github.com/kalkun-sms/Kalkun/wiki/SMS-template)
* Automatic message signature
* Filter incoming messages
* [Keyboard shortcuts](https://github.com/kalkun-sms/Kalkun/wiki/Keyboard-shortcuts)
* [API access](https://github.com/kalkun-sms/Kalkun/wiki/API)
* Localization
* [Kalkun core parameters](https://github.com/kalkun-sms/Kalkun/wiki/Configuration)
    - Conversation grouping
    - Disable all outgoing SMS
    - [Alternate gateways](https://github.com/kalkun-sms/Kalkun/wiki/Alternate-gateways) (send-out only) if you don't want to use Gammu backend
    - [Append ads](https://github.com/kalkun-sms/Kalkun/wiki/SMS-ads) to your message
    - Send SMS repeatedly with [SMS bomber](https://github.com/kalkun-sms/Kalkun/wiki/SMS-bomber)

### Requirements
For full details, see the [requirements on the wiki](https://github.com/kalkun-sms/Kalkun/wiki/Requirements).

Briefly, this is what has to be installed & configured prior to installing Kalkun:
* HTTP Server (any of Apache httpd, Lighttpd, NGINX, IIS...)
* [Composer](https://getcomposer.org/) (Dependency Manager for PHP)
* [PHP](https://www.php.net) >=5.6, >=7, >=8
* PHP-CLI (command line interface)
* PHP extensions (composer should tell you which ones are missing on your system)
* MySQL/MariadDB 5.5.3+ (having full UTF-8 support) or PostgreSQL or SQLite3
* [Gammu SMSD](https://wammu.eu/smsd/) (make sure it is already running and configured)

### Installation
You can find [detailed installation instructions on the wiki](https://github.com/kalkun-sms/Kalkun/wiki/Installation).

There are also Debian packages produced on every commit. Find them on the [github actions](https://github.com/kalkun-sms/Kalkun/actions?query=workflow%3APackaging++) page. [Detailed installation instructions of the Debian package](https://github.com/kalkun-sms/Kalkun/wiki/Installation#Debian-and-related) are on the wiki.

#### Brief installation steps
Find the [detailed installation steps](https://github.com/kalkun-sms/Kalkun/wiki/Installation) on the wiki.

If you are upgrading, check the [Release notes](https://github.com/kalkun-sms/Kalkun/wiki/Release-notes).

Steps in brief:
1. Extract to web root folder (eg: /var/www/html => Ubuntu)
1. Run `composer install` from there to get & check the dependencies.
1. [Create the gammu smsd database](https://github.com/kalkun-sms/Kalkun/wiki/Setup-Gammu-SMSD-DB).
    - Kalkun uses the database that is created by gammu with all the tables created by gammu. Kalkun then adds tables and some columns. So there is no such case where one has a gammu database separate from the kalkun database. Both software share the same database.
1. Import gammu database schema (it's included on gammu sources, eg. `gammu/docs/sql/mysql.sql`).
1. Configure Kalkun daemon & outbox_queue scripts (to manage inbox and autoreply)
1. Optionally: [increase the security of your installation](https://github.com/kalkun-sms/Kalkun/wiki/Making-Kalkun-more-secure)
1. Configure the connection to the database in `application/config/database.php`
1. Launch the install wizard of Kalkun, preferably by going to http://localhost/kalkun/index.php/install
1. Delete the installation file (the install wizard tell you where it is, if this step is needed)
1. [Change the default encryption key](https://github.com/kalkun-sms/Kalkun/wiki/Installation#change-the-default-encryption-key) in `application/config/config.php`
1. Launch Kalkun by browsing to http://localhost/kalkun. Default login and password are `kalkun`.

### Contributing
Please check the wiki [contribution suggestions](https://github.com/kalkun-sms/Kalkun/wiki/Contributing).

### Documentation
See the [documentation on the Wiki](https://github.com/kalkun-sms/Kalkun/wiki)

### License
Kalkun is licensed under GPL-3-or-later.
