<?php

namespace ZipCode;

use Exception;

/**
 * Zip Code Range and Distance Calculation
 *
 * This class is a port of https://github.com/Quixotix/PHP-ZipCode-Class to support PostgreSQL.
 *
 * Calculate the distance between zip codes and find all zip codes within a
 * given distance of a known zip code.
 *
 * Project page: https://github.com/Quixotix/PHP-ZipCode-Class
 * Live example: http://www.micahcarrick.com/code/PHP-ZipCode/example.php
 *
 * @package        zipcode
 * @author         Micah Carrick
 * @copyright  (c) 2011 - Micah Carrick
 * @version        2.0
 * @license        http://opensource.org/licenses/gpl-3.0.html GNU General Public License v3
 */
class ZipCode {
    /**
     * @var string
     */
    protected $zip_code_id;
    /**
     * @var string
     */
    protected $zip_code;
    /**
     * @var string
     */
    protected $lat;
    /**
     * @var string
     */
    protected $lon;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $county;
    /**
     * @var string
     */
    protected $area_code;
    /**
     * @var string
     */
    protected $time_zone;
    /**
     * @var string
     */
    protected $state_prefix;
    /**
     * @var string
     */
    protected $state_name;
    /**
     * @var string
     */
    public $pg_table = 'zip_code';
    /**
     * @var resource PostgreSQL connection resource
     */
    public $pg_conn = false;
    /**
     * @var string[]
     */
    protected $pg_row;
    /**
     * @var string
     */
    protected $print_name;
    /**
     * @var int LOCATION_ZIP | LOCATION_CITY_STATE
     */
    protected $location_type;

    const UNIT_MILES          = 1;
    const UNIT_KILOMETERS     = 2;
    const MILES_TO_KILOMETERS = 1.609344;

    const LOCATION_ZIP        = 1;
    const LOCATION_CITY_STATE = 2;

