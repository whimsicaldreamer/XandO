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
        try {
            $stmt = $this->dbh->prepare("SELECT room FROM players WHERE room = :room");
            $stmt->execute(array(":room" => $room));
            return $stmt->rowCount() > 0;
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return false;
    }

    /*
     * Function to check whether the room has 2 players
     */
    function isRoomEmpty($room)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT playerName FROM players WHERE room = :room");
            $stmt->execute(array(":room" => $room));
            return $stmt->rowCount() <= 2;
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return false;
    }

    function destroyRoom()
    {

    }

    /*
     * Function to set a player with all details in db
     */
    function setPlayer($playerName, $boardSize, $roomNumber)
    {
        $playerId = rand();
        try {
            $stmt = $this->dbh->prepare("INSERT INTO players(playerId, room, playerName, boardSize, lastPing) VALUES (:playerId, :roomNumber, :playerName, :boardSize, :lastPing)");
            $stmt->execute(array(
                ":playerId" =>  $playerId,
                ":roomNumber" => $roomNumber,
                ":playerName" => $playerName,
                ":boardSize" => $boardSize,
                ":lastPing" => time()
            ));
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        // ToDo Change cookie name before deployment
        setcookie("players_local_".$roomNumber, $playerId, time() + (86400 * 2), "/");
    }

    /*
     * Function to get the players details
     */
    function getPlayer($roomName)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM players WHERE room = :room ORDER BY id ASC");
            $stmt->execute(array(":room" => $roomName));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());

            return null;
        }
    }

    /*
     * Function to get player order number
     */
    function getPlayerOrder($roomName)
    {
        $resultSet = $this->getPlayer($roomName);
        $count = count($resultSet);
        if($count < 2) {
            $retData = array('p1_name' => $resultSet[0]['playerName']);
        }
        else {
            $retData = array('p1_name' => $resultSet[0]['playerName'], 'p2_name' => $resultSet[1]['playerName']);
        }
        return json_encode($retData);
    }

    /*
     * Function to check whether the particular player is alive
     */
    function isPlayerAlive($roomName)
    {
        $resultSet = $this->getPlayer($roomName);
        $playerId = $_COOKIE['players_local_'.$roomName];
        $res = current($this->findOtherPlayer($resultSet, 'playerId', $playerId));
        $lastPing = $res['lastPing'];
        $now = time();
        $timeGap = $now - $lastPing;

        if($timeGap > 30) {
            $this->removePlayer($playerId, $roomName);
            return true;
        }
        else {
            try {
                $stmt = $this->dbh->prepare("UPDATE players SET lastPing = :now WHERE playerId = :playerID AND room = :roomName");
                $stmt->execute(array(
                    ":now" => $now,
                    ":playerID" => $playerId,
                    ":roomName" => $roomName
                ));
            } catch (Exception $e) {
                $this->logError($e->getMessage());
            }
            return false;
        }
    }

    /*
     * Function to get the opponent player details
     */
    function findOtherPlayer($result, $key, $value) {
        return array_filter($result, function ($v) use ($key, $value)  {
            return $v[$key] !== $value;
        });
    }

    /*
     * Function to get rid of a player
     */
    function removePlayer($playerId, $roomName)
    {
        try {
            $stmt = $this->dbh->prepare("DELETE FROM players WHERE playerId =  :playerId AND room = :roomName");
            $stmt->execute(array(
                ":playerId" => $playerId,
                ":roomName" => $roomName
            ));
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    /*
     * Generate game board depending on board size
     */
    function buildBoard($roomName)
    {
        $playerDetails = $this->getPlayer($roomName);
        $gridSize = $playerDetails[0]['boardSize'];

        $structure = "";
        for($row = 1; $row <= $gridSize; $row++) {
            $structure .= "<tr>\n";
            for($col = 1; $col <= $gridSize; $col++) {
                $structure .= "<td>&nbsp;</td>\n";
            }
            $structure .= "</tr>\n";
        }
        $structureArr = array('structure' => $structure, 'size' => $gridSize);
        return $structureArr;
    }

    /*
     *  Function to get board size
     */
    function getBoardSize($roomName)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT boardSize FROM players WHERE room = :room");
            $stmt->execute(array(":room" => $roomName));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$result) {
                return null;
            }
            return $result[0]['boardSize'];
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    function logError($error)
    {
        $logFile = fopen('errors.log', 'ab');
        fwrite($logFile, date('[Y-m-d H:i:s] ') . $error . PHP_EOL);
        fclose($logFile);
    }


}