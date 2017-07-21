$(function(){
	
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
	$(".num").keyup(function(){
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

