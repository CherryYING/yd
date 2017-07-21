document.writeln("<style type=\"text\/css\">");
document.writeln("");
document.writeln("#windowcenter {");
document.writeln("	width:290px;");
document.writeln("	position:fixed;");
document.writeln("	display:none;");
document.writeln("	bottom:30px;");
document.writeln("	left:50%;");
document.writeln("	 z-index:9999;");
document.writeln("	margin-left:-145px;");
document.writeln("	padding:2px;");
document.writeln("	border-radius:3px;");
document.writeln("	-webkit-border-radius:3px;");
document.writeln("	-moz-border-radius:3px;");
document.writeln("	background-color: #ffffff;");
document.writeln("	-webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	-moz-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	-o-box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);");
document.writeln("	font:14px\/1.5 Microsoft YaHei,Helvitica,Verdana,Arial,san-serif;");
document.writeln("}");
document.writeln(".window .title {");
document.writeln("	background-color: #2BB2A3;");
document.writeln("	line-height: 26px;");
document.writeln("  padding: 5px 5px 5px 10px;");
document.writeln("	color:#ffffff;");
document.writeln("	text-shadow:0px 1px 1px #14544D;");
document.writeln("	font-size:16px;");
document.writeln("	border-radius:3px 3px 0 0;");
document.writeln("	-webkit-border-radius:0.5em 0.5em 0 0;");
document.writeln("	-moz-border-radius:0.5em 0.5em 0 0;");
document.writeln("	");
document.writeln("}");
document.writeln(".window .content {");
document.writeln("	\/*min-height:100px;*\/");
document.writeln("	overflow:auto;");
document.writeln("	padding:10px;");
document.writeln("	background: linear-gradient(#FBFBFB, #EEEEEE) repeat scroll 0 0 #FFF9DF;");
document.writeln("    color: #222222;");
document.writeln("    text-shadow: 0 1px 0 #FFFFFF;");
document.writeln("	border-radius: 0 0 0.6em 0.6em;");
document.writeln("	-webkit-border-radius: 0 0 0.6em 0.6em;");
document.writeln("	-moz-border-radius: 0 0 0.6em 0.6em;");
document.writeln("}");
document.writeln(".window #txt {");
document.writeln("	min-height:30px;font-size:16px; line-height:22px;");
document.writeln("}");
document.writeln(".window #windowclosebutton {");
document.writeln("	margin-top:10px;");
document.writeln("}");
document.writeln(".window .btn {");
document.writeln("	height:40px;");
document.writeln("	line-height:40px;");
document.writeln("}");
document.writeln("input::-moz-placeholder, textarea::-moz-placeholder { color: #999;}");
document.writeln(".flex{");
document.writeln("	display:box;");
document.writeln("	display:-moz-box;");
document.writeln("	display:-webkit-box;");
document.writeln("}");
document.writeln(".window .title .close {");
document.writeln("	float:right;");
document.writeln("	background-image: url(\"data:image\/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAACTSURBVEhL7dNtCoAgDAZgb60nsGN1tPLVCVNHmg76kQ8E1mwv+GG27cestQ4PvTZ69SFocBGpWa8+zHt\/Up+IN+MhgLlUmnIE1CpBQB2COZibfpnXhHFaIZkYph0SOeeK\/QJ8o7KOek84fkCWSBtfL+Ny2MPpCkPFMH6PWEhWhKncIyEk69VfiUuVhqJefds+YcwNbEwxGqGIFWYAAAAASUVORK5CYII=\");");
document.writeln("	width:26px;");
document.writeln("	height:26px;");
document.writeln("	display:block;	");
document.writeln("}");
document.writeln(".confirm-box{");
document.writeln("    position: fixed;");
document.writeln("    top:50%;");
document.writeln("    left:50%;");
document.writeln("    padding:0px 15px;");
document.writeln("    width:220px;");
document.writeln("    -moz-box-sizing:border-box;");
document.writeln("    -webkit-box-sizing:border-box;");
document.writeln("    box-sizing:border-box;");
document.writeln("    background-color: rgba(0, 0, 0, 0.8);");
document.writeln("    color:#FFFFFF;");
document.writeln("    border-radius: 2px;");
document.writeln("    margin-left: -110px;");
document.writeln("    margin-top:-55px;");
document.writeln("    z-index:1000001;");
document.writeln("}");
document.writeln(".confirm-box .confirm-box-txt{");
document.writeln("    text-align: center;");
document.writeln("    margin:20px auto;");
document.writeln("}");
document.writeln(".confirm-box .confirm-box-btns{");
document.writeln("    width:190px;");
document.writeln("    padding-bottom:10px;");
document.writeln("    text-align: center;");
document.writeln("}");
document.writeln(".confirm-box .confirm-box-btns a{");
document.writeln("    background-color: #FFFFFF;");
document.writeln("    display:inline-block;");
document.writeln("    border-radius: 2px;");
document.writeln("    margin:5px;");
document.writeln("    text-align: center;");
document.writeln("    width:80px;");
document.writeln("    height:30px;");
document.writeln("    line-height: 30px;");
document.writeln("    color:#444444;");
document.writeln("}");
document.writeln(".confirm-mash{");
document.writeln("    position: fixed;");
document.writeln("    left:0px;");
document.writeln("    top:0px;");
document.writeln("    right:0px;");
document.writeln("    bottom:0px;");
document.writeln("    background-color: rgba(255,255,255,0.3);");
document.writeln("    z-index:1000000;");
document.writeln("}");
document.writeln("<\/style>");
document.writeln("<div class=\"window\" id=\"windowcenter\">");
document.writeln("	<div id=\"title\" class=\"title\"><span class=\"close\" id=\"alertclose\"><\/span><span class=\"tit_txt\">消息提醒<\/sapn></div>");
document.writeln("	<div class=\"content\">");
document.writeln("	 <div id=\"txt\"><\/div>");
document.writeln("	 <input type=\"button\" value=\"确定\" id=\"windowclosebutton\" name=\"确定\" class=\"btn btn-block\">	");
document.writeln("	<\/div>");
document.writeln("<\/div>");
$(document).ready(function ()
{
	$("#alertclose").click(function ()
	{
		$("#windowcenter").slideUp(500);
	});
});
function alert(title)
{
	$('#windowcenter #title .tit_txt').text('消息提醒');
	$("#txt").html(title);
	set_pos();
	$("#windowcenter").slideToggle("slow");
	$("#windowclosebutton").unbind().bind('click',function () {
		$("#windowcenter").slideUp(500);
	});
	// setTimeout('$("#windowcenter").slideUp(500)',8000);
}
function show_error(msg)
{
	$('#windowclosebutton').attr('entry','disabled');
	if($('#windowcenter #txt #error_tips').size() == 0)
	{
		$('#windowcenter #txt').prepend('<div id="error_tips" class="alert alert-error">'+msg+'</div>');
	}
	else
	{
		$('#windowcenter #txt #error_tips').text(msg);
	}
}
function hide_error()
{
	$('#windowclosebutton').attr('entry','');
	$('#windowcenter #txt #error_tips').remove();
}

