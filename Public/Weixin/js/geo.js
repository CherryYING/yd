var Geo = {
    city_id: 0,
    city: '',
    address: '',
	//浏览器定位
	geolocation: function(geoSuccess, geoError, geoOption,callback)
	{
		if (! ("geolocation" in navigator)) {
            return;
        }
        function sucCallback(pos)
        {
            hide_tips();
            // 设置cookie
            set_cookie('lat',pos.coords.latitude,600);
            set_cookie('lng',pos.coords.longitude,600);

            if(typeof geoSuccess == 'function')
            {
                geoSuccess({'lat':pos.coords.latitude,'lng':pos.coords.longitude},callback);
            }
            else
            {
                Geo.geocoding({'lat':pos.coords.latitude,'lng':pos.coords.longitude},callback);
            }
        }
        function errCallback(err)
        {
            if (typeof geoError == "function")
            {
                geoError(err);
            }
            else
            {
                Geo.geocoding({},callback);
            }
        }
        geoOption = geoOption || {
            enableHightAccuracy: true,
            timeout: 5000,
            maximumAge: 10000
        };
        show_tips('正在获取位置','',5000);
		navigator.geolocation.getCurrentPosition(sucCallback,errCallback,geoOption);
	},
    geocoding: function(point,callback)
    {
        $.getScript(site_url + '/js/api.js',function()
        {
            request_api('location',point,'','json',function(json)
            {
                if(json.error)
                {
                    show_tips(json.msg,'');
                }
                else
                {
                    Geo.city_id = json.data.id;
                    Geo.city = json.data.name;
                    Geo.address = json.data.address;
                    set_cookie('address',Geo.address,600);
                    if(typeof callback == 'function')
                    {
                        callback(Geo);
                    }
                    else
                    {
                        Geo.return_url(Geo);
                    }
                }
            });
        });
        return Geo;
    },
    return_url: function(obj)
    {
        var link = window.location.href;
        if(link.indexOf('choose_cityID') < 0 && obj.city_id > 0)
        {
            if(link.indexOf('?') < 0)
            {
                link += '?choose_cityID=' + obj.city_id;
            }
            else
            {
                link += '&choose_cityID=' + obj.city_id;
            }
            window.location.replace(link);
        }
    },
    init:function(geoSuccess, geoError, geoOption,callback)
    {
        var point = [];
        point.lat = get_cookie('lat');
        point.lng = get_cookie('lng');
        if(point.lat && point.lng)
        {
            Geo.geocoding(point,callback);
        }
        else
        {
            Geo.geolocation(geoSuccess, geoError, geoOption,callback);
        }
    }
};
function get_cookie(c_name)
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

function set_cookie(c_name,value,expiredays)
{
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" +escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()); //浣胯缃殑鏈夋晥鏃堕棿姝ｇ‘銆傚鍔爐oGMTString()
}