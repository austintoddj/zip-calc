PHP Zip Code Range and Distance Calculation for PostgreSQL
===========================================

**Calculate the distance between U.S. zip codes and find all zip codes within a 
given distance of a known zip code.**

This project was started to port a zip code class originally written by [Micah Carrick](https://github.com/Quixotix/PHP-ZipCode-Class) in 2005 from MySQL to PostgreSQL. 


Zip Code Database
-----------------

The `ZipCode` class is based on a PostgreSQL table or view with the following fields:

    zip_code_id      int(11) PRIMARY KEY
    zip_code         varchar(5) UNIQUE KEY
    city             varchar(50)
    county           varchar(50)
    state_name       varchar(50)
    state_prefix     varchar(2)
    area_code        varchar(3)
    time_zone        varchar(50)
    lat              float
    lon              float

The default name for this table is `zip_code`.

**Original Database (obsolete)**

The original zip code database was derived from 2000 U.S. Census data and manually
tweaked over the years when a zip code was missing or incorrect. This database
is known to have some missing and inaccurate zip codes. 

You can find the SQL script with the entire `zip_code` table in 
`/data/obsolete/zip_code.sql`. If you do not have access to your database from 
the command line, such as using phpMyAdmin, you will have to split the script
into multiple files and upload them one at a time.

**New Databases**

There are numerous sources for U.S. zip code databases. Some are free and some 
can be purchased. You can use one of these databases by either copying the 
necessary fields from the source table to the `zip_code` table.

Demo
---------

See `example.php` for example usage.


License
-------

[GNU General Public License v2][4]

[4]: http://opensource.org/licenses/GPL-2.0

Thanks
---------

Couldn't have done this project without the help of [Comanche](https://github.com/Comanche/). His refactoring of the SQL search functionality made this possible. Thanks!