function set_pos()
{
	var windowHeight;
	var popHeight;
	windowHeight=$(window).height();
	popHeight=$(".window").outerHeight();

	if(windowHeight < popHeight + 30)
	{
		var bottom = windowHeight - ($(document).scrollTop() + popHeight);
		var position = 'absolute';
	}
	else
	{
		var bottom = '30px';
		var position = 'fixed';
	}
	$('#windowcenter').css({'bottom':bottom,'position':position});
}

function myconfirm(options,callback)
{
	var defaults = {
		'txt': '是否确认？',
		'confirm': '确认',
		'cancel': '取消'
	};
	if(typeof options == 'string')
	{
		var txt = options;
		var options = [];
		options.txt = txt;
	}
	options = $.extend(defaults,options);

	if($('#confirm-box').length == 0)
	{
		var html = '<div id="confirm-box" class="confirm-box">';
		html += '<div id="confirm-txt" class="confirm-box-txt">' + options.txt + '</div>';
		html += '<div class="confirm-box-btns">';
		html += '<a href="javascript:void(0);" data-value="confirm">' + options.confirm + '</a>';
		html += '<a href="javascript:void(0);" data-value="cancel">' + options.cancel + '</a>';
		html += '</div>';
		html += '</div>';
		html += '<div id="confirm-mash" class="confirm-mash"></div>';
		$('body').append(html);
	}
	else
	{
		$('#confirm-box #confirm-txt').html(options.txt);
		$('#confirm-box a[data-value="confirm"]').html(options.confirm);
		$('#confirm-box a[data-value="cancel"]').html(options.cancel);
	}

	$('#confirm-box .confirm-box-btns a').bind('click',function()
	{
		var value = $(this).data('value');
		$('#confirm-box').remove();
		$('#confirm-mash').remove();
		if(value == 'confirm' && typeof callback == 'function')
		{
			callback();
		}
		else
		{
			return false;
		}
	});
}