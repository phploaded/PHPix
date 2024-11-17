<?php 
if($phpix_user==''){die('You must be logged in.');}
if($_GET['method']=='nocache'){
@unlink('cache/'.$_GET['file']);
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
}



if($_GET['method']=='calculate'){

$filenm = $_SERVER['HTTP_HOST'].'-index-'.date("Ym").'.html';
@unlink('cache/'.$filenm);

$path = $_GET['dir'];
$files = array_diff(scandir($path), array('.', '..'));

$bytes = 0;
foreach($files as $file){
$bytes = $bytes + filesize($path.'/'.$file);
}

mysqli_query($con, "UPDATE `".$prefix."dirs` SET `time`='".time()."', `files`='".count($files)."', `size`='$bytes' WHERE `id`='".$path."'");
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
}





if($_GET['method']=='generate'){

if($_GET['dir']=='thumb'){
$dir = 'full';
} else {
$dir = $_GET['dir'];
}

if(isset($_GET['albumid'])){
$return_url = $domain."phpix-manage.php?page=backup&aid=".$_GET['albumid']."&make=yes&dir=".$_GET['dir'];
} else {
$return_url = $domain."phpix-manage.php?page=operations&method=calculate&dir=".$_GET['dir'];
}

echo'<br><br>
<div class="progress progress-striped active">
<div id="gen-progress-bar" class="progress-bar" style="width: 0%"></div>
</div>
<div class="col-xs-12 col-md-6"><div id="gen-stats" class="well"></div></div>
<div class="col-xs-12 col-md-6"><div class="well"><ol class="gen-files">';
$data = mysqli_query($con, "SELECT `thumb` FROM `".$prefix."uploads`");
while($row = mysqli_fetch_assoc($data)){
if(!file_exists($_GET['dir'].'/'.$row['thumb'])){
echo'<li>'.$row['thumb'].'</li>';
}
}
echo"</ol></div></div>
<script>
var quality = '".$dir."';
var totfiles = 0;
$(document).ready(function(){
totfiles = $('.gen-files li').length;
start_generating();
});

\r\n
function start_generating(){
var remfiles = $('.gen-files li').length;
if(remfiles != 0){
$.post(main_domain+'thumb-gen.php', {id:$('.gen-files li:last-child').html(), q:quality} ,function(){
$('.gen-files li:last-child').remove();
var pcent = parseFloat(((totfiles-remfiles)/totfiles)*100).toFixed(2);
$('#gen-progress-bar').css('width', pcent+'%');

var zhtml = '<div class=\"huge\">'+pcent+'%</div><br><br><b>Total Files : </b>'+totfiles+'<br><br>\
<b>Completed : </b>'+(totfiles-remfiles)+'<br><br>\
<b>Remaining : </b>'+remfiles;
$('#gen-stats').html(zhtml);
start_generating();
});
} else {
document.location.href = '".$return_url."';
}
}
</script>";


}




if($_GET['method']=='delete'){
admin_only();
$filenm = $_SERVER['HTTP_HOST'].'-index-'.date("Ym").'.html';
@unlink('cache/'.$filenm);

$path = $_GET['dir'];
if($path!='full'){
$files = array_diff(scandir($path), array('.', '..'));

foreach($files as $file){
unlink($path.'/'.$file);
}

// create empty index.html
file_put_contents($path.'/index.html', '');

mysqli_query($con, "UPDATE `".$prefix."dirs` SET `time`='".time()."', `files`='1', `size`='0' WHERE `id`='".$path."'");
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
} else {
echo'<br><br><br><br>Cannot delete original images!';
}
}

 ?>