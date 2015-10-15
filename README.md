## Glance

An open source social dating platform built with love
by J. Ashley Craig (www.ashcraig.com) and offered as open source
under the MIT License.

Screens: http://bit.ly/1X4Z9ux

Demo: www.glancedate.com

## Concept

Glance is built using Semantic UI, PHP and Javascript.

Instead of a stale profile and a couple blurry pictures, Glance encourages users to express
themselves through daily posts. Think Twitter/Facebook for dating.

This is a Stateful platform that uses php sessions to manage activity.
While a (mostly) Stateless design was possible at the time of writing, using
DynamoDB or a caching server to manage sessions was too costly for this project.

If you plan to scale this app out (instead of up) you will most likely want to modify the code to
be stateless but in my testing, my load balancer rarely switched servers once a user was
authenticated and using Glance. So, it's your call :-)

The "recovery" and "signup" folders have their own asset directories..
while this seems redundant, this allows you to change their location on the
server. BE SURE TO UPDATE THE FILE INCLUDES if you change the dir location

The system can be run on just about any linux setup but it was designed for AWS Elastic Beanstalk, AWS Linux,
RDS server running MySQL

## Requirements

- PHP 5.6+
- PHP Extensions:
    - imagick
    - mbstring
    - mcrypt (be sure extension=mcrypt.so is in your PHP.ini)
    - gd
- MySQL (strongly consider AWS RDS server for best performance)

## Setup 

Stuff that has to be setup to use system

- Create a MySQL database using the "glance.sql" file (located in the SQL Setup dir)

- BE SURE to remove the SQL Setup dir

- MySQL db and connection info
    - data-inc.php

- AWS API
    - aws-inc.php

- Site definitions
    - def-inc.php

- Mailgun Setup
    - mailgun-inc.php

- Session (your domain)
    - session-inc.php

- Uploader (image server)
    - uploader-inc.php

- Setup your imaging server (site files are located: image-server-site dir)

- BE SURE to remove the image-server-site dir 

## Stuff that still needs work

- Protection on add/update functions to authenticate POST form (should be covered by connectivity monitor)

- Paging during search

- Connectivity monitor (connectivity-inc.php)

## Include Files

Important include files located in the root folder

- account-inc.php
    - Include for global methods dealing with member accounts

- activity-inc.php
    - Include that manages all site activity during navigation

- aws-inc.php
    - Include for AWS API connection info

- connectivity-inc.php
    - Include to monitor use/abuse of system. (unfinished concept - not used currently)

- data-inc.php
    - Include for MySQL server

- def-inc.php
    - The site definition file

- index.php
    - Your site's front page

- interact-inc.php
    - Include to manage all site interaction between users

- json-inc.php
    - JSON header include for data that needs to be returned in this format

- log-inc.php
    - Include to provide logging throughout the site

- logo-inc.php
    - The site's logo

- mailgun-inc.php
    - Include for mailgun support

- nav-inc.php
    - Site navigation include

- no-cache-inc.php
    - Include to attempt to force pages to load from server

- nohtml-inc.php
    - Include to remove all HTML from string

- output-inc.php
    - Include to provide sanitizing and output formatting

- recent-activity-inc.php
    - Include to manage all recent user activity throughout the site

- recent-friend-activity.php
    - Include to manage all recent recent friend activity throughout the site

- s3-uploader-inc.php
    - Include providing AWS S3 uploading methods

- session-inc.php
    - Include providing session parms

- timezone-inc.php
    - Include that defines the system timezone

- uploader-inc.php
    - Include that defines your image server(s)


