## Zip code range and distance calculations using PHP and PostgreSQL

[![Total Downloads](https://poser.pugx.org/austintoddj/php-postgresql-zipcode-class/downloads)](https://packagist.org/packages/austintoddj/php-postgresql-zipcode-class) 
[![Latest Stable Version](https://poser.pugx.org/austintoddj/php-postgresql-zipcode-class/v/stable)](https://packagist.org/packages/austintoddj/php-postgresql-zipcode-class) 
[![Latest Unstable Version](https://poser.pugx.org/austintoddj/php-postgresql-zipcode-class/v/unstable)](https://packagist.org/packages/austintoddj/php-postgresql-zipcode-class) [![License](https://poser.pugx.org/austintoddj/php-postgresql-zipcode-class/license)](https://packagist.org/packages/austintoddj/php-postgresql-zipcode-class)

#### Purpose

* This project was started to convert a zip code class originally written by [Quixotix](https://github.com/Quixotix/PHP-ZipCode-Class) in 2005 from MySQL to PostgreSQL.

* This class calculates the distance between U.S. zip codes and finds all zip codes within a 
given radius of a known zip code.

#### Database Structure

The `CREATE` syntax for the table used in the example is as follows:

```sql
CREATE TABLE "public"."maps" (
    zip_code_id  SERIAL PRIMARY KEY,
    zip_code TEXT NOT NULL,
    city TEXT NOT NULL,
    county TEXT NOT NULL,
    state_name TEXT NOT NULL,
    state_prefix TEXT NOT NULL,
    area_code TEXT NOT NULL,
    time_zone TEXT NOT NULL,
    lat NUMERIC(10,7) NOT NULL,
    lon NUMERIC(10,7) NOT NULL
);
```

#### Step 1: Clone the Repository

```sh
git clone https://github.com/austintoddj/PHP-PostgreSQL-ZipCode-Class.git
```

#### Step 2: Composer

Run `composer install` to allow autoloading. If you don't have Composer installed on your machine, you can find instructions on how to download it [here](https://getcomposer.org/doc/00-intro.md#globally).

#### Step 3: PgSQL Import

Once inside your database, import the `maps.sql` table included in the project. If you need some assistance on getting up and running with a PgSQL database, find out more on [Digital Ocean](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-postgresql-on-ubuntu-14-04).

#### Step 4: Update the Credentials

In the `example.php` file, you will need to update the `$connectionString`.

#### Step 5: Run the Example

If you followed the steps up to this point correctly, you should see the following when you access `example.php` from a browser :

![Example Screenshot](https://raw.github.com/austintoddj/PHP-PostgreSQL-ZipCode-Class/master/images/pgsql-screen.png)

[4]: http://opensource.org/licenses/GPL-2.0

#### Thanks

Couldn't have done this project without the help of [Comanche](https://github.com/Comanche/). His help in refactoring the SQL search functions made this possible. Thanks!
