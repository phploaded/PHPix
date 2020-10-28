<?php 

include('phpix-config.php');

if(file_exists('phpix-user-config.php')){include('phpix-user-config.php');}

include('phpix-admin-functions.php');
include('phpix-manage/header.php');

if($_SESSION['PHPix']!=''){ $phpix_user = 1; } 
elseif($_SESSION['phpixuser']!=''){ $phpix_user = $_SESSION['phpixuser']; } 
else{$phpix_user ='';}

if($phpix_user!='' || $_GET['nologin']=='1'){



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