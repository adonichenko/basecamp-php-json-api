NAME:
    For access to the Basecamp REST API.

AUTHOR:
	Alexander Donichenko (adonichenko@gmail.com)

REQUIREMENTS:
    
    PHP 5.1+
    curl PHP extension
    MySQL 5+
  
DESCRIPTION:
Create a Message for each newly created To Do list item within the project. If
items in To Do list change (added, removed, text changed), then new comment should be added
to the appropriate (previously created) Message. 

The main differences from the classic API PHP-library: JSON-format and simply code

 	1. task1.sql - create database
 	2. /library/classes/ini/setting.datafinder.ini - login to mysql
 	3. run task1.php:
 	$ ./task1.php {$id project}
 	or 
	$ php task1.php {$id project}
	Example:
	$ php task1.php 10557188

