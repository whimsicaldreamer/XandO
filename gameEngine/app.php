<?php
/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 2/2/2017
 * Time: 6:58 PM
 */
require_once 'game.class.php'; // Require the game class to set the game environment
$gameHandler = new game(); //Create new instance of game class

if (isset($_POST['playerName']) && isset($_POST['gridSize'])) {
    $playerName = $_POST['playerName']; //Get the player name
    $gameRoom   = $_POST['room']; //Get the game room
    $gridSize   = explode(" X ", $_POST['gridSize']); //Get the board size and break it for the size
    $gridSize   = $gridSize[0]; // Grid size to be used later on

    // Check whether player name is empty
    if ($playerName == "") {
        $playerName = "PLY".mt_rand(0, 9999999999); // Generate a random player number with a 'PLY' prefix
    }

    if (empty($gameRoom)) {
        $gameRoom = $gameHandler->generateRoom(); // Generate a room
    }

    if ($gameHandler->isRoomEmpty($gameRoom)) {
        $gameHandler->setPlayer($playerName, $gridSize, $gameRoom); // Set the player with a room
        echo '/room/'.$gameRoom; // Generate new room link
    } else {
        echo 'index';
    }
    die;
}

if(!empty($_POST['action']) && 'reset' == $_POST['action']) {
    echo $gameHandler->newGame();
}

// all the following actions depends on $_POST['room'] parameter
if (empty($_POST['room'])) {
    die;
}

if (!empty($_POST['action']) && 'update' == $_POST['action']) {
    $gameHandler->updatePing($_POST['room']);
    $gameHandler->removeInactivePlayer($_POST['room']);
    $allMoves = $gameHandler->getMoves(); // Session starts here
    $dataSet = ['playerDetails' => $gameHandler->getPlayersNames($_POST['room']), 'movesMade' => $allMoves, 'winner' => $gameHandler->whoIsWinning($allMoves), 'tie' => $gameHandler->getScores(game::INDEX_SCORE_DRAW)];
    echo json_encode($dataSet);
}

if(!empty($_POST['action']) && 'move' == $_POST['action']) {
    echo json_encode($gameHandler->addMove($_POST['cell'], $_POST['room']));
}