$(function() {
    var e, t, n = 0,
    r;
    $.message = function(i) {
        i.content == null && (i.content = ""),
        e == null && (e = $("body")),
        !window.XMLHttpRequest && t == null && (t = $("select")),
        t != null && t.css("visibility", "hidden"),
        i.type == null && (i.type = "warn"),
        i.time == null && (i.time = 1500),
        i.id == null ? r = "message" + n: r = i.id,
        n++;
        var s;
        s = '<div class="baseMessage" id="' + r + '"><span class="icon_' + i.type + '"></span><span class="icon_txt">' + i.content + '</span><span class="icon_r"></span></div>',
        e.append(s);
        var o = $("#" + r);
        return o.css({
            "margin-left": "-" + parseInt(o.width() / 2) + "px"
        }).slideToggle(),
        setTimeout(function() {
            o.animate({
                width: 0,
                height: 0
            },
            "toggle",
            function() {
                o.remove(),
                t != null && t.css("visibility", "visible")
            })
        },
        i.time),
        r
    }
})