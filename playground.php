<?php
    require_once 'gameEngine/function.game.php';
    $roomName = $_GET['room'];
    if($roomName == '') {
        header('Location: index');
    }

    $environmentReady = new game();

    if(!$environmentReady->isRoomExists($roomName)) {
        header('Location: index');
        die;
    }
    if(!isset($_COOKIE["players_local_".$roomName])) {
        header('Location: index?room='.$roomName.'&action=1');
    }

    $structure = $environmentReady->buildBoard($roomName);
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
    <link href="css/playground.css" rel="stylesheet">
    <link href="css/ionicons.min.css" rel="stylesheet">
</head>
<body>
<div class="navbar navbar-default">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9 col-xs-12">
                <div class="navbar-header">
                    <a href="index" class="navbar-brand"><span class="icon ion-arrow-left-c"></span> Back</a>
                </div>
            </div>
            <div class="col-md-3 col-xs-12 scoreboard">
                <div class="row">
                    <div class="col-xs-12">
                            <h4><span class="text-center">Scoreboard</span></h4>
                        </div>
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <div class="counter-outer">
                            <span class="score clearfix">0</span>
                            <div class="detail">Player 1</div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="counter-outer">
                            <span class="score clearfix">0</span>
                            <span class="detail">Player 2</span>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="counter-outer">
                            <span class="score clearfix">0</span>
                            <span class="detail">Draws</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="jumbotron playground">
    <div class="boardContainer">
        <table id="boardGrid" class="table table-bordered symbolSize-<?php echo $structure['size']; ?>">
            <?php
            echo $structure['structure'];
            ?>
        </table>
    </div>
</div>

<div class="comments" style="background-color: #9d9d9d;">
    comments section to be made
</div>






<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    function  resizetable() {
        var elem = $('#boardGrid');
        var newGridWidth = elem.outerWidth();
        elem.outerHeight(newGridWidth);
    }
    resizetable();
    $(window).resize(function () {
        resizetable();
    });

    $('td').on('click', function() {
       $(this).html('&#10008;').addClass('crosses');
    });
});
</script>
</body>
</html>