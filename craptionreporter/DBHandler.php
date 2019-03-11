<?php

class DBHandler {

    private static $INSTANCE;
    private static $HOST; // db server
    private static $DB_NAME; // database name
    private static $USER; // db user
    private static $PASSWORD; // db password (mention your db password here

    public function __construct($host, $dbname, $user, $password) {
        self::$HOST = $host;
        self::$DB_NAME = $dbname;
        self::$USER = $user;
        self::$PASSWORD = $password;
    }

    public static function getInstance($host, $dbname, $user, $password) {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new self($host, $dbname, $user, $password);
        }
        return self::$INSTANCE;
    }

    public static function connect() {

        date_default_timezone_set("Asia/Tehran");

        $conn = new PDO(
            'mysql:dbname='.self::$DB_NAME.';host='.self::$HOST.';charset=utf8',
            self::$USER,
            self::$PASSWORD,
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            )
        );
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }

}