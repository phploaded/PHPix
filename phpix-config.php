<?php 

session_start();
$notify = array();
error_reporting(E_ALL & ~E_NOTICE);

// for recaptcha v2 checkbox
$siteKey = "6Lem7a0mAAAAANFnf09uwVI7ut513x4xG8zP_teW";
$secretKey = "6Lem7a0mAAAAAPh2WqCsJmIqGr7zLwmh-NFFUXnW";

date_default_timezone_set("Asia/Calcutta");
$domain = "http://localhost/mlib3/";
$gallery_domain = $domain;
$admin_url = $domain."phpix-manage.php?page=";
$website_name = "PHPix";
$con = new mysqli("localhost","root","","mlib3");
$prefix = "mlib";
$manager_mail = "sakush100@gmail.com";
$date_format = "l, d-M-Y, h:i a";

$xthumb_secret = "rt37yp";

$admin_key = "intel945";

$albumFILE = "phpix-album.php";

$default_gallery_settings = array(
	"thumb_width" => "200"	,
	"thumb_height" => "150"	,
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