# How to install Imperium

Web based business management and accounting software

Imperium is an all-in-one business management software solution for small business seeking CRM, Work Order, Invoicing and Accounting features. Easily organize clients and contacts, provide quotes, track work and send bills. The Accounting module provides book-keeping functionality and reporting for small businesses.

Imperium provides the "power to control" all of your business data with a client centric approach.

## Requirements

  * Linux Server - Gentoo, Ubuntu have been tested
  * Apache 2.2
  * PHP 5.2
  * Radix 2012.x
  * PostgreSQL 9.0

MySQL may work but has not been tested as much as PostgreSQL

## Installation

Export the latest version to some location on the system such as `/opt/edoceo/imperium/`

```
git clone https://github.com/edoceo/imperium /opt/edoceo/imperium
```

Create the database user and the database itself

```
# psql -U postgres
postgres=# create user imperium;
postgres=# create database imperium with owner imperium encoding 'UTF8';
```

Change to the `approot/sql` directory then run each of those files in order.
```
# cd /opt/edoceo/imperium/approot/sql
# for f in *; do psql -U imperium -f $f; done
```

Copy `approot/etc/imperium.ini` to `approot/etc/imperium-local.ini`.
Edit imperium-local.ini to configure proper values for your environment.

Install the Repos via Composer

```
curl -sS https://getcomposer.org/installer | php
    ./composer.phar --no-dev install
```
