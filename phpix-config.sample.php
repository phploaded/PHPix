<?php 

session_start();
$notify = array();
error_reporting(E_ALL & ~E_NOTICE);

// for recaptcha v2 checkbox
$siteKey = "6LceCMYZAAAAAKgWSld_bbCLcsGAWLEHL0Iy224e";
$secretKey = "6LceCMYZAAAAADU0hOBVi8joNUH0Kl7r3pXM2eBh";

date_default_timezone_set("Asia/Calcutta");
$domain = "http://localhost/familydb/";
$gallery_domain = $domain;
$admin_url = $domain."phpix-manage.php?page=";
$website_name = "PHPix";
$con = new mysqli("localhost","root","","phpix");
$prefix = "phpix_";
$manager_mail = "admin@shopfblikes.com";
$date_format = "l, d-M-Y, h:i a";

$xthumb_secret = "rt37yp";

$admin_key = "renuthewife";

$albumFILE = "phpix-album.php";

$default_gallery_settings = array(
	"thumb_width" => "200"	,
	"thumb_height" => "150"	,
	"thumb_dir" => "thumb"	,
	"image_dir" => "full"	,
	"temp_dir" => "temp"	,
);

if($_SESSION["gallery"]["thumb_width"]==""){
$_SESSION["gallery"] = $default_gallery_settings;
}


if (mysqli_connect_errno()){
echo "Failed to connect to MySQLi: " . mysqli_connect_error();
}