    /**
     *  Constructor
     *
     *  Instantiate a new ZipCode object by passing in a location. The location
     *  can be specified by a string containing a 5-digit zip code, city and
     *  state, or latitude and longitude.
     *
     * @param  string
     *
     * @throws \Exception
     */
    public function __construct($location) {
        if (is_array($location)) {
            $this->setPropertiesFromArray($location);
            $this->print_name    = $this->zip_code;
            $this->location_type = $this::LOCATION_ZIP;
        } else {
            $this->location_type = $this->locationType($location);

            switch ($this->location_type) {

                case ZipCode::LOCATION_ZIP:
                    $this->zip_code   = $this->sanitizeZip($location);
                    $this->print_name = $this->zip_code;
                    break;

                case ZipCode::LOCATION_CITY_STATE:
                    $a                  = $this->parseCityState($location);
                    $this->city         = $a[0];
                    $this->state_prefix = $a[1];
                    $this->print_name   = $this->city;
                    break;

                default:
                    throw new Exception('Invalid location type for ' . __CLASS__);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->print_name;
    }

    /**
     * Calculate Distance using SQL
     *
     * Calculates the distance, in miles, to a specified location using pgSQL
     * math functions within the query.
     *
     * @access protected
     *
     * @param  string
     *
     * @return float
     * @throws \Exception
     */
    protected function calcDistanceSql($location) {
        $sql = 'SELECT 3956 * 2 * ATAN2(SQRT(POWER(SIN((RADIANS(t2.lat) - '
               . 'RADIANS(t1.lat)) / 2), 2) + COS(RADIANS(t1.lat)) * '
               . 'COS(RADIANS(t2.lat)) * POWER(SIN((RADIANS(t2.lon) - '
               . 'RADIANS(t1.lon)) / 2), 2)), '
               . 'SQRT(1 - POWER(SIN((RADIANS(t2.lat) - RADIANS(t1.lat)) / 2), 2) + '
               . 'COS(RADIANS(t1.lat)) * COS(RADIANS(t2.lat)) * '
               . 'POWER(SIN((RADIANS(t2.lon) - RADIANS(t1.lon)) / 2), 2))) '
               . 'AS "miles" '
               . "FROM {$this->pg_table} t1 CROSS JOIN {$this->pg_table} t2 ";


        switch ($this->location_type) {

            case ZipCode::LOCATION_ZIP:
                // note: zip code is sanitized in the constructor
                $from_location = $this->zip_code;
                $sql .= "WHERE t1.zip_code = '{$this->zip_code}' ";
                break;

            case ZipCode::LOCATION_CITY_STATE:
                $city          = @pg_escape_string($this->pg_conn, $this->city);
                $state         = @pg_escape_string($this->pg_conn, $this->state_prefix);
                $from_location = $city . ', ' . $state;
                $sql .= "WHERE (t1.city = '$city' AND t1.state_prefix = '$state')";
                break;

            default:
                throw new Exception('Invalid location type for ' . __CLASS__);
        }

        switch (ZipCode::locationType($location)) {
            case ZipCode::LOCATION_ZIP:
                $to_location = $this->sanitizeZip($location);
                $sql .= "AND t2.zip_code = '$to_location'";
                break;
            case ZipCode::LOCATION_CITY_STATE:
                $a             = $this->parseCityState($location);
                $city          = @pg_escape_string($this->pg_conn, $a[0]);
                $state         = @pg_escape_string($this->pg_conn, $a[1]);
                $from_location = $city . ', ' . $state;
                $sql .= "AND (t2.city = '$city' AND t2.state_prefix = '$state')";
                break;
        }

        $r = @pg_query($this->pg_conn, $sql);

        if (!$r) {
            throw new Exception(pg_last_error($this->pg_conn));
        }

        if (pg_numrows($r) == 0) {
            throw new Exception("Record does not exist calculating distance between $from_location and $to_location");
        }

        $miles = pg_fetch_result($r, 0);
        pg_free_result($r);

        return $miles;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAreaCode() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->city;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCity() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->city;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCounty() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->county;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStateName() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->state_name;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStatePrefix() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->state_prefix;
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    public function getDbRow() {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();
        return $this->pg_row;
    }

    /**
     * Get Distance To Zip
     *
     * Gets the distance to another zip code. The distance can be obtained in
     * either miles or kilometers.
     *
     * @param  string
     * @param  integer
     * @param  integer
     *
     * @return float
     */
    public function getDistanceTo($zip, $units = ZipCode::UNIT_MILES) {
        $miles = $this->calcDistanceSql($zip);

        if ($units == ZipCode::UNIT_KILOMETERS) {
            return $miles * ZipCode::MILES_TO_KILOMETERS;
        } else {
            return $miles;
        }
    }

    /**
     * @param string|int $range_from
     * @param string|int $range_to
     * @param int        $units UNIT_MILES | UNIT_KILOMETERS
     *
     * @return ZipCode[]
     * @throws \Exception
     */
    public function getZipsInRange($range_from, $range_to, $units = 1) {
        if (empty($this->zip_code_id)) $this->setPropertiesFromDb();

        $sql = "SELECT 3956 * 2 * ATAN2(SQRT(POWER(SIN((RADIANS({$this->lat}) - "
               . 'RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * '
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2)), SQRT(1 - POWER(SIN((RADIANS({$this->lat}) - "
               . "RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * "
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2))) AS \"miles\", z.* FROM {$this->pg_table} z "
               . "WHERE zip_code <> '{$this->zip_code}' "
               . "AND lat BETWEEN ROUND({$this->lat} - (25 / 69.172), 4) "
               . "AND ROUND({$this->lat} + (25 / 69.172), 4) "
               . "AND lon BETWEEN ROUND({$this->lon} - ABS(25 / COS({$this->lat}) * 69.172)) "
               . "AND ROUND({$this->lon} + ABS(25 / COS({$this->lat}) * 69.172)) "
               . "AND 3956 * 2 * ATAN2(SQRT(POWER(SIN((RADIANS({$this->lat}) - "
               . "RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * "
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2)), SQRT(1 - POWER(SIN((RADIANS({$this->lat}) - "
               . "RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * "
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2))) <= $range_to "
               . "AND 3956 * 2 * ATAN2(SQRT(POWER(SIN((RADIANS({$this->lat}) - "
               . "RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * "
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2)), SQRT(1 - POWER(SIN((RADIANS({$this->lat}) - "
               . "RADIANS(z.lat)) / 2), 2) + COS(RADIANS(z.lat)) * "
               . "COS(RADIANS({$this->lat})) * POWER(SIN((RADIANS({$this->lon}) - "
               . "RADIANS(z.lon)) / 2), 2))) >= $range_from "
               . "ORDER BY 1 ASC";

        $r = pg_query($this->pg_conn, $sql);
        if (!$r) {
            throw new Exception(pg_last_error($this->pg_conn));
        }
        $a = array();
        while ($row = pg_fetch_assoc($r)) {
            // TODO: load ZipCode from array
            $a[$row['miles']] = new ZipCode($row);
        }

        return $a;
    }

    /**
     * @return bool
     */
    protected function hasDbConnection() {
        if ($this->pg_conn) {
            return pg_ping($this->pg_conn);
        } else {
            return pg_ping();
        }
    }


    /**
     * @param $location
     *
     * @return bool|int
     */
    protected function locationType($location) {
        if (ZipCode::isValidZip($location)) {
            return ZipCode::LOCATION_ZIP;
        } elseif (ZipCode::isValidCityState($location)) {
            return ZipCode::LOCATION_CITY_STATE;
        } else {
            return false;
        }
    }

    /**
     * @param $zip
     *
     * @return int
     */
    static function isValidZip($zip) {
        return preg_match('/^[0-9]{5}/', $zip);
    }

    /**
     * @param $location
     *
     * @return bool
     */
    static function isValidCityState($location) {
        $words = preg_split('/\s*,\s*/', $location);

        if (empty($words) || count($words) != 2 || strlen(trim($words[1])) != 2) {
            return false;
        }

        if (!is_numeric($words[0]) && !is_numeric($words[1])) {
            return true;
        }

        return false;
    }

    /**
     * @param $location
     *
     * @return string[]
     * @throws \Exception
     */
    static function parseCityState($location) {
        $words = preg_split('/\s*,\s*/', $location);

        if (empty($words) || count($words) != 2 || strlen(trim($words[1])) != 2) {
            throw new Exception("Failed to parse city and state from string.");
        }

        $city  = trim($words[0]);
        $state = trim($words[1]);

        return [$city, $state];
    }

    /**
     * @param string|int $zip
     *
     * @return mixed
     */
    protected function sanitizeZip($zip) {
        return preg_replace("/[^0-9]/", '', $zip);
    }

    /**
     * @param array $properties
     *
     * @throws \Exception
     */
    protected function setPropertiesFromArray($properties) {
        if (!is_array($properties)) {
            throw new Exception("Argument is not an array");
        }

        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }

        $this->pg_row = $properties;
    }

    /**
     * @throws \Exception
     */
    protected function setPropertiesFromDb() {
        switch ($this->location_type) {

            case ZipCode::LOCATION_ZIP:
                $sql = "SELECT * FROM {$this->pg_table} t "
                       . "WHERE zip_code = '{$this->zip_code}' LIMIT 1";
                break;

            case ZipCode::LOCATION_CITY_STATE:
                $sql = "SELECT * FROM {$this->pg_table} t "
                       . "WHERE city = '{$this->city}' "
                       . "AND state_prefix = '{$this->state_prefix}' LIMIT 1";
                break;
        }

        $r   = pg_query($this->pg_conn, $sql);
        $row = pg_fetch_assoc($r);
        pg_free_result($r);

        if (!$row) {
            throw new Exception("{$this->print_name} was not found in the database.");
        }

        $this->setPropertiesFromArray($row);
    }
}
