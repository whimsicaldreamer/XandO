/**
 * Created by Ayan Dey on 2/2/2017.
 */
$(document).ready(function() {
    //On click of start button
    $('#start').on('click', function() {
        var playerName = $('#user').val();
        var gridSize   = $('#boardSize').val();
        var room       = $('#room').val();

        $.ajax({
           type: "POST",
            url: "gameEngine/app.php",
            data: {
                playerName: playerName,
                gridSize: gridSize,
                room: room
            },
            success: function (response) {
                console.log(response);
                window.location.href = response;
            },
            error: function(error) {
               console.log(error);
            }
        });
    });
});