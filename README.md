iftarcal
========

Iftar signup calendar for Islamic centers to use during the month of Ramadan

## Version: 0.5


## Description

The iftar signup calendar application allows Islamic centers to provide a web-based management app for community iftar during Ramadan.

This application was initially developed to support the Iftar program at the [Upper Westchester Muslim Society](uwms.org).  Hopefully it will be useful to other centers providing similar programs for their communities.

**Features**

* familiar calendar-based interface
* self-service signup for community members to select the date(s) they wish to host the iftar
* ability for administrators to update / delete signups
* automatic email notifications to hosts on signup, or modifications
* customizable for specific center needs or programs

## System requirements

iftarcal has been tested and deployed with PHP 5.2 and MySQL server 5.0 (community edition).

It is recommended to use a tool like phpMyAdmin to simplify creation and setup of the database table for iftarcal.

iftarcal has the following dependencies (versions of these are included in the distribution without modification):

* [Smarty php templates](http://www.smarty.net/)
* [PHPMailer class](https://github.com/PHPMailer/PHPMailer)
* [Bootstrap 3](http://getbootstrap.com/) web framework
* [jquery](https://jquery.com/)

## Customization

In order to customize iftarcal, you may want to modify some of the following files:

* `iftarcal_settings.php` - the main settings file - customize for your institution, Ramadan program, email, etc.
* `iftarcal_runconfig.php` - runtime and environment settings for controlling DEBUG and LOCAL operation
* `iftarcal.css` - sample stylesheet for iftar calendar (customize colors, fonts, etc.)
* `events.dat` - specify special events during the month, if any, that would take the place of the iftar on that day.  The filename should not be changed.  The format of the events.dat file is one event per line with tab-separated fields for the `full date`, `text with any html tags`, and `css style` from iftarcal.css.  The distribution contains an example file.
* `templates/` - email templates for signup, change, and removal notifications.  The path to template files may be changed in `iftarcal_settings.php`.


## Installation

1. Set up the iftarcal database using the iftarcal.sql initialization file

2. Modify settings and perform other customizations described above


## License

As of version 0.5 this software is distributed under the [Apache 2.0 license](http://www.apache.org/licenses/LICENSE-2.0.txt).  Please see the the accompanying `LICENSE` file for terms.

Previous versions were distributed under the GPL v3 license.

## Author

Anees Shaikh
Copyright (c) 2012-2016, Anees Shaikh
All rights reserved.
