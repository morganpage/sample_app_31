jQuery(document).ready(function() {
   
   var loading = function(){
	jQuery("#processing").html("Processing request...");
   };
   var pagging = function(){
	getData(jQuery(this).attr("href"),jQuery(this).attr("val"),jQuery(this).attr("id"));
	return false;
   };
   var delete_post = function(){
	var url = jQuery(this).attr("href");
	var type = jQuery(this).attr("class");
	var postID = jQuery(this).attr("id");
	//alert("Hello this is me");
	getOperation(url,type,postID,null);
	return false;
   }
   var unlike_reblog = function(){
	var url = jQuery(this).attr("href");
	var type = jQuery(this).attr("class");
	var postID = jQuery(this).attr("id");
	var reBlog = jQuery(this).attr("val");
	//alert(url+type+postID+reBlog);
	//return false;
	getOperation(url,type,postID,reBlog);
	return false;
   }
   var getData = function(url,type_get,page_no){
	jQuery.post(url,{beforeSend: loading,action:"add_act",type:type_get,page: page_no ,"cookie":encodeURIComponent(document.cookie)},
		    function(str_data){
			jQuery("#result").html(str_data).children("a.js_page").bind("click",pagging);
			jQuery("#result").children("div").children("a.unlike").bind("click",unlike_reblog);
			jQuery("#result").children("div").children("a.delete").bind("click",delete_post);
			jQuery("#result").children("div").children("a.reblog").bind("click",unlike_reblog);
			jQuery("#processing").html('');
			return false;
	});
    }
   
   getData(jQuery("a.read").attr("href"),"all",0);
   
   jQuery("a.read").click(function(){
	getData(jQuery(this).attr("href"),jQuery(this).attr("val"),0);
	return false;
    });
   
   //FOR OPERATION LIKE DELETE UNLIKE LIKE AND IMPORT
   var getOperation = function(url,type_get,postID,reBlog){
	jQuery.post(url,{action:"add_opt",type:type_get,post_id : postID,re_blog: reBlog ,"cookie":encodeURIComponent(document.cookie)},
		    function(result_data){
			
			if(result_data=='rebloged')
			{
			    jQuery("#processing").html("Post has been rebloged");
			    
			}
			else if(result_data!="notDone")
			{
			    jQuery("#processing").html(result_data);
			    jQuery("#result").children("div#div_"+postID).fadeOut();
			}
			else
			{
			    jQuery("#processing").html(result_data);
			}
			jQuery("#processing").delay(5000).html("");
			return false;
	});
   }
   
   
   
});
