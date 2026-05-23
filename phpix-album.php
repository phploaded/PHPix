<?php 

include('phpix-config.php');
include('phpix-front-functions.php');
$admin_session = isset($_SESSION['PHPix']) ? $_SESSION['PHPix'] : '';
$user_session = isset($_SESSION['phpixuser']) ? $_SESSION['phpixuser'] : '';
if($admin_session!=''){ $phpix_user = 1; } 
elseif($user_session!=''){ $phpix_user = $user_session; } 
else{$phpix_user ='';}
?><!DOCTYPE html>
<html>
<head>
<?php include('phpix-scripts.php'); ?>
<title>PHPix Photo Gallery</title>
</head>
<body>
<div id="flscrn">
<div id="album-sidebar">
<ul id="album-sidebar-menu">
<li onclick="album_gallery_main()">Gallery - Show albums</li>
<li onclick="album_search_mode();album_toggle_sidebar();">Search - Find albums &amp; photos</li>
<li onclick="album_nav_keys()">Keyboard Navigation Buttons</li>
<li onclick="album_notes();album_toggle_sidebar();">Public news and notebook</li>
<li onclick="gal_settings()">User Interface Settings</li>
<?php 
if(!isset($_SESSION['phpixuser'])){
echo'<li><a target="_blank" href="'.$admin_url.'index">Administration Area</a></li>';
}

if(!isset($_SESSION['PHPix'])){
if(!isset($_SESSION['phpixuser'])){
echo'<li onclick="album_get_pwd()">Retrieve your password</li>
<li onclick="album_login()">User Login</li>';
} else {
	
echo'<li><a href="'.$admin_url.'albums">Manage your albums</a></li>
<li onclick="album_change_pwd()">Change your password</li>
<li onclick="album_logout()">User Logout</li>';
} 
}

?>
<li onclick="album_about()">About PHPix Gallery</li>
</ul>
</div>

<div id="flscrn-inner">
<div id="album-list-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>Public albums</span>
<ul class="album-buttons">
<li class="albtn-deepshow gal-play"></li>
<li onclick="toggleFullscreen('#flscrn')" class="albtn-fullscreen"></li>
</ul>
</div>
</div>


<?php 
gal_display_albums();
 ?>

</div>


<div id="album-pics-block"></div>



<div id="album-notes-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a href="javascript:void(0)" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>Notes</span>
<ul class="album-buttons">
<li onclick="toggleFullscreen('#flscrn')" class="albtn-fullscreen"></li>
<li onclick="album_closePhotos()" class="albtn-back"></li>
</ul>
</div>
</div>

<div class="album-notes-ctr">
<div class="album-pages"></div>
<?php 

$count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `".$prefix."content` WHERE `type`='note' AND `status`='Enabled'"));

$spot_data = mysqli_query($con, "SELECT `title`, `sort` FROM `".$prefix."spots` ORDER BY `sort` DESC");
$spots = array();
$spotsSort = array();
while($row = mysqli_fetch_assoc($spot_data)){
$spots[] = $row['title'];
$spotsSort[] = $row['sort'];
}

if(!isset($_GET['pagenumber'])){$_GET['pagenumber']='';} 

if($_GET['pagenumber']==''){
$cpage = 1;
} else {
$cpage = $_GET['pagenumber'];
}

echo'<script>
$(function() {
    $(".album-pages").pagination({
        items: '.$count['total'].',
        itemsOnPage: gal_vars_notes_ipp,
        cssStyle: \'light-theme\',
		currentPage: '.$cpage.',
		hrefTextPrefix: "'.$albumFILE.'?tab=notes&ipp="+gal_vars_notes_ipp+"&pagenumber=",
		hrefTextSuffix: "",
		onPageClick: function(pgno, e){e.preventDefault();get_album_notes(pgno);}
    });
});

var spotsArray = ' . json_encode($spots) . ';
var spotsSort = ' . json_encode($spotsSort) . ';
</script>';

 ?>
<div class="album-notes"></div>
</div>

</div>


<div id="album-search-block">
<div class="album-bar-ctr">
<div class="album-bar">
<a href="javascript:void(0)" onclick="album_toggle_sidebar()" class="albtn-menu"></a>
<span>Search</span>
<ul class="album-buttons">
<li onclick="toggleFullscreen('#flscrn')" class="albtn-fullscreen"></li>
<li onclick="album_closeSearch()" class="albtn-back"></li>
</ul>
</div>
</div>

<div class="album-search">
<div id="album-nosearch-ui"></div>
<div id="gal-ctr-out"></div>
</div>
</div>


</div></div>



</body>
</html>
