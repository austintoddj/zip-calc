<html>

    <head>

        <title>PHP PostgreSQL ZipCode Class</title>

        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

    </head>

    <body>

        <div class="container">

        <?php

        include('./vendor/autoload.php');

        use ZipCode\Maps;

        /*
         * Connect to the pgSQL database with the zip_code table
         */
        $connectionString = "host=localhost port=5432 dbname=database user=username password=password";
        $dbConnection     = pg_connect($connectionString);

        Maps::setConnection($dbConnection);

        /*
         * Instantiate ZipCodesInCity with a zip code or with city and state
         */
        $brooklyn_park = new Maps("55445");
        $minneapolis   = new Maps("Minneapolis, MN");

        /*
         * You can get the distance to another location by specifying a zip code,
         * city/state string, or another ZipCodesInCity object. You can specify whether you want
         * to get the distance in miles or kilometers.
         */
        $distance1 = round($brooklyn_park->getDistanceTo("55404"), 2);
        $distance2 = round($brooklyn_park->getDistanceTo("Minneapolis, MN"), 2);
        $distance3 = round($minneapolis->getDistanceTo("55109"), 2);
        $distance4 = round($minneapolis->getDistanceTo("Maplewood, MN"), 2);

        ?>

        <h2>Get distance between 2 zip codes</h2>

        <hr>

        <h4>With Numeric Zip Codes Supplied</h4>

        <ul>

            <li><?php echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance1</strong> miles away from " . "zip code <strong>55404</strong><br/>"; ?></li>

            <li><?php echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance2</strong> miles away from " . "the city <strong>Minneapolis, MN</strong><br/>"; ?></li>

        </ul>

        <h4>With City Name Supplied</h4>

        <ul>

            <li><?php echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance3</strong> miles away from " . "zip code <strong>55404</strong><br/>"; ?></li>

            <li><?php echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance4</strong> miles away from " . "the city <strong>Minneapolis, MN</strong><br/>"; ?></li>

        </ul>

        <hr>

        <?php

        /*
         * You can get all of the zip codes within a distance radius from the zip. Here we
         * are getting all zip codes between 0 and 15 miles. The returned array contains the
         * distance as the array's key and the array element is another ZipCodesInCity object.
         */
        echo "<h2>Get all zip codes within 15 miles from 55445 (<i>" . count($brooklyn_park->getZipsInRange(0, 15)) . "</i>)</h2>";

        foreach ($brooklyn_park->getZipsInRange(0, 15) as $miles => $zip) {

            $miles = round($miles, 1);

            echo "Zip code <strong>$zip</strong> is <strong>$miles</strong> miles away from " . " <strong>$brooklyn_park</strong> ({$zip->getCounty()} county)<br/>";

        }

        echo '<hr>';

        /*
         * You can get all of the zip codes within a city.
         */
        echo "<h2>Get all zip codes in Brooklyn Park, MN</h2>";

        $brooklyn_park = new Maps('Brooklyn Park, MN');

        foreach ($brooklyn_park->getZipsInCity() as $value) {

            var_dump($value);

        }

        ?>

        </div>

    </body>

</html>
