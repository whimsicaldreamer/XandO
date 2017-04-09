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
            var playersData = JSON.parse(response);
            // Print the winner in console for now
            console.log(playersData.winner);
            var symbolColor;
            //Update player names
            jQuery.each(['p1_name', 'p2_name'], function(_, key) {
                if (playersData.playerNames[key]) {
                    $('#' + key).html(playersData.playerNames[key]);
                } else {
                    $('#' + key).html('---');
                }
            });
            //Update table with moves by both players
            jQuery.each(playersData.movesMade, function(i, key) {
               if(key != '-') {
                   if(key == '&#10008;') {
                       symbolColor = 'crosses';
                   }
                   else
                   if(key == '&#9711;') {
                       symbolColor = 'noughts';
                   }
                   $("td[data-cell='"+ i +"']").html(key).addClass(symbolColor);
               }
            });
            setTimeout(updateState, 2500);
        });
    }
    updateState();

    cellBlock.on('click', function () {
        var cellTarget = $(this);
        var cellNumber = cellTarget.data('cell');
        var symbolColor;
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
                if(moves.symbol == '&#10008;') {
                    symbolColor = 'crosses';
                }
                else
                if(moves.symbol == '&#9711;') {
                    symbolColor = 'noughts';
                }
                $("td[data-cell='"+ moves.cellNo +"']").html(moves.symbol).addClass(symbolColor);
            }
        });
    });
});