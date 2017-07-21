(function ($) {
    $["template"] = function (tmpl, data) { return (template(tmpl, data)); }; $["tmpl"] = function (tmpl, data) { return $(template(tmpl, data)); }; var template = function (str, data) {
        if (!data)
            data = {}; return tmpl(str, data);
    }; (function () { var cache = {}; this.tmpl = function tmpl(str, data) { var fn = !/\W/.test(str) || /.js$/.test(str) ? cache[str] = cache[str] || tmpl(document.getElementById(str).innerHTML) : new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};" + "with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ").replace(/'(?=[^%]*%>)/g, "\t").split("'").join("\\'").split("\t").join("'").replace(/<%=(.+?)%>/g, "',$1,'").split("<%").join("');").split("%>").join("p.push('") + "');}return p.join('');"); return data ? fn(data) : fn; }; })();
})(jQuery);