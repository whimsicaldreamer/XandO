<?php
/**
 * Created by PhpStorm.
 * User: Ayan Dey
 * Date: 2/2/2017
 * Time: 6:58 PM
 */
require_once 'function.game.php'; // Require the game class to set the game environment
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
        $gameRoom = $gameHandler->createRoom(); // Generate a room
    }
    $gameHandler->setPlayer($playerName, $gridSize, $gameRoom); // Set the player with a room

    echo "playground?room=".$gameRoom; // Generate new room link
}