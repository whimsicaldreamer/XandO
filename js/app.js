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
    var playerOneName  =   $('#p1_name');
    var playerTwoName  =   $('#p2_name');
    var playerOneScore =   $('#p1_score');
    var playerTwoScore =   $('#p2_score');
    var cellBlock       =   $('td');
    var notification    =   $('#notification');

    /**
     * Update Player name on joining
     * Stop auto refreshing when both player joins
     */
    var roomName = $('#room').val();

    function updateState() {
        $.post('gameEngine/app.php', {action: 'update', room: roomName}, function(response) {
            // update game table
            var playersData = JSON.parse(response);
            jQuery.each(['p1_name', 'p2_name'], function(_, key) {
                if (playersData.playerNames[key]) {
                    $('#' + key).html(playersData.playerNames[key]);
                } else {
                    $('#' + key).html('---');
                }
            });
            setTimeout(updateState, 5000);
        });
    }
    updateState();

    cellBlock.on('click', function () {
        var cellTarget = $(this);
        var cellNumber = cellTarget.data('cell');
        $.post('gameEngine/app.php', {action: 'move', room: roomName, cell: cellNumber}, function(response) {
            console.log(response);
            var moves = JSON.parse(response);
            if(moves.code == 1) {
                $(notification).html('This place is already occupied!').addClass('alert-warning animated bounceInDown').show().one('animationend',function() {
                    $(this).addClass('bounceOutUp').one('animationend', function() {
                        $(this).removeClass('alert-warning animated bounceInDown bounceOutUp').html('');
                    });
                });
            }
            else {
                $("td[data-cell='"+ moves.cellNo +"']").html(moves.symbol);
            }
        });
    });
});