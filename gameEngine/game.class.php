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

    const INDEX_SCORE_PLAYER_1 = 0;
    const INDEX_SCORE_PLAYER_2 = 1;
    const INDEX_SCORE_DRAW = 2;

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
        $allPlayers = $this->getPlayers($roomNumber);
        $activeSessionId = null;
        if(empty($allPlayers)) {
            session_start();
            session_regenerate_id();
            $_SESSION['moves'] = array_fill_keys(range(0, ($boardSize*$boardSize)-1), '-');
            $_SESSION['scores'] = [0,0,0];
            $_SESSION['gameStat'] = 'IN_PROGRESS';
            $activeSessionId = session_id();
        }
        else {
            $activeSessionId = $allPlayers[0]['sessionId'];
            session_id($activeSessionId);
            session_start();
        }
        $playerId = mt_rand();
        try {
            $stmt = $this->dbh->prepare("INSERT INTO players(playerId, room, playerName, boardSize, lastPing, sessionId) VALUES (:playerId, :roomNumber, :playerName, :boardSize, :lastPing, :sessionId)");
            $stmt->execute(array(
                ":playerId" =>  $playerId,
                ":roomNumber" => $roomNumber,
                ":playerName" => $playerName,
                ":boardSize" => $boardSize,
                ":lastPing" => time(),
                ":sessionId" => $activeSessionId
            ));
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
        }
        // ToDo Change cookie name before deployment
        setcookie("players_local_X_O", $playerId, time() + (86400 * 2), "/");
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
            $result[sprintf('p%d_', $count++)] = ['name' => $player['playerName'], 'score' => $this->getScores($count - 2)];
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
            if ($stmt->rowCount()) {
                if(PHP_SESSION_ACTIVE != session_status()) {
                    session_start();
                }
                //Reset scores when someone is really removed
                $_SESSION['scores'] = [0, 0, 0];
            }
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
                $structure .= "<td data-cell=$cellNumber></td>\n";
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
        $playerId = $_COOKIE['players_local_X_O'];
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

    /**
     * Function to add player moves to the session
     * @param $cell
     * @param $roomName
     * @return array
     */
    public function addMove($cell, $roomName)
    {
        session_start();
        $playerId = $_COOKIE['players_local_X_O'];
        $resultSet = $this->getPlayers($roomName);
        $result = [];
        $symbol = '';
        foreach($resultSet as $player) {
            $result[] = $player['playerId'];
        }
        $playerKey = array_search($playerId, $result);

        if($playerKey == 0) {
            $symbol = '&#10008;';
        }
        elseif ($playerKey == 1) {
            $symbol = '&#9711;';
        }

        if(isset($_SESSION['moves']) && $_SESSION['moves'][$cell] == '-') {
            $_SESSION['moves'][$cell] = $symbol;
            $response = ['cellNo' => $cell, 'symbol' => $symbol, 'code' => 0]; //The place is not taken
            return $response;
        }
        else {
            $response = ['cellNo' => $cell, 'symbol' => $symbol, 'code' => 1]; //The place is already taken
            return $response;
        }
    }

    /**
     * Function to get all moves made
     * @return mixed
     */
    public function getMoves()
    {
        if(PHP_SESSION_ACTIVE != session_status()) {
            session_start();
        }
        return $_SESSION['moves'];
    }

    /**
     * @param $state
     * @return string
     */
    public function whoIsWinning($state)
    {
        $n = sqrt(count($state));
        $rows = $this->isWin($state, $this->genPaths($n, 0,     1,      $n, $n), $n);
        $cols = $this->isWin($state, $this->genPaths($n, 0,     $n,     1,  $n), $n);
        $diUp = $this->isWin($state, $this->genPaths(1, $n-1,  $n-1,   0,  $n), $n);
        $diDn = $this->isWin($state, $this->genPaths(1,  0,     $n+1,   0,  $n), $n);

        if ($rows !== '-') return $rows;
        if ($cols !== '-') return $cols;
        if ($diUp !== '-') return $diUp;
        if (!in_array('-', $state)) {
            $this->updateScore(self::INDEX_SCORE_DRAW);
            return 'Draw';
        }
        return $diDn;
    }

    /**
     * Function to generate the paths to win
     * @param $count
     * @param $start
     * @param $incrementA
     * @param $incrementB
     * @param $length
     * @return array
     */
     private function genPaths($count, $start, $incrementA, $incrementB, $length)
    {
        $paths = [];
        for ($i = 0; $i < $count; $i++) {
            $path = [];
            for($j = 0; $j < $length; $j++) {
                array_push($path, $start + $i * $incrementB + $j * $incrementA);
            }
            array_push($paths, $path);
        }
        return $paths;
    }

    /**
     * @param $state
     * @param $paths
     * @param $cellsInALine
     * @return string
     */
    private function isWin($state, $paths, $cellsInALine)
    {
        for ($i = 0; $i < count($paths); $i++) {
            $currentPathResult = $this->isPathWin($state, $paths[$i], $cellsInALine);
            if ($currentPathResult != '-')
                return $currentPathResult;
        }
        return '-';
    }

    /**
     * @param $state
     * @param $path
     * @param $winThreshold
     * @return string
     */
    private function isPathWin($state, $path, $winThreshold)
    {
        if($winThreshold > 3) {
            $winThreshold = $winThreshold - 1;
        }
        $actualPathFollowed = "";
        for($j=0; $j<count($path); $j++) {
            $actualPathFollowed .= $state[$path[$j]];
        }
        $countX = substr_count($actualPathFollowed, '&#10008;');
        $countO = substr_count($actualPathFollowed, '&#9711;');

        if ($countX >= $winThreshold) {
            $this->updateScore(self::INDEX_SCORE_PLAYER_1);
            return '&#10008;';
        }
        elseif ($countO >= $winThreshold) {
            $this->updateScore(self::INDEX_SCORE_PLAYER_2);
            return '&#9711;';
        }
        else {
            return '-';
        }
    }

    public function newGame()
    {
        try {
            session_start();
            $_SESSION['moves'] = array_fill(0, count($_SESSION['moves']), '-');
            $_SESSION['gameStat'] = 'IN_PROGRESS';
            return 'success';
        }
        catch (Exception $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    public function getScores($index)
    {
        $allScores = $_SESSION['scores'];
        $result = $allScores[$index];
        return $result;
    }

    public function updateScore($index)
    {
        if($_SESSION['gameStat'] == 'IN_PROGRESS') {
            if (in_array($index, [self::INDEX_SCORE_PLAYER_1, self::INDEX_SCORE_PLAYER_2, self::INDEX_SCORE_DRAW])) {
                $_SESSION['scores'][$index] += 1;
                $_SESSION['gameStat'] = 'COMPLETED';
            } else {
                die;
            }
        }
    }
}