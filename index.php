<?php
    $state  = "";
    $boardSize = "";
    $room = !empty($_GET['room']) ? $_GET['room'] : '';
    $action = !empty($_GET['action']) ? $_GET['action'] : '';
    if ($action == 1) {
        require_once 'gameEngine/function.game.php';
        $environmentReady = new game();
        $boardSize = $environmentReady->getBoardSize($room);
        $state = "disabled = 'disabled'";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <title>noughts N crosses</title>
    <link href="images/ico.png" type="image/x-icon" rel="shortcut icon" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <link href="css/ionicons.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid site-wrapper">
    <div class="centerMe">
        <a href="index"><h1>n<span class="highlightO">o</span>ughts <span class="highlightX">X</span> cr<span class="highlightO">o</span>sses</h1></a>
        <p id="subHeader"><small>an age old time killer game</small></p>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-5 col-sm-offset-2">
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="basic-addon1">
                            <span class="ion ion-person"></span> Name
                        </span>
                        <input id="user" type="text" class="form-control form-inline" placeholder="Enter your name">
                    </div>
                </div>
                <div class="col-sm-3 lefty">
                    <div class="input-group input-group-lg">
                        <span class="input-group-addon" id="basic-addon2">
                            <span class="ion ion-ios-grid-view-outline"></span> Board
                        </span>
                        <select id="boardSize" class="form-control form-inline text-center" title="Select Board Size" <?php echo $state; ?>>
                            <option>3 X 3</option>
                            <option>4 X 4</option>
                            <option>5 X 5</option>
                            <option>6 X 6</option>
                            <?php
                            if($boardSize != "") {
                            ?>
                                <option selected="selected"><?php echo $boardSize.' X '.$boardSize; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 col-sm-offset-4">
                    <button id="start" type="button" class="btn btn-default btn-lg btn-block">Let's Play!</button>
                    <input type="hidden" id="room" value="<?= $room ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="navbar navbar-fixed-bottom">
    <div class="container-fluid">
        <p>Copyright &copy; 2017-18, Ayan Dey</p>
    </div>
</div>
<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/getReady.js"></script>
</body>
</html>