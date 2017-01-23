function showPopup() {

    var message = $.cookie('status');
    var type    = $.cookie('class') || 'danger';

    $.removeCookie('status', {path: '/'});
    $.removeCookie('class' , {path: '/'});

    if (message) $.bootstrapGrowl(message, {type: type});
}

$(function() {

    moment.locale('ru');
    $.bootstrapGrowl.default_options.delay = 6000;
    $.ajaxSetup({
        complete: function() { showPopup(); }
    });

    $('a.disabled').click(function (e) {
        e.preventDefault();
    });

    // блок с информаций об отделе
    $('#asu-info').click(function() {
        $('#info-block').slideToggle('slow');
    });

    // mysql ошибки или другие
    $('#status-text:not(:empty)').closest('#status-footer').show();

    $('[data-toggle="popover"]').popover();

    $('.modal').on('hide.bs.modal', function (e) {
        $(this).removeData('bs.modal');
    });

    showPopup();
});
