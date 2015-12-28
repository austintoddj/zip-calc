<?php

namespace ZipCode;

class Maps extends ZipCode {

    /**
     * @var resource PostgreSQL connection resource
     */
    protected static $dbConnection = null;

    /**
     * Constructor.  Must call static::setConnection before constructor.
     *
     * @param string $location zip code or city, state combination
     *
     * @throws \UnexpectedValueException
     */
    public function __construct($location) {
        parent::__construct($location);
        $this->pg_table = 'maps';
        if (is_null(static::$dbConnection)) {
            throw new \UnexpectedValueException('Expected database connection resource but got null.');
        }
        $this->pg_conn = static::$dbConnection;
    }

    /**
     * Get PostgreSQL connection resource.
     *
     * @return resource
     */
    public static function getConnection() {
        return static::$dbConnection;
    }

    /**
     * Set PostgreSQL connection resource.  This is the first method that must be called before the class is used.
     *
     * @param resource $dbConnection
     */
    public static function setConnection($dbConnection) {
        static::$dbConnection = $dbConnection;
    }

    /**
     *  Get Zips in City
     *
     *  Provides a list of zip codes within a city if a city and state_prefix are given.
     *
     * @return  array
     */
    public function getZipsInCity() {

        $sql  = "SELECT * FROM {$this->pg_table} t "
                ."WHERE city = '{$this->city}' "
                ."AND state_prefix = '{$this->state_prefix}' ";

        $result = pg_query($this->pg_conn, $sql);

        return pg_fetch_all($result);
    }

}
