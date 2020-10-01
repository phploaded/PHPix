<?php 
include('phpix-config.php');
include('phpix-front-functions.php');

if($_GET['design']=='1'){
echo'<!DOCTYPE html>
<html><head>';
include('phpix-scripts.php');
echo'</head>
<body>';
}


$file_parts = pathinfo($_GET['id']);
$file = 'full/'.$file_parts['filename'].'.'.$file_parts['extension'];
$size = getimagesize($file);

$aspect = round($size[0]/$size[1], 2, PHP_ROUND_HALF_UP);

if($aspect == 1.78){
$ratio = '16:9';
} elseif($aspect == 1.6){
$ratio = '16:10';
} elseif($aspect == 1.33){
$ratio = '4:3';
} elseif($aspect == 2){
$ratio = '18:9';
} elseif($aspect == 0.5){
$ratio = '9:18';
} elseif($aspect == 0.75){
$ratio = '3:4';
} elseif($aspect == 0.63){
$ratio = '10:16';
} elseif($aspect == 0.56){
$ratio = '9:16';
} elseif($aspect == 1.25){
$ratio = '5:4';
} elseif($aspect == 1.5){
$ratio = '3:2';
} elseif($aspect == 1.67){
$ratio = '15:9';
} elseif($aspect == 1.71){
$ratio = '128:75';
} elseif($aspect == 0.67){
$ratio = '2:3';
} else {
$ratio = $aspect;
}

$flash = array(
"0" => "Flash Did Not Fire",
"1" => "Flash Fired",
"2" => "Strobe Return Light Detected",
"4" => "Strobe Return Light Not Detected",
"8" => "Compulsory Flash Mode",
"16" => "Auto Mode",
"32" => "No Flash Function",
"64" => "Red Eye Reduction Mode"
);

$metering_mode = array(
"0" => "Unknown",
"1" => "Average",
"2" => "Center Weighted Average",
"3" => "Spot",
"4" => "Multi Spot",
"5" => "Pattern",
"6" => "Partial"
);

if(file_exists($file)){
$exif = exif_read_data($file);

$mmode = $exif['MeteringMode'];
if(array_key_exists($mmode, $metering_mode)){
$metering = $metering_mode[$mmode].' ('.$mmode.')';
} else {
$metering = $mmode;
}

$fp = explode('/', $exif['FocalLength']);
if(count($fp)>0 && $fp[1]>0){
$focal = $fp[0]/$fp[1].' mm';
} elseif($fp[0]>0 && $fp[1]<=0){
$focal = $fp[0].' mm';
} else {
$focal = $exif['FocalLength'];
}

$local_name = $file_parts['filename'].'.'.$file_parts['extension'];

$sql = "SELECT 
`".$prefix."uploads`.`id`,
`".$prefix."uploads`.`type`,
`".$prefix."uploads`.`title`,
`".$prefix."uploads`.`folder`,
`".$prefix."uploads`.`caption`,
`".$prefix."uploads`.`tags`,
`".$prefix."uploads`.`url`,
`".$prefix."uploads`.`thumb`,
`".$prefix."uploads`.`time`,
`".$prefix."uploads`.`uid`,
`".$prefix."uploads`.`size`,
`".$prefix."albums`.`title` as albumName,
`".$prefix."albums`.`count`
 FROM `".$prefix."uploads` 
 LEFT JOIN `".$prefix."albums` 
ON `".$prefix."uploads`.`folder` = `".$prefix."albums`.`id` WHERE `url`='".$local_name."' limit 1";

$data = mysqli_fetch_assoc(mysqli_query($con, $sql));

echo'<div xtarget="gal_info_main" class="gal-title"><b class="gal-expand" title="Click this icon to toggle information"></b>General Information</div>
<div class="gal-info gal_info_main">
<h3>'.$data['title'].'</h3>
<p>'.$data['caption'].'</p>
<ul class="gal-tags">'.gal_html_tags($data['tags'], $gallery_domain.'tag.php?id=').'</ul>
<p>Uploaded in <a href="'.$gallery_domain.'album.php?aid='.$data['folder'].'">'.$data['albumName'].'</a> ('.$data['count'].' photos)</p>
</div>

<div xtarget="gal_info_file" class="gal-title"><b class="gal-expand" title="Click this icon to toggle information"></b><b title="Click this icon or press shift+i on keyboard to pin/unpin" class="gal-pin">📌</b>File &amp; Attributes</div>
<div class="gal-info gal_info_file">
<table>
<tr><td>File Name :</td><td>'.$file_parts['filename'].'.'.$file_parts['extension'].'</td></tr>
<tr><td>Type :</td><td>'.mime_content_type($file).' ('.$file_parts['extension'].' file)</td></tr>
<tr><td>Size :</td><td>'.convertToReadableSize($exif['FileSize']).' ('.$exif['FileSize'].' bytes)</td></tr>
<tr><td>Modified on :</td><td>'.date("d M Y, h:i a", filemtime($file)).'</td></tr>
<tr><td>Attributes :</td><td>'.$size[0].' x '.$size[1].' ('.round((($size[0]*$size[1])/1000000), 1, PHP_ROUND_HALF_UP).' MP) <b>'.$ratio.'</b></td></tr>
</table>
<br />
</div>

<div xtarget="gal_info_exif" class="gal-title"><b class="gal-collapse" title="Click this icon to toggle information"></b>Exif Information</div>
<div class="gal-tabs gal_info_exif">
<div class="gal-exif">
<table>
<tr><td>Make</td><td>'.$exif['Make'].'</td></tr>
<tr><td>Model</td><td>'.$exif['Model'].'</td></tr>
<tr><td>Software</td><td>'.$exif['Software'].'</td></tr>
<tr><td>Captured on</td><td>'.$exif['DateTime'].'</td></tr>
<tr><td>Exposure Time</td><td>'.$exif['ExposureTime'].'</td></tr>
<tr><td>Exposure Program</td><td>'.$exif['ExposureProgram'].'</td></tr>
<tr><td>Exposure Bias</td><td>'.$exif['ExposureBiasValue'].'</td></tr>
<tr><td>F Number</td><td>'.$exif['FNumber'].'</td></tr>
<tr><td>Max Aperture</td><td>'.$exif['MaxApertureValue'].'</td></tr>
<tr><td>ISO Speed</td><td>'.$exif['ISOSpeedRatings'].'</td></tr>
<tr><td>Flash</td><td>'.$flash[$exif['Flash']].'</td></tr>
<tr><td>Focal Length</td><td>'.$focal.'</td></tr>
<tr><td>Metering Mode</td><td>'.$metering.'</td></tr>
<tr><td>Comment</td><td>'.$exif['COMMENT'][0].'</td></tr>
</table>
</div>
<div class="gal-comment"></div>
</div><script>gal_infotabs();</script>';
} else {
echo"<b>$file</b> - File not found";
}


if($_GET['design']=='1'){
echo'</body></html>';
}
 ?>