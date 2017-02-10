<?php

/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 2/2/2017
 * Time: 8:17 PM
 */
require_once 'function.connect.php';

class game
{
    /*
     * Holds the database connection
     */
    public $dbh;

    /*
     * Start the database connection
     */
    public function __construct()
    {
        $this->connection = new connection();
        $this->dbh = $this->connection->connect();
    }

    /*
     * Function to create a new room
     */
    function createRoom()
    {
        $roomNumber = bin2hex(openssl_random_pseudo_bytes(4));
        if($this->isRoomExists($roomNumber)) {
            $this->createRoom();
        }
        return $roomNumber;
    }

    /*
     * Function to check whether the room created already exists
     */
    function isRoomExists($room)
    {
        $count = 0;
        try {
            $stmt = $this->dbh->prepare("SELECT room FROM players WHERE room = :room");
            $stmt->execute(array(":room" => $room));
            $count = $stmt->rowCount();
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }

        //Return boolean values depending on whether a room is already present
        if ($count > 0) {
           return true;
        }
        else {
           return false;
        }
    }

    function destroyRoom()
    {

    }

    function setPlayer($playerName, $boardSize, $roomNumber)
    {
        try {
            $stmt = $this->dbh->prepare("INSERT INTO players(room, playerName, boardSize) VALUES (:roomNumber, :playerName, :boardSize)");
            $stmt->execute(array(
                ":roomNumber" => $roomNumber,
                ":playerName" => $playerName,
                ":boardSize" => $boardSize
            ));
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    function findPlayer()
    {

    }

    function removePlayer()
    {

    }

    function logError($error)
    {
        $logFile = fopen('errors.log', 'ab');
        fwrite($logFile, date('[Y-m-d H:i:s] ') . $error . PHP_EOL);
        fclose($logFile);
    }

}