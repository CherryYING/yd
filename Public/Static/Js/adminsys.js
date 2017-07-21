function duanajax1(durl){
	$.ajax({
		url:durl,
		type:"GET",
		dataType:'json',
		timeout:1000,
		error:function(){
			alert('Error duan');
		},
		success:function(data){
			var dobj=$("#dmsg div");
			duanhtml(data.info,data.status,dobj);
			location.reload();
		}
	});
}

function duanhtml(data,status,dobj){
	if(status){
		dobj.html('操作成功：'+data).parent().css("display","").addClass("success").animate({opacity: 1.0}, 2000).fadeOut("slow",function(){ 
   			$(this).css("display","none"); 
		});
	}else{
		dobj.html('操作失败：'+data).parent().css("display","").addClass("error").animate({opacity: 1.0}, 2000).fadeOut("slow",function(){ 
   			$(this).css("display","none"); 
		});
	}
}
  
  
  