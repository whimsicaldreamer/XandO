<?php

/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 2/3/2017
 * Time: 11:08 AM
 */
class connection
{
    const NAME = "noughtsNcrosses";
    const AUTHOR = "Ayan Dey";
    const VERSION = "0.0.0";
    public $dbh;

    function connect()
    {
        $hostname = 'localhost';
        $dbname = 'noughtsandcrosses';
        $username = 'root';
        $password = '';
        try {
            $this->dbh = new PDO("mysql:host=$hostname; dbname=$dbname", $username, $password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo('ERROR: ' . $e->getMessage());
        }
        return $this->dbh;
    }
}