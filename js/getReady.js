/**
 * Created by Ayan Dey on 2/2/2017.
 */
$(document).ready(function() {
    //On page load
    var manpageBtn = $('#manual');
    $(manpageBtn).tooltip('show');
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
    //Hide notification on clicking anywhere on the page
    $(document).on('click', function () {
        var container = $('#notification');
        if(container.is(":visible")) {
            container.removeClass('bounceInDown').addClass('bounceOutUp');
            history.replaceState("", document.title, window.location.pathname);
        }
    });
    //Show the instructions panel
    $(manpageBtn).on('click', function() {
        $('#instructions').modal('show').addClass('animated bounceInDown');
    });
    //Close the instructions panel
    $('.close').on('click', function () {
        $('#instructions').addClass('bounceOutUp').one('animationend', function () {
            $(this).modal('hide').removeClass('animated bounceInDown bounceOutUp');
        });
    });
});