<?php
    require_once 'gameEngine/game.class.php';
    $roomName = $_GET['room'];
    if ($roomName == '') {
        header('Location: /index');
    }

    $gameHandler = new game();

    if (!$gameHandler->isRoomExists($roomName)) {
        header('Location: /index');
        die;
    }
    if (!isset($_COOKIE["players_X_O"])) {
        header('Location: /join/'.$roomName);
        die;
    }
    if (!$gameHandler->findPlayer($roomName, $_COOKIE["players_X_O"])) {
        header('Location: /join/'.$roomName);
        die;
    }

    $disqusPageIdentifier = 'h%^Fmx$sgaj3s';
    $structure = $gameHandler->buildBoard($roomName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <title>noughts N crosses</title>
    <base href="/">
    <link href="images/ico.png" type="image/x-icon" rel="shortcut icon" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/playground.css" rel="stylesheet">
    <link href="css/ionicons.min.css" rel="stylesheet">
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-97701111-1', 'auto');
        ga('send', 'pageview');

    </script>
</head>
<body>
<div id="notification" class="alert" role="alert"></div>
<div id="podium" class="modal animated" role="dialog" aria-labelledby="podium" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">Game Over!</h1>
            </div>
            <div class="modal-body">
                <span id="winnerSymbol"></span>
                <img id="crown" src="images/crown.png"/>
                <div id="wonText" class="box"></div>
            </div>
            <div class="modal-footer">
                <button id="restart" type="button" class="btn btn-lg btn-restart btn-success"><span class="ion ion-refresh podium-btn-icon-restart"></span></button>
                <a href="index"><button id="exit" type="button" class="btn btn-lg btn-exit btn-danger" ><span class="ion ion-close-circled podium-btn-icon-exit"></span></button></a>
            </div>
        </div>
    </div>
</div>
<div class="navbar navbar-default">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9 col-xs-12">
                <div class="navbar-header">
                    <a href="index" class="navbar-brand"><span class="ion ion-arrow-left-c"></span> Back</a>
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
                            <span id="p1_score" class="score clearfix">0</span>
                            <div id="p1_name" class="detail">Player 1</div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="counter-outer">
                            <span id="p2_score" class="score clearfix">0</span>
                            <span id="p2_name" class="detail">Player 2</span>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="counter-outer">
                            <span id="draw_score" class="score clearfix">0</span>
                            <span id="draw_title" class="detail">Draws</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="jumbotron playground">
    <div class="boardContainer">
        <table id="boardGrid" class="table table-bordered symbolSize-<?= $structure['size'] ?>">
            <?= $structure['structure'] ?>
        </table>
    </div>
</div>
<input id="room" type="hidden" value="<?= $roomName ?>">
<div class="comments">
    <div id="disqus_thread"></div>
    <div id="footer" class="container-fluid">
        <p>Copyright &copy; 2017-18, Ayan Dey</p>
    </div>
</div>


<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/app.min.js"></script>
<script>
     var disqus_config = function () {
     this.page.identifier = '<?php echo $disqusPageIdentifier; ?>';
     };

    (function() {
        var d = document, s = d.createElement('script');
        s.src = 'https://xando.disqus.com/embed.js';
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
    })();
</script>
<noscript>Please enable JavaScript.</noscript>
</body>
</html>