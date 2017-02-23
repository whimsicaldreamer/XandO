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
    var playerOne_name = $('#p1_name');
    var playerTwo_name = $('#p2_name');

    /**
     * Update Player name on joining
     * Stop auto refreshing when both player joins
     */
    var allJoined = false;
    var roomName = $('#room').val();
    var timeout = function () {
        if(!allJoined) {
            $.ajax({
                type: "POST",
                url: "gameEngine/app.php",
                data: {
                    activity: 1,
                    room: roomName
                },
                success: function (ScoreBoardResponse) {
                    var obj = JSON.parse(ScoreBoardResponse);
                    var count = Object.keys(obj).length;
                    if (count == 1) {
                        playerOne_name.html(obj.p1_name);
                    }
                    else if (count == 2) {
                        playerOne_name.html(obj.p1_name);
                        playerTwo_name.html(obj.p2_name);
                        allJoined = true;
                    }
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    };
    setTimeout(timeout, 2000);

});