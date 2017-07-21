function hide_tips(error)
{
    window.clearTimeout(tips_timer);
    $('#dialogLayer').fadeOut();
    $('#maskLayer').fadeOut();
}

var tips_timer = null;
function show_tips(msg,type,timer)
{
    if(typeof timer == 'undefined' || !timer)
    {
        timer = 2000;
    }
    if($('#dialogLayer').size() == 0)
    {
        $('body').append('<div id="dialogLayer"></div><div id="maskLayer"></div>');
    }
    var icon = '';
    switch(type)
    {
        case 'load':
            icon = '<img src="/Public/Weixin/images/loading.gif" />';
        break;
        case 'warn':
            icon = '<i class="icon-exclamation-circle"></i>';
        break;
        case 'error':
            icon = '<i class="icon-times-circle"></i>';
        break;
        case 'happy':
            icon = '<i class="icon-happy2"></i>';
        break;
        case 'sad':
            icon = '<i class="icon-sad2"></i>';
        break;
        case '':
            icon = '';
            break;
        break;
        case 'ok':
        default:
            icon = '<i class="icon-check-circle"></i>';
        break;
    }
    $('#dialogLayer').html(icon+' <b>'+msg+'</b>');
    $('#dialogLayer').css({
        'top': $(window).scrollTop() + ($(window).height() - $('#dialogLayer').outerHeight())/2,
        'left': ($(window).width() - $('#dialogLayer').outerWidth())/2
    });
    $('#dialogLayer').fadeIn('slow');
    $('#maskLayer').fadeIn('slow');

    tips_timer = window.setTimeout(function()
    {
        hide_tips();
    },timer);
}