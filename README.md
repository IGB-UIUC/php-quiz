# PHP Quiz

[![Build Status](https://www.travis-ci.com/IGBIllinois/php-quiz.svg?branch=master)](https://www.travis-ci.com/IGBIllinois/php-quiz)

* A simple PHP Quiz
* Supports Multiple different quizes
* MySQL backend with LDAP for authentication

## Prerequisites
* PHP
* php-ldap
* MySql
* Ldap

## Installation
* Download the latest release from https://github.com/IGB-UIUC/php-quiz/releases or git clone the repository.  Place this in the document root of the web server
```
git clone https://github.com/IGB-UIUC/php-quiz.git
```
* Create Mysql Database
```
CREATE DATABASE phpquiz CHARACTER SET utf8 COLLATE utf8_general_ci;
```
* Import sql/php-quiz.sql to create database structure
```
mysql -u root -p phpquiz < sql/php-quiz.sql
```
* Create mysql user with insert,select,update,delete permissions on php-quiz database
```
CREATE USER 'phpquiz'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON phpquiz.* to 'phpquiz'@'localhost';
```
* Copy conf/config.inc.php.dist to conf/config.inc.php
```
cp conf/config.inc.php.dist conf/config.inc.php
```
* Edit includes/config.inc.php to have your mysql and ldap settings
```
//MySQL settings
@define ('DB_USER','XXXXXXXXXXX');
@define ('DB_PASSWORD','XXXXXXXXXXXX');
@define ('DB_HOST','localhost');
@define ('DB_NAME','phpquiz');

//LDAP Settings
@define ('LDAP_HOST','XXX.XXX.XXX.XXX');
@define ('LDAP_PEOPLE_DN', 'ou=people,dc=XXX,dc=XXX,dc=XXX');
@define ('LDAP_GROUP_DN', 'ou=group,dc=XXX,dc=XXX,dc=XXX');
@define ('LDAP_SSL','0');
@define ('LDAP_PORT','389');

@define ('DEFAULT_PAGE','exam_list');

@define ('UPLOAD_DIR','uploads/');
@define ('DEFAULT_QUESTION_POINTS',1);
@define ('DEFAULT_PASS_SCORE',0);
@define ('TITLE','PHP Quiz Webiste');
@define ('EMAIL','XXXXXXXXXXXX');
```
* Add initial user to database
```
INSERT INTO users(user_name,user_role) VALUES('USERNAME','1');
```
* Run composer install to install php/javascript dependencies
```
composer install
```
* Set /uploads folder for the apache user to have read/write permssions on it
```
chown apache.apache uploads
```
