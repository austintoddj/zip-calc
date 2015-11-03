<html>
<head>
    <title>PHP Zip Code Range and Distance Calculation Class for PostgreSQL</title>
</head>
<body>
<?php

include('./vendor/autoload.php');

// connect to the pgSQL database with the zip code table
$connectionString = "host=HOST port=PORT dbname=DBNAME user=USER password=PASSWORD";
$dbConnection     = pg_connect($connectionString);

Maps::setConnection($dbConnection);

// you can instantiate ZipCodesInCity with a zip code or with city and state
$brooklyn_park = new Maps("55445");
$minneapolis   = new Maps("Minneapolis, MN");

/*
You can get the distance to another location by specifying a zip code,
city/state string, or another ZipCodesInCity object. You can specify whether you want
to get the distance in miles or kilometers.
*/
echo "<h2>Get the distance between 2 zip codes</h2>";
$distance1 = round($brooklyn_park->getDistanceTo("55404"), 2);
$distance2 = round($brooklyn_park->getDistanceTo("Minneapolis, MN"), 2);
echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance1</strong> miles away from "
     . "zip code <strong>55404</strong><br/>";
echo "Zip code <strong>$brooklyn_park</strong> is <strong>$distance2</strong> miles away from "
     . "the city <strong>Minneapolis, MN</strong><br/>";
echo '<hr>';


$distance1 = round($minneapolis->getDistanceTo("55109"), 2);
$distance2 = round($minneapolis->getDistanceTo("Maplewood, MN"), 2);
echo "Zip code <strong>$minneapolis</strong> is <strong>$distance1</strong> miles away from "
     . "zip code <strong>55109</strong><br/>";
echo "Zip code <strong>$minneapolis</strong> is <strong>$distance2</strong> miles away from "
     . "the city <strong>Maplewood, MN</strong><br/>";
echo '<hr>';

/*
You can get all of the zip codes within a distance range from the zip. Here we
are doing all zip codes between 0 and 2 miles. The returned array contains the
distance as the array's key and the array element is another ZipCodesInCity object.
*/
echo "<h2>Get all zip codes within 15 miles from 55445 (<i>" . count($brooklyn_park->getZipsInRange(0, 15)) . "</i>)</h2>";
foreach ($brooklyn_park->getZipsInRange(0, 15) as $miles => $zip) {
    $miles = round($miles, 1);
    echo "Zip code <strong>$zip</strong> is <strong>$miles</strong> miles away from "
         . " <strong>$brooklyn_park</strong> ({$zip->getCounty()} county)<br/>";
}
echo '<hr>';


/*
You can get all of the zip codes within a city.
*/
echo "<h2>Get all zip codes in Brooklyn Park, MN</h2>";
$port = new Maps('Brooklyn Park, MN');
foreach ($port->getZipsInCity() as $value) {
    var_dump($value);
}

?>
</body>
</html>
