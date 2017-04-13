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
                if ('index' == response) {
                    window.location.href = 'index#notification';
                    return;
                }
                window.location.href = response;
            },
            error: function(error) {
               console.log(error);
            }
        });
    });
    //Hide notification on clicking anywhere on the notification div
    $('body').on('click', function () {
        if(!$('#notification').is(":visible")) {
            $(this).removeClass('bounceInDown').addClass('bounceOutUp');
            history.replaceState("", document.title, window.location.pathname);
        }
    });
});