<?php 

include('config.php');
$album = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['aid']."'"));

?><!DOCTYPE html>
<html>
<head>
<?php include('scripts.php'); ?>
<script type="text/javascript" src="<?php echo $gal_domain; ?>js/html2canvas.min.js"></script>
<link href="<?php echo $gal_domain; ?>css/print.css" media="all" rel="stylesheet" />
<title>PHPix Print</title>
</head>
<body>

<div class="gal-print-ctr">

<div class="gal-print-frame gal-print-size-postcard">
<img src="http://localhost/familydb/fhd/5f0543c1bc283.jpg">
<h2>Happy Birthday!</h2>
</div>

</div>


<script>
html2canvas(document.querySelector(".gal-print-frame")).then(canvas => {
    document.body.appendChild(canvas)
});
</script>
</body>
</html>
