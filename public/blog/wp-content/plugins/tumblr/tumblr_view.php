<?PHP
//read tumblr settings
$aut_data = unserialize(get_option("tumblr"));
define(THUMBLR_PATH,plugins_url('/tumblr/'));
define(LOGIN_EMAIL,$aut_data['tumblr_login_email']);
define(LOGIN_PASS,$aut_data['tumblr_login_pass']);

//add javascript file to header
add_action('admin_head', 'addJs');
function addJs(){
    echo "<script type='text/javascript' src='".THUMBLR_PATH."js/jquery.tumblr.js'></script>";
}

//Add top menu
function manageTumblrView(){
    $path = get_option('siteurl').'/wp-admin/admin-ajax.php';
    
    echo <<<END
    <ul class="subsubsub">
	<li>
	    <a href="{$path}" val="all"  class="read">My post </a>|
	</li>
	<li>
	    <a href="{$path}" val="like" class="read">Like</a>
	</li>
    </ul><br clear=all>
    <div id="processing" class="update-nag"></div>
    <div id="result">
    </div>
END;
}

//handel ajax calls
add_action('wp_ajax_add_act', 'query_tumblr');
add_action('wp_ajax_add_opt', 'operation_tumblr');

/*Manage curl request
 *
 *@param string $URLServer, string $postdata   
 *return string
*/
function datapost($URLServer,$postdata)
{
   $agent = "Mozilla/5.0";
   $cURL_Session = curl_init();
   curl_setopt($cURL_Session, CURLOPT_URL,$URLServer);
   curl_setopt($cURL_Session, CURLOPT_USERAGENT, $agent);
   curl_setopt($cURL_Session, CURLOPT_POST, 1);
   curl_setopt($cURL_Session, CURLOPT_POSTFIELDS,$postdata);
   curl_setopt($cURL_Session, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($cURL_Session, CURLOPT_FOLLOWLOCATION, 1);
   $result = curl_exec ($cURL_Session);
   return $result;
}

/*Make call to tumblr for unlike, delete and reblog operation
 *
 *@param $_POST 
 *return string
*/
function operation_tumblr(){
    $tumblr_data = unserialize(get_option("tumblr"));
    $postdata['email'] = $tumblr_data['tumblr_login_email'];
    $postdata['password'] = $tumblr_data['tumblr_login_pass'];
    if($_POST['type']=="unlike")
    {
	$URLServer = "http://www.tumblr.com/api/unlike";
	$postdata['post-id'] = $_POST['post_id'];
	$postdata['reblog-key'] = $_POST['re_blog'];
	$postdata['type'] = $_POST['type'];
    }elseif($_POST['type']=="delete"){
	$URLServer = "http://www.tumblr.com/api/delete";
	$postdata['post-id'] = $_POST['post_id'];
    }elseif($_POST['type']=="reblog"){
	$URLServer = "http://www.tumblr.com/api/reblog";
	$postdata['post-id'] = $_POST['post_id'];
	$postdata['reblog-key'] = $_POST['re_blog'];
	$postdata['type'] = $_POST['type'];
    }
    
    
    $postdata = http_build_query($postdata);
    $result = datapost($URLServer,$postdata);
    if($result=="Deleted"){
	echo $result;
    }elseif(strstr($result,"Unliked")!==false){
	echo trim(str_replace(array("Unliked post ","."),'',$result));
    }else{
	if($_POST['type']=="reblog")
	echo "rebloged";
	else
	echo "notDone";
    }
    
    
    exit;
}

/*Make call to tumblr for get post of all and like type
 *
 *@param $_POST
 *return string
*/
function query_tumblr()
{
    $tumblr_data = unserialize(get_option("tumblr"));
    $postdata['email'] = $tumblr_data['tumblr_login_email'];
    $postdata['password'] = $tumblr_data['tumblr_login_pass'];

    if($_POST['type']=="all"){
	$URLServer = "http://".$tumblr_data['tumblr_blog_name'].".tumblr.com/api/read";
	$show_per_page = 10;
	$postdata['num'] = $show_per_page;
	$current_page = 0;
	$start=0;
	if(isset($_POST['page'])){
	    $current_page = $_POST['page'];
	    $start = ($current_page*$show_per_page)+1;
	}
	$postdata['start'] = $start;
    }elseif($_POST['type']=="like"){
	$URLServer = "http://www.tumblr.com/api/likes";
	$postdata['like'] = 1;
	$show_per_page = 1000;
    }
    
    $postdata = http_build_query($postdata);
    $post_xml = datapost($URLServer,$postdata);
    $post_xml = str_replace(array("conversation-","regular-","quote-","photo-","reblog-"),'',$post_xml);
    $post_xml = @simplexml_load_string($post_xml);
    
    //Handeling paggination
    
    echo process_view($post_xml,$_POST['type']);
    $path = get_option('siteurl').'/wp-admin/admin-ajax.php';
    $total_pages = ceil($post_xml->posts->attributes()->total/$show_per_page);
    for($p=0;$p<$total_pages;$p++)
    {
	if($p==$current_page)
	echo ($p+1)." ";
	else
	echo "<a href='".$path."' id='".$p."' val='".$_POST['type']."' class='js_page'>".($p+1)."</a>";
    }
    exit;
}
/*Parse the xml to generate the view 
 *
 *@param string $post_xml, string $type_dis
 *return string
*/
function process_view($post_xml,$type_dis){
    $path = get_option('siteurl').'/wp-admin/admin-ajax.php';
    $out = "";
    foreach($post_xml->posts->post as $post)
    {
	if(isset($post->attributes()->private)){
	    $flag = "Private";
	}else{
	    $flag = "Public";
	}
	//echo $post->attributes()->id;
	$out .= "<div id='div_".$post->attributes()->id."'>";
	//$out .= $post->attributes()->type;
	if($post->attributes()->type=="regular")
	$out .= "<h2>".$post->title."</h2>";
	elseif($post->attributes()->type=="conversation")
	$out .= "<h3>".$post->title."</h3>";
	elseif($post->attributes()->type=="quote")
	$out .= "<h3>".$post->text."</h3>";
	elseif($post->attributes()->type=="photo"){
	    $out .= "<h3>".$post->caption."</h3>";
	    $img_url = $post->url[3][0];
	    $out .= "<img src=$img_url><br>";
	}
	$out .= $flag;
	if($type_dis=="like")
	$out .= " <a href='".$path."' id=".$post->attributes()->id." val=".$post->attributes()->key."  class='unlike'>Unlike</a>";
	else
	$out .= " <a href='".$path."' id=".$post->attributes()->id." class='delete'>Delete</a>";
	$out .= " | <a href='".$path."' id=".$post->attributes()->id." val=".$post->attributes()->key."  class='reblog'>Reblog</a>";
	$out .= "<hr><br></div>";
    }
    return $out;
}