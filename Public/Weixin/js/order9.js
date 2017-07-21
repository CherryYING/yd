/*********确认订单*********/
var timer = null;
var Order = {
    comid : 0,
    goods_number : 1,
    goods_amount : 0,

    //显示收货地址
    showAddress: function () {
        var h = $('#addr_template').html();
        $('#item_addr').html(h);
        if($('#addr_0').hasClass('curr'))
        {
            Order.editAddress(0);
        }
        if(typeof _select == 'function')
        {
            _select();
        }
    },
    showDeliverys: function ()
    {
        var order_contact = GetCookie('order_contact');
        var order_contact_mobile = GetCookie('order_contact_mobile');
        if(!order_contact)
        {
            Order.editContact();
        }
        var h = $('#contact_template').html();
        h += $('#delivery_template').html();
        $('#item_addr').html(h);
    },
    //编辑联系信息
    editContact: function()
    {
        var order_contact = GetCookie('order_contact');
        var order_contact_mobile = GetCookie('order_contact_mobile');
        var order_contact_email = GetCookie('order_contact_email');
        var contact_dialog = dialog({'type':'id','value':'contactTemplate'},{'id':'contact-dialog','title':'联系信息','submitText':'确认','closeText':'取消','center':false})

        $('#contact-dialog #contact_form input[name=contact]').val(order_contact);
        $('#contact-dialog #contact_form input[name=contact_mobile]').val(order_contact_mobile);
        $('#contact-dialog #contact_form input[name=contact_email]').val(order_contact_email);

        $('#contact-dialog #dialog-submit').unbind().bind('click',function()
        {
            var contact = $('#contact-dialog #contact_form input[name=contact]').val();
            var mobile = $('#contact-dialog #contact_form input[name=contact_mobile]').val();
            var email = $('#contact-dialog #contact_form input[name=contact_email]').val();
            if(!contact)
            {
                alert('请填写联系人姓名');
                return false;
            }
            else if(!mobile)
            {
                alert('请填写联系人电话');
                return false;
            }
            else if(!/^1[3|4|5|7|8][0-9]{9}$/.test(mobile) && !/^(([4|8]00)-(\d{3})-(\d{4}))|(\d{3,4}\-[\d]{7,8}(\-(\d{1,4})){0,})$/.test(mobile))
            {
                alert('请填写正确的电话号码');
                return false;
            }
            else if(!email && !/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i.test(email))
            {
                alert('请填写正确的邮箱');
                return false;
            }
            else
            {
                $('#contact li [name=contact]').text(contact);
                $('#contact li [name=mobile]').text(mobile);
                $('#contact li [name=email]').text(email);
                SetCookie('order_contact',contact,1);
                SetCookie('order_contact_mobile',mobile,1);
                SetCookie('order_contact_email',email,1);
                contact_dialog.close();
            }
        });
    },
    //编辑地址
    editAddress:function(id)
    {
        var obj = $('#addr_' + id);
        if(obj.hasClass('edit')) return ;
        $('#address li .edit').attr('class','');
        obj.attr('class','curr edit');
        if(typeof areaselect != 'undefined')
        {
            var province = $('#addr_form_' + id).data('province');
            var city = $('#addr_form_' + id).data('city');
            var district = $('#addr_form_' + id).data('district');
            areaselect.selectArea('province_' + id,province);
            areaselect.selectCity('city_' + id,'province_' + id,city);
            areaselect.selectZone('zone_' + id,'city_' + id,district);
        }
        $("#cart-address-btn").fadeOut();
    },
    hideEditAddress:function(id)
    {
        var obj = $('#addr_' + id);
        if(!obj.hasClass('edit')) return ;
        obj.removeClass('edit');
        if(id == 0)
        {
            obj.removeClass('curr');
        }
        $("#cart-address-btn").fadeIn();
    },
    saveAddress:function(id)
    {
        var param = {
            'addressid': id,
            'is_real':Order.is_real,
            'consignee': $(' #addr_form_'+id+' input[name=txtName]').val(),
            'mobile': $(' #addr_form_'+id+' input[name=txtMobile]').val(),
            'email': $(' #addr_form_'+id+' input[name=txtemail]').val(),
            'address': $(' #addr_form_'+id+' [name=txtAddress]').val(),
            'province': $(' #addr_form_'+id+' select[name=selProvince]').val(),
            'city': $(' #addr_form_'+id+' select[name=selCity]').val(),
            'district': $(' #addr_form_'+id+' select[name=selRegion]').val(),
            'access_id': access_id,
            'access_token': encodeURIComponent(access_token)
        };
        $.ajax(
        {
            url: home_url + '/api.php?act=save_consignee',
            data: param,
            type: 'POST',
            dataType: 'json',
            beforeSend:function()
            {
                if(!param.consignee)
                {
                    alert('收货人不能为空');
                    return false;
                }
                else if(!param.mobile)
                {
                    alert('收货人手机不能为空');
                    return false;
                }
                else if(!/^1[3|4|5|7|8]\d{9}$/.test(param.mobile))
                {
                    alert('收货人手机不正确');
                    return false;
                }
                if(parseInt(Order.is_real) == 1)
                {
                    if(parseInt(param.province) <= 0)
                    {
                        alert('请选择省份');
                        return false;
                    }
                    if(parseInt(param.city) <= 0)
                    {
                        alert('请选择城市');
                        return false;
                    }
                    else if(!param.address)
                    {
                        alert('请填写收货地址');
                        return false;
                    }
                }
            },
            success:function(json)
            {
                hide_tips();
                if(json.error == 0)
                {
                    Order.hideEditAddress(id);
                    if(id > 0)
                    {
                        if(parseInt(Order.is_real) == 1)
                        {
                            $(' #addr_info_' + id + ' [name=address]').text(json.data.fullarea + param.address);
                        }
                        else
                        {
                            $(' #addr_info_' + id + ' [name=address]').text('邮箱：' + param.email);
                        }
                        $(' #addr_contact_' + id + ' [name=consignee]').text(param.consignee);
                        $(' #addr_contact_' + id + ' [name=tel]').text(param.mobile);
                    }
                    else
                    {
                         json.data.is_real = Order.is_real;
                        var template = $('#add_newaddr_template').html();
                        var h = $.template(template,{data: json.data});
                        $('#item_addr ul li div').removeClass('curr');
                        $('#item_addr ul').prepend(h);
                    }
                    $("#cart-address-btn").fadeIn();
                }
                else
                {
                    show_tips(json.msg,'warn');
                    window.setTimeout(function()
                    {
                        hide_tips();
                    },2000);
                    if(param.addressid > 0)
                    {
                        Order.editAddress(param.addressid);
                    }
                }
            }
        });
    },
    //表单提交验证
    saveCheck: function () {
        //支付方式
        var payid = parseInt($("#payment div.curr").attr('payid'));
        if(payid == 0)
        {
            show_tips('请选择支付方式','warn');
            return false;
        }
        else
        {
            $("#payid").val(payid);
        }
        //配送地址
        // var curraddressid = parseInt(GetCookie('curraddressid'));
        // if(curraddressid == 0)
        // {
        //     show_tips('请选择配送地址','warn');
        //     return false;
        // }
        // else
        // {
        //     $("#curraddressid").val(curraddressid);
        // }
        //配送方式
        has_shipping = 1;
        $(".cart-shipping select[entry='jq']").each(function()
        {
            if(parseInt($(this).val()) > 0)
            {

            }
            else
            {
                s_top = $(this).parents('.cart-shipping').position().top;
                has_shipping = 0;
                return false;
            }
        })
        // if(has_shipping == 0)
        // {
        //     $('body,html').animate({'scrollTop':s_top},500);
        //     show_tips('请选择配送方式','warn');
        //     return false;
        // }
        //九鼎币
        var jd_money = $('#checkout-box #jd_money').val() * 1;
        $("#jd_money_val").val(jd_money);

        return true;
    },
    //选择配送方式
    delChange: function (obj) {
        var shippingid = $(obj).val();
        var shipfee = $(obj).find('option[value=' + shippingid + ']').attr('data-fee') * 1;
        var ship_code = $(obj).find('option[value=' + shippingid + ']').attr('data-code');

        var uid = $(obj).attr('data-uid');
        var totalmoney = $("#shoptotalmoney_" + uid).attr('data-money') * 1;
        var order_amount = shipfee + totalmoney;
        order_amount = order_amount.toFixed(2);
        $("#shoptotalmoney_" + uid).text('￥' + order_amount);
        totalshipfee = 0;
        $(".cart-shipping .jq_select_list li.selected").each(function()
        {
            totalshipfee += $(this).attr('data-fee') * 1;
        });
        //总快递费
        // totalshipfee = parseFloat(totalshipfee, 2);
        $("#shippfee_amount").text(totalshipfee);

        //订单费用
        totalmoney = $("#order_amount").attr('data-money') * 1;

        total_orderamount = totalmoney + totalshipfee;
        $("#order_amount").text(total_orderamount.toFixed(2));
    },
    //选择支付方式
    payChange: function (obj) {
        var p = obj.parents('ul');
        if (p.size() > 0)
        {
            p.find('div.curr').removeClass('curr');
            obj.addClass('curr');
        }
    },
    //选择地址
    addressChange: function (obj)
    {
        if(obj.hasClass('curr')) return ;
        obj.parents('li').removeClass('hide');
        $('#address>li>div').attr('class','');
        obj.addClass('curr');
    },
    //选择提货点
    deliveryChange: function (obj)
    {
        if(obj.hasClass('curr')) return;
        $('#delivery>li>div').removeClass('curr');
        obj.addClass('curr');
    },
    //九鼎币兑换
    moneyPay: function()
    {
        var jd_money = $('#jd_money').val() * 1;
        var max = $('#jd_money').attr('max');
        if(jd_money < 0)
        {
            $('#jd_money').val(0);
        }
        if(jd_money > max)
        {
            $('#jd_money').val(max);
        }
        window.clearTimeout(timer);
        timer = window.setTimeout(function()
        {
            Order.changeAmount();
        },500);
    },
    changeAmount: function()
    {
        var shipping_fee = $("#shippfee_amount").text() * 1;
        var jd_money = $('#jd_money').val() * 1;
        var max = $('#jd_money').attr('max') * 1;
        var money = 0;

        var coupon_amount = 0;
        $('[entry="coupon_money"]').each(function()
        {
            coupon_amount += $(this).val() * 1;
        });
        if(jd_money > max)
        {
            // Order.toDecimal(max/100,2);
        }
        else if(jd_money > 0)
        {
            money = Order.toDecimal(jd_money/100,2);
            if(Order.goods_amount * 1 + shipping_fee - coupon_amount < money)
            {
                money = Order.toDecimal(Order.goods_amount * 1 + shipping_fee - coupon_amount,2);
                jd_money = Math.floor(money * 100);
                $('#jd_money').val(jd_money);
            }
        }
        else
        {
            money = 0;
        }
        if($('#checkout-fee [entry=jd_money]').size() == 0)
        {
            $('#checkout-fee').append('<li entry="jd_money"><p>九鼎币：-<i class="icon-cny"></i><span class="mbprice">'+money+'</span></p></li>');
        }
        else
        {
            $('#checkout-fee [entry=jd_money] .mbprice').text(money);
        }
        var total = Order.toDecimal((Order.goods_amount * 1 + shipping_fee - money - coupon_amount),2);
        $('#order_amount').html(total);
    },
    //重新计算运费
    refShippingfee: function(param)
    {
        param.access_id = access_id;
        param.access_token = encodeURIComponent(access_token);
        $.ajax(
        {
            url: home_url + '/ajax.php?act=change_address_shippingmult',
            data: param,
            type: 'POST',
            dataType: 'json',
            beforeSend:function()
            {
                show_tips('数据处理中','load');
            },
            success:function(json)
            {
                hide_tips();
                if(json.error == 0)
                {


                }
                else
                {

                }
            }
        });
    },

    //加载
    load: function ()
    {
        Order.showAddress();
        //结算
        $('#btnEnter').click(function ()
        {
            if(!$(this).attr('disabled'))
            {
                $(this).attr('disabled',true);
                window.setTimeout(function()
                {
                    $('#btnEnter').attr('disabled',false)
                },3000);
                if(Order.saveCheck())
                {
                    document.forms["form1"].submit(); //确认订单
                }
            }
        });
    },
    toDecimal: function(E, A)
    {
        var D = parseInt(E * Math.pow(10, A) + 0.5) / Math.pow(10, A);
        if (isNaN(D)) {
            return 0;
        }
        var D = Math.round(E * Math.pow(10,A)) / Math.pow(10,A);
        var C = D.toString();
        var B = C.indexOf(".");
        if (B < 0) {
            B = C.length;
            C += ".";
        }
        while (C.length <= B + A) {
            C += "0";
        }
        return C;
    }
}

function GetCookie(c_name)
{
    if (document.cookie.length > 0)
    {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1)
        {
            c_start = c_start + c_name.length + 1;
            c_end   = document.cookie.indexOf(";",c_start);
            if (c_end == -1)
            {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start,c_end));
        }
    }
    return null
}

function SetCookie(c_name,value,expiredays)
{
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" +escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()) + ";path=/"; //浣胯缃殑鏈夋晥鏃堕棿姝ｇ‘銆傚鍔爐oGMTString()
}