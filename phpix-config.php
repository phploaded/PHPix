<?php 

session_start();
$notify = array();
error_reporting(E_ALL & ~E_NOTICE);

// for recaptcha v2 checkbox
$siteKey = "";
$secretKey = "";

date_default_timezone_set("Asia/Calcutta");
$domain = "http://localhost/phpix/";
$gallery_domain = $domain;
$admin_url = $domain."phpix-manage.php?page=";
$website_name = "PHPix";
$con = new mysqli("localhost","root","","phpix");
$prefix = "px_";
$manager_mail = "you@example.com";
$date_format = "l, d-M-Y, h:i a";

$xthumb_secret = "rt37yp";

$admin_key = "changeme";

$albumFILE = "phpix-album.php";

$default_gallery_settings = array(
	"thumb_width" => "200"	,
	"thumb_height" => "300"	,
	"thumb_dir" => "thumb"	,
	"image_dir" => "full"	,
	"temp_dir" => "temp"	,
);

if(!isset($_SESSION["gallery"]["thumb_width"])){
$_SESSION["gallery"] = $default_gallery_settings;
}


if (mysqli_connect_errno()){
echo "Failed to connect to MySQLi: " . mysqli_connect_error();
}
