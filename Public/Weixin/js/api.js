function request_api(action,data,method,datatype,callback)
{
	if(!action) return;
	if(!method)
	{
		method = 'GET';
	}

	var req = [];
	req.url = site_url + '/api.php?act=' + action;
	req.data = data,
	req.type = method;
	if(datatype)
	{
		req.dataType = datatype;
	}
	if(callback)
	{
		req.success = callback;
	}

	$.ajax(req);
}