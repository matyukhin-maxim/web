/**
 * Created by fellix on 09.08.16.
 */
$(function () {

    $('#board').click(function (e) {
        var offset = $(this).offset();
        //console.info(e.pageX - offset.left, e.pageY - offset.top);


        $.post('/game/check/', {x:e.pageX - offset.left, y:e.pageY - offset.top},
        function (data) {
            $('#response').html(data);
        });

    });

    $('#reload').click(function (e) {
        e.preventDefault();
        $('#board').attr('src', '/game/generate/?sid=' + Math.random());
        setTimeout(showPopup, 2000);
    });
});