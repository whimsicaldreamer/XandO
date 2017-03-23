<?php

/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 2/2/2017
 * Time: 8:17 PM
 */
require_once 'connection.class.php';

class game
{
    /**
     * Holds the database connection
     */
    public $dbh;

    /**
     * Start the database connection
     */
    public function __construct()
    {
        $this->connection = new connection();
        $this->dbh = $this->connection->connect();
    }

    /**
     * Function to create a new game room
     */
    public function generateRoom()
    {
        $roomNumber = bin2hex(openssl_random_pseudo_bytes(4));
        if ($this->isRoomExists($roomNumber)) {
            $this->generateRoom();
        }
        return $roomNumber;
    }

    /**
     * Function to check whether the room created already exists
     * @param $room
     * @return bool
     */
    public function isRoomExists($room)
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

    /**
     * Function to check whether the room has 2 players
     * @param $room
     * @return bool
     */
    public function isRoomEmpty($room)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT playerName FROM players WHERE room = :room");
            $stmt->execute(array(":room" => $room));
            return $stmt->rowCount() < 2;
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return false;
    }

    public function destroyRoom()
    {

    }

    /**
     * Function to set a player with all details in db
     * @param $playerName
     * @param $boardSize
     * @param $roomNumber
     */
    public function setPlayer($playerName, $boardSize, $roomNumber)
    {
        $playerId = mt_rand();
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

    /**
     * Function to get details of all players in a room
     * @param $roomName
     * @return array|null
     */
    public function getPlayers($roomName)
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

    /**
     * Function to get player names in order
     * @param $roomName
     * @return array
     */
    public function getPlayersNames($roomName)
    {
        $resultSet = $this->getPlayers($roomName);
        $result = [];
        $count = 1;
        foreach ($resultSet as $player) {
            $result[sprintf('p%d_name', $count++)] = $player['playerName'];
        }
        return $result;
    }

    public function findPlayer($roomName, $playerId)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT id FROM players WHERE playerId = :playerId AND room = :roomName");
            $stmt->execute(array(
                ":playerId" => $playerId,
                ":roomName" => $roomName
            ));

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        return false;
    }

    /**
     * Function to remove inactive players in the board
     * @param $roomName
     * @param int $timeout
     */
    public function removeInactivePlayer($roomName, $timeout = 30)
    {
        try {
            $stmt = $this->dbh->prepare("DELETE FROM players WHERE room = :roomName AND :now - lastPing > :timeout");
            $stmt->execute(array(
                ":roomName" => $roomName,
                ":now" => time(),
                ":timeout" => $timeout,
            ));
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    /**
     * Function to get rid of a player
     * @param $playerId
     * @param $roomName
     */
    public function removePlayer($playerId, $roomName)
    {
        try {
            $stmt = $this->dbh->prepare("DELETE FROM players WHERE playerId = :playerId AND room = :roomName");
            $stmt->execute(array(
                ":playerId" => $playerId,
                ":roomName" => $roomName
            ));
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    /**
     * Generate game board depending on board size
     * @param $roomName
     * @return array
     */
    public function buildBoard($roomName)
    {
        $playerDetails = $this->getPlayers($roomName);
        $gridSize = $playerDetails[0]['boardSize'];
        $cellNumber = 0;
        $structure = "";
        for($row = 1; $row <= $gridSize; $row++) {
            $structure .= "<tr>\n";
            for($col = 1; $col <= $gridSize; $col++) {
                $structure .= "<td data-cell=$cellNumber>&nbsp;</td>\n";
                $cellNumber++;
            }
            $structure .= "</tr>\n";
        }
        $structureArr = array('structure' => $structure, 'size' => $gridSize);
        return $structureArr;
    }

    /**
     * Function to get board size
     * @param $roomName
     * @return null
     */
    public function getBoardSize($roomName)
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

    /**
     * @param $error
     */
    public function logError($error)
    {
        $logFile = fopen('errors.log', 'ab');
        fwrite($logFile, date('[Y-m-d H:i:s] ') . $error . PHP_EOL);
        fclose($logFile);
    }

    /**
     * Update your own last ping time
     * @param $roomName
     */
    public function updatePing($roomName)
    {
        $playerId = $_COOKIE['players_local_'.$roomName];
        $now = time();
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
    }

}
