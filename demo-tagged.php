<?php 

include('config.php');
$album = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['aid']."'"));

?><!DOCTYPE html>
<html>
<head>
<?php include('scripts.php'); ?>
<title>TAG PHOTO</title>
</head>
<body>


<div style="padding:50px;">


<img onload="xtag_init(this)" src="hd/5f2bdd0ee520e.jpg">



</div>






</body>
</html>