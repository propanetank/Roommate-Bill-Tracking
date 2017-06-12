# ReadME

### TOC
- Intro
- Requirements
- Setup

### Intro
A relatively simple PHP application for roommates to easily manage shared bills and grocery lists. This project is being actively developed and will be until all features are complete. This is a project that I had a need for and I'm putting it here in case anyone else also has a need.

### Requirements
 - Web server (Apache is known to work, Nginx should also work just fine)
 - PHP 7
 - MySQL or fork of for database (PostgreSQL will not work as of now, SQL statements will need to be rewritten, not a top priority, just get MySQL, it works)

### Installation
As of now there is no installer, but that is a planned feature. To install, simply create your database and then import **setup.sql** in the config folder. Copy **config.sample.php** to **config.php** also in the config folder and then edit the config to your setup.
#### Features not yet implemented
- Google login (only local login is supported right now
- SMTP (you will need to use PHP mail function for the time being)
- Misc MySQL options aren't yet functional, changing those does nothing

Once installed, login with the default username/password of **admin/admin**. To change the username, you'll need to edit the database, changing the username has no harm as all associations are done via user ID. The password can be changed from the profile page. The default account has administrator access which allows creation of users and shared grocery items, the admin can also add existing users to a grocery item when it is first created.
