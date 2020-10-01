<?php 

include('phpix-config.php');
include('phpix-admin-functions.php');
include('phpix-manage/header.php');

if($_SESSION[$website_name]!='' || $_GET['nologin']=='1'){



if($_GET['page']==''){
include('phpix-manage/index.php');
} else {
include('phpix-manage/'.$_GET['page'].'.php');	
}


} else {
include('phpix-manage/login.php');
}

include('phpix-manage/footer.php');


$con->close();


 ?>