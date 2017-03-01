/**
 * Created by Ayan Dey on 2/22/2017.
 */
$(document).ready(function () {
    /**
     * Resize the board depending on viewport
     */
    function  resizetable() {
        var elem = $('#boardGrid');
        var newGridWidth = elem.outerWidth();
        elem.outerHeight(newGridWidth);
    }
    resizetable();
    $(window).resize(function () {
        resizetable();
    });

    /**
     * Initializing all selectors
     */
    var playerOne_name  =   $('#p1_name');
    var playerTwo_name  =   $('#p2_name');
    var playerOne_score =   $('#p1_score');
    var playerTwo_score =   $('#p2_score');
    var cellBlock       =   $('td');

    /**
     * Update Player name on joining
     * Stop auto refreshing when both player joins
     */
    var allJoined = false;
    var roomName = $('#room').val();
    var playerNameSet = function () {
        if(!allJoined) {
            $.ajax({
                type: "POST",
                url: "gameEngine/app.php",
                data: {
                    activityCode: 1,
                    room: roomName
                },
                success: function (ScoreBoardResponse) {
                    var obj = JSON.parse(ScoreBoardResponse);
                    var count = Object.keys(obj).length;
                    if (count == 1) {
                        playerOne_name.html(obj.p1_name);
                        setTimeout(playerNameSet, 3000);
                    }
                    else if (count == 2) {
                        playerOne_name.html(obj.p1_name);
                        playerTwo_name.html(obj.p2_name);
                        allJoined = true;
                        //Start the heartbeat to check if the other player is alive
                        setTimeout(startHeartbeat, 15000);
                        clearTimeout(playerNameSet);
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    };
    setTimeout(playerNameSet, 3000);

    /**
     * Heartbeat
     * Check whether the other player is live
     * Interval of 15 sec
     */
    function startHeartbeat() {
        $.ajax({
            type: "POST",
            url: "gameEngine/app.php",
            data: {
                activityCode: 2,
                room: roomName
            },
            success: function(beat) {
                if(beat) {
                    console.log("dead");
                }
                else {
                    console.log("alive");
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
        setTimeout(startHeartbeat, 15000);
    }

});