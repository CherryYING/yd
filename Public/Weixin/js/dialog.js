/**
 * Dialog
 *
 * @author    caixw <http://www.caixw.com>
 * @copyright Copyright (C) 2010, http://www.caixw.com
 * @license   FreeBSD license
 */


/**
 * jQuery的Dialog插件。
 *
 * @param object content
 * @param object options 选项。
 * @return
 */
function Dialog(content, options)
{
    var defaults = { // 默认值。
        title:'标题',       // 标题文本，若不想显示title请通过CSS设置其display为none
        showTitle:true,     // 是否显示标题栏。
        showFooter:false,     // 是否显示底部。
        submitText:'发表',       // 提交按钮文本，若不想显示title请通过CSS设置其display为none
        closeText:'x', // 关闭按钮文字，若不想显示关闭按钮请通过CSS设置其display为none
        draggable:false,     // 是否移动
        modal:true,         // 是否是模态对话框
        center:true,        // 是否居中。
        fixed:true,         // 是否跟随页面滚动。
        time:0,             // 自动关闭时间，为0表示不会自动关闭。
        id:false            // 对话框的id，若为false，则由系统自动产生一个唯一id。
    };
    var options = $.extend(defaults, options);
    options.id = options.id ? options.id : 'dialog-' + Dialog.__count; // 唯一ID
    var overlayId = options.id + '-overlay'; // 遮罩层ID
    var timeId = null;  // 自动关闭计时器
    var isShow = false;
    var isIe = $.browser.msie;
    var isIe6 = $.browser.msie && ('6.0' == $.browser.version);

    /* 对话框的布局及标题内容。*/
    var barHtml = !options.showTitle ? '' :
        '<div class="dialog-title"><a id="dialog-close" href="javascript:void(0);" class="dialog-close">' + options.closeText + '</a><span>' + options.title + '</span><a id="dialog-submit" href="javascript:void(0);" class="dialog-submit">' + options.submitText + '</a></div>';

    var footHtml = !options.showFooter ? '' :
        '<div class="dialog-footer"><input type="button" class="btn" id="dialog-cancle" value="取消" /><input type="button" class="btn btn-primary" id="dialog-submit" value="确定" /></div>';

    var dialog = $('<div id="' + options.id + '" class="dialog">'+barHtml+'<div class="dialog-content"></div>'+footHtml+'</div>').hide();
    $('body').append(dialog);


    /**
     * 重置对话框的位置。
     *
     * 主要是在需要居中的时候，每次加载完内容，都要重新定位
     *
     * @return void
     */
    var resetPos = function()
    {
        /* 是否需要居中定位，必需在已经知道了dialog元素大小的情况下，才能正确居中，也就是要先设置dialog的内容。 */
        if(options.center)
        {
            var left = ($(window).width() - dialog.width()) / 2;
            var top = ($(window).height() - dialog.height()) / 2;
            if(!isIe6 && options.fixed)
            {
                dialog.css({top:top,left:left});
            }
            else
            {
                dialog.css({top:top+$(document).scrollTop(),left:left+$(document).scrollLeft()});
                ;
            }
        }
    }

    /**
     * 初始化位置及一些事件函数。
     *
     * 其中的this表示Dialog对象而不是init函数。
     */
    var init = function()
    {
        /* 是否需要初始化背景遮罩层 */
        if(options.modal)
        {
            $('body').append('<div id="' + overlayId + '" class="dialog-overlay"></div>');
            $('#' + overlayId).css(
            {
                'left':0,
                'top':0,
                'width':'100%',
                'height':'100%',
                'z-index':++Dialog.__zindex,
                'position':'fixed'
            }).hide();
        }

        dialog.css({'z-index':++Dialog.__zindex, 'position':options.fixed ? 'fixed' : 'absolute'});

		/*  IE6 兼容fixed代码 */
        if(isIe6 && options.fixed)
        {
            dialog.css('position','absolute');
            resetPos();
            var top = parseInt(dialog.css('top')) - $(document).scrollTop();
            var left = parseInt(dialog.css('left')) - $(document).scrollLeft();
            $(window).scroll(function()
            {
                dialog.css({'top':$(document).scrollTop() + top,'left':$(document).scrollLeft() + left});
            });
        }

        /* 以下代码处理框体是否可以移动 */
        var mouse={x:0,y:0};
        function moveDialog(event)
        {
            var e = window.event || event;
            var top = parseInt(dialog.css('top')) + (e.clientY - mouse.y);
            var left = parseInt(dialog.css('left')) + (e.clientX - mouse.x);
            dialog.css({top:top,left:left});
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        };
        dialog.find('.dialog-title').mousedown(function(event)
        {
            if(!options.draggable)
            {
                return;
            }

            var e = window.event || event;
            mouse.x = e.clientX;
            mouse.y = e.clientY;
            $(document).bind('mousemove',moveDialog);
        });
        $(document).mouseup(function(event)
        {
            $(document).unbind('mousemove', moveDialog);
        });

        /* 绑定一些相关事件。 */
        dialog.find('#dialog-close').bind('click', this.close);
        dialog.find('#dialog-cancle').bind('click', this.close);
        dialog.bind('mousedown', function()
        {
            dialog.css('z-index', ++Dialog.__zindex);
        });

        // 自动关闭
        if(0 != options.time)
        {
            timeId = window.setTimeout(this.close, options.time);
        }
    }


    /**
     * 设置对话框的内容。
     *
     * @param string c 可以是HTML文本。
     * @return void
     */
    this.setContent = function(c)
    {
        var div = dialog.find('.dialog-content');
        if('object' == typeof(c))
        {
            switch(c.type.toLowerCase())
            {
                case 'id': // 将ID的内容复制过来，原来的还在。
                    div.html($('#' + c.value).html());
                    resetPos();
                break;
                case 'img':
                    div.html('加载中...');
                    $('<img alt="" />').load(function(){div.empty().append($(this));resetPos();}).attr('src',c.value);
                break;
                case 'url':
                    div.html('加载中...');
                    $.ajax(
                    {
                        url:c.value,
                        success:function(html)
                        {
                            div.html(html);
                            resetPos();
                        },
                        error:function(xml,textStatus,error)
                        {
                            div.html('出错啦');
                        }
                    });
                break;
                case 'iframe':
                    if(typeof c.width == 'undefined' || c.width == null)
                    {
                        c.width = 700;
                    }
                    if(typeof c.height == 'undefined' || c.height == null)
                    {
                        c.height = 350;
                    }

                    div.append($('<iframe src="' + c.value + '" frameborder="0" scrolling="no" style="width:'+c.width+'px;height:'+c.height+'px" />'));
                    resetPos();
                break;
                case 'text':
                default:
                    div.html(c.value);
                    resetPos();
                break;
            }
        }
        else
        {
            div.html(c);
            resetPos();
        }
    }

    /**
     * 显示对话框
     */
    this.show = function()
    {
        if(undefined != options.beforeShow && !options.beforeShow())
        {
            return;
        }

        /**
         * 获得某一元素的透明度。IE从滤境中获得。
         *
         * @return float
         */
        var getOpacity = function(id)
        {
            if(!isIe)
            {
                return $('#' + id).css('opacity');
            }

            var el = document.getElementById(id);
            return (undefined != el
                    && undefined != el.filters
                    && undefined != el.filters.alpha
                    && undefined != el.filters.alpha.opacity)
                ? el.filters.alpha.opacity / 100 : 1;
        }

        /* 是否显示背景遮罩层 */
        if(options.modal)
        {
            $('#' + overlayId).fadeTo('slow', getOpacity(overlayId));
        }
        dialog.fadeTo('slow', getOpacity(options.id), function()
        {
            if(undefined != options.afterShow)
            {
                options.afterShow();
            }
            isShow = true;
        });

        // 自动关闭
        if(0 != options.time)
        {
            timeId = window.setTimeout(this.close, options.time);
        }
        resetPos();
    }
    window.onresize = function()
    {
        resetPos();
    }

    /**
     * 隐藏对话框。但并不取消窗口内容。
     */
    this.hide = function()
    {
        if(!isShow)
        {
            return;
        }

        if(undefined != options.beforeHide && !options.beforeHide())
        {
            return;
        }

        dialog.fadeOut('slow',function()
        {
            if(undefined != options.afterHide)
            {
                options.afterHide();
            }
        });
        if(options.modal)
        {
            $('#' + overlayId).fadeOut('slow');
        }
        isShow = false;
    }

    /**
     * 关闭对话框
     *
     * @return void
     */
    this.close = function()
    {
        if(undefined != options.beforeClose && !options.beforeClose())
        {
            return;
        }
        dialog.css('top','-100%');
        dialog.fadeOut('slow', function()
        {
            $(this).remove();
            isShow = false;
            if(undefined != options.afterClose)
            {
                options.afterClose();
            }
        });
        if(options.modal)
        {
            $('#'+overlayId).fadeOut('slow', function()
            {
                $(this).remove();
            });
        }
        window.clearTimeout(timeId);
    }

    init.call(this);
    this.setContent(content);

    Dialog.__count++;
    Dialog.__zindex++;
}
Dialog.__zindex = 500;
Dialog.__count = 1;

function dialog(content, options)
{
	var dlg = new Dialog(content, options);
	dlg.show();
	return dlg;
}

