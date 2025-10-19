<p align="center"><h1 align="center">Interface for decision support system — HR Robot</h1><br /></p>

**HR Robot - Interface (HRR-Interface)** is a web-based application (a client part) for decision support system, namely, **HR Robot** (emotion detection, analysis and interpretation).

HRR-Interface is based on [PHP 7](https://www.php.net/releases/7.0/ru.php) and [Yii 2 Framework](http://www.yiiframework.com/).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![build](https://github.com/yiisoft/yii2-app-basic/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-app-basic/actions?query=workflow%3Abuild)

### Version

1.0

DIRECTORY STRUCTURE
-------------------

      commands/           contains console commands (controllers) for user creation by default and video interview analysis
      components/         contains OS connection class and different detectors (phrase, text frequency, trend, facial feature, and others)
      config/             contains main configurations for this application and database
      migrations/         contains migrations for creation of all tables of a new database
      modules/            contains one main module including:
          controllers/    contains controllers (basic actions)
          models/         contains data models and forms
          views/          contains basic layout and differnt views (web pages)
      web/                contains js-scripts, css-scripts and other web resources


REQUIREMENTS
------------

The minimum requirement by this project that your Web server supports PHP 7.0, PostgreSQL 9.0 or MySQL 8.0.

We recommend you to use a combination of [PHP 7.0](https://www.php.net/downloads) with DBMS [PostgreSQL 9](https://www.postgresql.org/download/) or more.

INSTALLATION
------------

### Install via Git
If you do not have [Git](https://git-scm.com/), you can install [it](https://git-scm.com/downloads) depending on your OS.

You can clone this project into your directory (recommended installation):

~~~
git clone https://github.com/LedZeppe1in/hr_robot_interface.git
~~~

### Update dependencies
After installation, you need to update project's dependencies. In particular, this includes installing Yii2 framework itself into the vendor folder.

The following commands are entered sequentially into the console (located in the project folder):
- `composer self-update` — Composer update;
- `composer global update` — Composer global update;
- `composer clear-cache` — clearing Composer's internal package cache;
- `composer update` — update all dependencies to the latest versions.

**NOTES:**
If for some reason the dependencies could not be installed (there is no vendor folder), then you need to manually add the vendor folder to this project.

CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

##### PostgreSQL:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=hrrobot;',
    'username' => 'postgres',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'hrrobot_',
    'schemaMap' => [
        'pgsql'=> [
            'class'=>'yii\db\pgsql\Schema',
            'defaultSchema' => 'public'
        ]
    ],
];
```

##### MySQL:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=hrrobot;',
    'username' => 'admin',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'hrrobot_',
];

```

**NOTES:**
- HRR-Interface won't create a new database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.

##### Database commands:
HRR-Interface contains commands for filling a new database with the initial data necessary for the application to run.
This set of commands is entered sequentially into the console (located in the project folder):
- `php yii migrate/up` — applying migrations (creating all tables in a new database);
- `php yii user/create-default-users` — creating a default users (administrator and five common users).

AUTHOR
-------------

[Nikita O. Dorodnykh](mailto:tualatin32@mail.ru)