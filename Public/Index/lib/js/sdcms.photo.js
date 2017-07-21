$(function() {
    function i(e) {
        $("#originalpic img").eq(e).stop(!0, !0).fadeIn(800).siblings("img").hide(),
        $(".thumbpic li").eq(e).addClass("hover").siblings().removeClass("hover"),
        $.browser.msie && $.browser.version == "6.0" && !$.support.style && $("#aPrev,#aNext").css("height", $("#originalpic img").eq(e).height() + "px")
    }
    function s() {
        e >= 0 && e < n - 1 ? (++e, i(e)) : $.dialog({
            title: "\u63d0\u793a",
            icon: "face-sad",
            content: "\u5df2\u7ecf\u662f\u6700\u540e\u4e00\u5f20,\u70b9\u51fb\u786e\u5b9a\u91cd\u65b0\u6d4f\u89c8\uff01",
            lock: !0,
            opacity: "0.5",
            okVal: "\u786e\u5b9a",
            ok: function() {
                e = 0,
                i(0),
                n >= t && (aniPx = (n - t) * 92 + "px", $("#piclist ul").animate({
                    left: "+=" + aniPx
                },
                200)),
                r = 1
            },
            cancelVal: "\u53d6\u6d88",
            cancel: function() {}
        });
        if (r < 0 || r > n - t) return ! 1;
        $("#piclist ul").animate({
            left: "-=92px"
        },
        200),
        r++
    }
    function o() {
        if (e <= 0) return $.message({
            content: "\u5df2\u7ecf\u662f\u7b2c\u4e00\u5f20",
            time: 3e3
        }),
        !1;
        e >= 1 && (--e, i(e));
        if (r < 2 || r > n + t) return ! 1;
        $("#piclist ul").animate({
            left: "+=92px"
        },
        200),
        r--
    }
    var e = 0,
    t = 5,
    n = $("#originalpic img").length,
    r = 1;
    $("#originalpic img").eq(0).show(),
    $.browser.msie && $.browser.version == "6.0" && !$.support.style && $("#aPrev,#aNext").css("height", $("#originalpic img").eq(0).height() + "px"),
    $(".thumbpic li").eq(0).addClass("hover"),
    $(".thumbpic tt").each(function(e) {
        var t = e + 1 + "/" + n;
        $(this).html(t)
    }),
    $(".bntnext,#aNext").click(function() {
        s()
    }),
    $(".bntprev,#aPrev").click(function() {
        o()
    }),
    $(".thumbpic li").click(function() {
        e = $(".thumbpic li").index(this),
        i(e)
    })
})