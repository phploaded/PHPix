<?php 

$zhtml = '';

if($_SESSION['phpixuser']!=''){
echo'<style type="text/css">#delete-cache{display:none;}</style>';
}

if(isset($_GET['welcome'])){

sadmin_title('Installation Complete!'); 
include('phpix-info.php');
?>

<div class="clearfix"></div>
<div class="well">
<h4>What's new in <b>PHPix <?php echo $software_version; ?></b> that was released on <i><?php echo xdate($software_updated, 'global', 'both'); ?></i></h4>
<?php 
if(file_exists('changelog/'.$software_version.'.html')){
include('changelog/'.$software_version.'.html'); 
} else {
echo'<p>No information is provided. Please refer to official website.</p>';
}
?>
</div>


<?php

} else {
$filenm = $_SERVER['HTTP_HOST'].'-index-'.date("Ym").'.html';
sadmin_title('<i class="fa fa-pie-chart"></i> Dashboard<a href="phpix-manage.php?page=operations&method=nocache&file='.$filenm.'" class="pull-right btn btn-warning"><i class="fa fa-refresh"></i> Refresh</a><div class="clearfix"></div>'); 
include('phpix-info.php');
?>
<div class="clearfix"></div>
<div class="well">You are currently running <b>PHPix <?php echo $software_version; ?></b> released on <i><?php echo xdate($software_updated); ?></i></div>
<?php
if(file_exists('cache/'.$filenm)){ 
readfile('cache/'.$filenm);
 } else {
$users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total from `".$prefix."users`"));
$albums = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total from `".$prefix."albums`"));
$notes = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total from `".$prefix."content` WHERE `type`='note'"));
$photos = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total from `".$prefix."uploads` WHERE `folder`!=''"));


$data = mysqli_query($con, "SELECT * FROM `".$prefix."dirs` ORDER BY `sort` ASC");
$tb = 0;
while($row = mysqli_fetch_assoc($data)){

if($row['id']=='full' || $row['id']=='cover'){
$xlinks = '';
} elseif($row['id']=='temp'){
$xlinks = '<a class="confirm btn btn-xs btn-danger" href="'.$admin_url.'operations&method=delete&dir='.$row['id'].'">Delete</a>
';
} else {
$xlinks = '<a class="confirm btn btn-xs btn-danger" href="'.$admin_url.'operations&method=delete&dir='.$row['id'].'">Delete</a>
<a class="btn btn-xs btn-success" href="'.$admin_url.'operations&method=generate&dir='.$row['id'].'">Generate</a>';
}

// show 1 file less in count for exact no of images
$zhtml = $zhtml.'<tr>
<td>'.$row['id'].'</td>
<td>'.($row['files']-1).'</td>
<td>'.xsize($row['size']).'</td>
<td>'.xdate($row['time'], 'global', 'dynamic').'</td>
<td>
'.$xlinks.'
<a class="btn btn-xs btn-info" href="'.$admin_url.'operations&method=calculate&dir='.$row['id'].'">Calculate</a></td>
</tr>';
$tb = $tb + $row['size'];
}



$output = '<div class="row">
<div class="col-xs-12 col-sm-6 col-md-3">
<div class="panel panel-warning">
<div class="panel-heading">
<div class="row">
<div class="col-xs-3">
<i class="fa fa-users fa-5x"></i>
</div>
<div class="col-xs-9 text-right">
<div class="huge">'.$users['total'].'</div>
<div>Private users!</div>
</div>
</div>
</div>
<a href="'.$domain.'phpix-manage.php?page=users">
<div class="panel-footer">
<span class="pull-left">Manage private users</span>
<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
<div class="clearfix"></div>
</div>
</a>
</div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
<div class="panel panel-info">
<div class="panel-heading">
<div class="row">
<div class="col-xs-3">
<i class="fa fa-photo fa-5x"></i>
</div>
<div class="col-xs-9 text-right">
<div class="huge">'.$photos['total'].'</div>
<div>Photos in '.$albums['total'].' albums!</div>
</div>
</div>
</div>
<a href="'.$domain.'phpix-manage.php?page=albums">
<div class="panel-footer">
<span class="pull-left">Manage gallery albums</span>
<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
<div class="clearfix"></div>
</div>
</a>
</div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
<div class="panel panel-success">
<div class="panel-heading">
<div class="row">
<div class="col-xs-3">
<i class="fa fa-edit fa-5x"></i>
</div>
<div class="col-xs-9 text-right">
<div class="huge xamountx">'.$notes['total'].'</div>
<div class="xamountx2">Public notes!</div>
</div>
</div>
</div>
<a href="'.$domain.'phpix-manage.php?page=content&id=note">
<div class="panel-footer">
<span class="pull-left">Manage public notes</span>
<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
<div class="clearfix"></div>
</div>
</a>
</div>
</div>
<div class="col-xs-12 col-sm-6 col-md-3">
<div class="panel panel-danger">
<div class="panel-heading">
<div class="row">
<div class="col-xs-3">
<i class="fa fa-support fa-5x"></i>
</div>
<div class="col-xs-9 text-right">
<div class="huge"></div>
<div>Are you a developer? Do you want to develop packages for PHPix?</div>
</div>
</div>
</div>
<a target="_blank" href="http://phploaded.com/post/phpix-docs/">
<div class="panel-footer">
<span class="pull-left">View Documentation</span>
<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
<div class="clearfix"></div>
</div>
</a>
</div>
</div>
</div>




<div class="row">
<div class="col-xs-12 col-sm-6 col-md-12">
<div class="panel panel-primary">
<div class="panel-heading"><i class="fa fa-hdd-o fa-fw"></i> Disk Space Usage <b class="pull-right">Total : '.xsize($tb).'</b></div>
<!-- /.panel-heading -->

<table class="table table-striped table-bordered table-hover">
<thead><tr>
<th>Folder</th>
<th>Files</th>
<th>Size</th>
<th>Updated on</th>
<th>Options</th>
</tr></thead>
<tbody>
'.$zhtml.'
</tbody>
</table>
</div></div>';

@unlink('cache/'.$filenm);
$fp = fopen('cache/'.$filenm, 'w');
fwrite($fp, $output);
fwrite($fp, '<!-- Cached copy, generated '.date("d-m-Y, h:i:s a").' -->');
fclose($fp);
echo $output;
}
}

?>