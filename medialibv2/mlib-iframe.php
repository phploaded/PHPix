<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="mlib-includes/css/iframe.css" rel="stylesheet" type="text/css" />
<script src="mlib-includes/js/jquery-1.11.1.min.js" type="text/javascript"></script>
<script src="mlib-includes/js/iframe.js" type="text/javascript"></script>
<?php 

echo'<script>mlib_parent_init = \''.$_GET['init'].'\';</script>';

 ?>
<title>upload files</title>
</head>
<body>

<form class="iframe-form" method="post" name="xyz" enctype="multipart/form-data" action="mlib-upload-ie.php">
<input type="hidden" name="mlib_manual" value="yes">
<div class="mlib-extra-upload"><input type="file" name="file[]" /> <input type="button" value="+ Add More" id="addmoreupload" /></div>
<div style="clear:both;"></div><br /><br /><input type="submit" value="Upload Now" />
</form>

</body>
</html>