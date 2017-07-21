// JavaScript Document


	$(function(){
		
	
			function alt1(counts,pres) 
       {
	       for (j=1;j<=counts;j++)
	           { if(j==pres)
			     {$("#product"+j).show();
				  
				  $("#trailer"+j).css("background"," #16B6AC");
				 }
			else
			{ 
			$("#product"+j).hide();
			
	        $("#trailer"+j).css("background","#E22B41");
			        
			}
				}
					}
			
			
			$("#trailer1").mouseenter(function(){
				 alt1(2,1)
				})
			
			$("#trailer2").mouseenter(function(){
				 alt1(2,2)
				})
		
				
		
			
		
	  
})
