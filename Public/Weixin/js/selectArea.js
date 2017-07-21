var AreaSelect = function()
{
	this.hideEmpty = true;
	this.areaList = areaList;
	this.allCity = allCity;
	this.cityList = cityList;
	this.zoneList = zoneList;
	this.streetList = streetList;

	this.selectArea = function(objid,id)
	{
		var selectobj = document.getElementById(objid);
		var len = this.areaList.length;
		if(len <= 0)
		{
			return;
		}
		for(var i=0;i<len;i++)
		{
			var addOption = new Option(this.areaList[i][1],this.areaList[i][0]);
			selectobj.options[i+1] = addOption;
			if(parseInt(id) == this.areaList[i][0])
			{
				selectobj.options[i+1].selected = true;
			}
		}
	}

	this.selectCity = function(objid,areaid,cityid)
	{
		var selectobj = document.getElementById(objid);
		selectobj.options[0].selected = true;
		var slen = selectobj.options.length - 1;
		if(slen > 0)
		{
			for(var i=slen;i>0;i--)
			{
				selectobj.options.remove(i);
			}
		}
		if(areaid)
		{
			var areaobj = document.getElementById(areaid);
			var aid = parseInt(areaobj.value);
			if(aid <= 0)
			{
				return;
			}
			var list = this.cityList[aid];
		}
		else
		{
			var list = this.allCity;
		}

		if(this.hideEmpty)
		{
			if(typeof list == 'undefined')
			{
				selectobj.style.display = 'none';
				return;
			}
			else
			{
				selectobj.style.display = '';
			}
		}
		else
		{
			if(typeof list == 'undefined')
			{
				return;
			}
		}

		var len = list.length;
		for(var i=0;i<len;i++)
		{
			var addOption = new Option(list[i][1],list[i][0]);
			selectobj.options[i+1] = addOption;
			if(parseInt(cityid) == list[i][0])
			{
				selectobj.options[i+1].selected = true;
			}
		}
	}

	this.selectZone = function(objid,cityid,zid)
	{
		var selectobj = document.getElementById(objid);
		var cityobj = document.getElementById(cityid);
		selectobj.options[0].selected = true;
		var slen = selectobj.options.length - 1;
		if(slen > 0)
		{
			for(var i=slen;i>0;i--)
			{
				selectobj.options.remove(i);
			}
		}
		var id = parseInt(cityobj.value);
		if(id <= 0)
		{
			return;
		}

		var list = this.zoneList[id];
		if(this.hideEmpty)
		{
			if(typeof list == 'undefined')
			{
				selectobj.style.display = 'none';
				return;
			}
			else
			{
				selectobj.style.display = '';
			}
		}
		else
		{
			if(typeof list == 'undefined')
			{
				return;
			}
		}

		var len = list.length;
		for(var i=0;i<len;i++)
		{
			var addOption = new Option(list[i][1],list[i][0]);
			selectobj.options[i+1] = addOption;
			if(parseInt(zid) == list[i][0])
			{
				selectobj.options[i+1].selected = true;
			}
		}
	}

	this.selectStreet = function(objid,zoneid,sid)
	{
		var selectobj = document.getElementById(objid);
		var zoneobj = document.getElementById(zoneid);
		selectobj.options[0].selected = true;
		var slen = selectobj.options.length - 1;
		if(slen > 0)
		{
			for(var i=slen;i>0;i--)
			{
				selectobj.options.remove(i);
			}
		}
		var id = parseInt(zoneobj.value);
		if(id <= 0)
		{
			return;
		}

		var list = this.streetList[id];
		if(this.hideEmpty)
		{
			if(typeof list == 'undefined')
			{
				selectobj.style.display = 'none';
				return;
			}
			else
			{
				selectobj.style.display = '';
			}
		}
		else
		{
			if(typeof list == 'undefined')
			{
				return;
			}
		}

		var len = list.length;
		for(var i=0;i<len;i++)
		{
			var addOption = new Option(list[i][1],list[i][0]);
			selectobj.options[i+1] = addOption;
			if(parseInt(sid) == list[i][0])
			{
				selectobj.options[i+1].selected = true;
			}
		}
	}
}