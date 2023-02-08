$(document).ready( function(){
	$('.article_id').click(function(){
		var article_id = $(this).attr('article_id');

 		var title = $(this).parent("td").siblings(".title-sib").children(".title").html();
 		var description = $(this).parent("td").siblings(".description").children(".description-val").val();
 		$.ajax({
		    url:ajaxurl, // this is the object instantiated in wp_localize_script function
		    type: 'POST',
		    data:{ 
		      action: 'publishPost', // this is the function in your functions.php that will be triggered
		      title: title,
		      Content: description,
		      article_id: article_id
		    },
    		success: function( data ){
    			console.log(data);
    			location.reload();
     		}
  		});
 	
 	});
});


