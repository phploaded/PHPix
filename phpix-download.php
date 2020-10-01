<?php 

include('phpix-config.php');
include('phpix-front-functions.php');
$file = $_GET['f'];
$quality = $_GET['q'];

if(file_exists($quality.'/'.$file)){
header('location:'.$quality.'/'.$file);
} else {
header('location:thumb-gen.php?q='.$quality.'&id='.$file);
}

 ?>