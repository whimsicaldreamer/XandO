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

    function updateState() {
        console.log('update state');
        $.post('gameEngine/app.php', {action: 'update', room: roomName}, function(response) {
            // update game table
            var players = JSON.parse(response);
            jQuery.each(['p1_name', 'p2_name'], function(_, key) {
                if (players[key]) {
                    $('#' + key).html(players[key]);
                } else {
                    $('#' + key).html('---');
                }
            });
            setTimeout(updateState, 5000);
        });
    }
    updateState();
});