$(function(){
	/*
		地区联动
	*/
	$.post("index.php?g=Admin&m=Ajax&a=province",'',function(json){
		var html='<option>-请选择-</option>';
		$.each(json,function(i,obj){
			html+='<option value='+obj.id+'>'+obj.region_name+'</option>';
		});
		$("#province").html(html);
	},'json');
	
	$("#province").change(function(){
		$.post("index.php?g=Admin&m=Ajax&a=city",{'parent_id':$(this).val()},function(json){
			var html='<option>-请选择-</option>';
			$.each(json,function(i,obj){
				html+='<option value='+obj.id+'>'+obj.region_name+'</option>';
			});
			$("#city").html(html);
		},'json');
	});
	
	$("#city").change(function(){
		$.post("index.php?g=Admin&m=Ajax&a=district",{'parent_id':$(this).val()},function(json){
			var html='<option>-请选择-</option>';
			$.each(json,function(i,obj){
				html+='<option value='+obj.id+'>'+obj.region_name+'</option>';
			});
			$("#district").html(html);
		},'json');
	});
	
	
	
	
	/*
		当前登录管理员
	*/
	$('#user-curr').bind('click',function(){
		$(".user-info").slideToggle('fast');
	});
	$('#user-curr').bind('mouseover',function(){
		$(".user-info").slideDown('fast');
	});
	
	/*
		只能输入数字
	*/
	$(".num,.number").keyup(function(){
		if(isNaN($(this).val())){
			$(this).val('');
		}
	});
	
	/*
		操作前确认
	*/
	$(".confirm").click(function(){
		if(!confirm('确认操作？')){
			return false;	
		}
	});

});

function alert(data){
	artDialog({content:data, style:'alert', lock:false}, function(){});	
}

/*
	 正则校验银行卡
*/
var bank_card = function(content) {  
	/*var regex = /^(998801|998802|622202|622525|622526|435744|435745|483536|528020|526855|622156|622155|356869|531659|622157|627066|627067|627068|627069)\d{10}$/;  */
	var regex=/^\d{19}$|^\d{16}$/;
	if (regex.test(content)) {  
		return true;  
	}else{
		return false;  
	}  
}  

var mobile=function(content){
	//var regex = /^13[0-9]{9}$|^15[0-9]{9}$|^18[0-9]{9}$/;  
	var regex = /^(13[0-9]|15[012356789]|17[012356789]|18[0236789]|14[57])[0-9]{8}$/;
	if (regex.test(content)) {  
		return true;  
	}else{
		return false;  
	}  

}
