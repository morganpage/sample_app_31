<?PHP
/*
Plugin Name: tumblr
Plugin URI: http://www.arcgate.com
Description: Plugin to manage your tumblr post from wordpress and to pushed post tumblr.
Version: 1.0
Author: imran khan
License: GPL
This software comes without any warranty, express or otherwise, and if it
breaks your blog or results in your cat being shaved, it's not my fault.
*/

include("tumblr_view.php");

add_action('admin_menu', 'tumblr_admin_menu');

//Hook the options page
function tumblr_admin_menu() {
    add_options_page('Tumblr settings', 'Tumblr settings','administrator',"manage_options",'tumblr_settings');
    add_options_page('Manage tumblr','Manage tumblr','administrator','manage_view','manageTumblrView');
}


function tumblr_settings(){
    $msg="";
    if(isset($_REQUEST['Submit'])){
	$valid = validation();
	if($valid["valid"]==true){
	    update_option("tumblr",serialize($_POST));    
	}
    }
    if($valid["valid"]==false){
	$msg= $valid['error'];
    }else if($valid["valid"]==true){
	$msg= $valid['msg'];
    }
    
    $tumblr = get_option("tumblr");
    if(trim($tumblr)!=''){
	$tumblr = unserialize($tumblr);
    }
    $tumblr_blog_email = isset($tumblr['tumbr_blog_email'])?$tumblr['tumbr_blog_email']:"";
    $tumblr_login_email = isset($tumblr['tumblr_login_email'])?$tumblr['tumblr_login_email']:"";
    $tumblr_login_pass = isset($tumblr['tumblr_login_pass'])?$tumblr['tumblr_login_pass']:"";
    $tumblr_blog_name = isset($tumblr['tumblr_blog_name'])?$tumblr['tumblr_blog_name']:"";
    
    if($msg!="")
    $msg = '<div class="updated fade" id="message" style="background-color:#fffbcc;"><p><strong>'.$msg.'</strong></p></div>';
    
    echo <<<END
    $msg
    <div class="wrap">
	<h2>Tumblr settings</h2>
	<form action="" method="post">
	    <table class="form-table">
		<tbody>
		    <tr valign="top">
			    <th scope="row">
				<label for="default_post_edit_rows">Post blog on http://www.tumblr.com</label>
			    </th>
			    <td>
				<input type="hidden" class="regular-text" value="tumblr" id="post_to" name="post_to" />
				<input type="text" class="regular-text" value="$tumblr_blog_email" id="blog_email" name="tumbr_blog_email" />
				<br />Enter email address provided by tumblr. <a href="http://www.tumblr.com/docs/en/email_publishing" target=_blank>@help?</a>
			    </td>
			    
		    </tr>
		    <tr valign="top">
			    <th scope="row">
				<label for="default_post_edit_rows">Tumblr blog name.</label>
			    </th>
			    <td>
				<input type="text" class="regular-text" value="$tumblr_blog_name" id="tumblr_blog_name" name="tumblr_blog_name" />
				<br />Enter tumblr blog name.
			    </td>
			
		    </tr>
		    <tr valign="top">
			    <th scope="row">
				<label for="default_post_edit_rows">Tumblr login email</label>
			    </th>
			    <td>
				<input type="text" class="regular-text" value="$tumblr_login_email" id="tumblr_login_email" name="tumblr_login_email" />
				<br />Enter email use to login with tumblr.
			    </td>
			
		    </tr>
		    <tr valign="top">
			    <th scope="row">
				<label for="default_post_edit_rows">Tumblr password</label>
			    </th>
			    <td>
				<input type="password" class="regular-text" value="$tumblr_login_pass" id="tumblr_login_pass" name="tumblr_login_pass" />
				
			    </td>
			
		    </tr>
		    <tr valign="top">
			<th scope="row">
			</th>
			<td>
			    <p>
				<input type="submit" value="Save Changes" class="button-primary" name="Submit">
			    </p>
			</td>
		    </tr>	    
		</tbody>
	    </table>
	</form>
    </div>
END;
}
//Vaildate the form submited
function validation(){
    $email = $_POST['tumbr_blog_email'];
    $login_email = $_POST['tumblr_login_email'];
    $password = $_POST['tumblr_login_pass'];
    if(is_email($email, $check_dns)){
	$success =true;
    }else{
	$msg = "Email for posting is not valid.";
	return array("valid"=>false,"error"=>$msg);
    }
    
    if($success && is_email($login_email, $check_dns)){
	$success = true;
    }else{
	$msg = "Email for login is not valid.";
	return array("valid"=>false,"error"=>$msg);
    }
    
    if($success && trim($password)!=""){
	$success = true;
    }else{
	$msg = "Enter password for tumblr.";
	return array("valid"=>false,"error"=>$msg);
    }
    $valid["valid"] = true;
    return $valid;
}

//post blog to tumblr
function postBlogTumblr($postID)
{
    $URLServer = "http://www.tumblr.com/api/write";
    $t_post = get_post($postID);
    $tumblr_data = unserialize(get_option("tumblr"));
    $postdata['email'] = $tumblr_data['tumblr_login_email'];
    $postdata['password'] = $tumblr_data['tumblr_login_pass'];
    $postdata['type'] = "regular";
    $postdata['title'] = $t_post->post_title;
    $postdata['body'] = $t_post->post_content;
    $postdata['state'] = "published";
    $postdata = http_build_query($postdata);
    
    $result = datapost($URLServer,$postdata);
    
}

add_action("publish_post","postBlogTumblr");
