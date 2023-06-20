<?php 

if(!isset($_POST['action'])){$_POST['action']='';} 

if(isset($_FILES['xfile']['tmp_name']) && $_FILES['xfile']['tmp_name']!=''){
rrmdir('temp/');
$parts = pathinfo($_FILES['xfile']['name']);
$ext = strtolower($parts['extension']);
if($ext=='zip'){
$file = slugify($parts['filename']).'.'.$ext;

move_uploaded_file($_FILES['xfile']['tmp_name'], 'phpix-imports/packages/'.$file);



$zip = new ZipArchive;
if ($zip->open('phpix-imports/packages/'.$file) === TRUE) {
    $zip->extractTo('temp/');
    $zip->close();
}

if(file_exists('temp/info.json')){
$data = json_decode(file_get_contents('temp/info.json'), true);
mkdir('phpix-imports/packages/'.$data['type'].'-'.$data['folder']);
xcopy('temp/', 'phpix-imports/packages/'.$data['type'].'-'.$data['folder']);
notify('<b>Success : </b>Package installed successfully!', 'installer', 'success');
} else {
notify('<b>Warning : </b>package does not contain info.json!', 'installer', 'warning');
}

rrmdir('temp/');
mkdir('temp/');
unlink('phpix-imports/packages/'.$file);

} else {
notify('<b>Warning : </b>package is not a zip file!', 'installer', 'warning');
}


}


if($_POST['action']=='ACTIVATE'){
foreach($_POST['package'] as $package){
$data = json_decode(file_get_contents($package.'/info.json'), true);
mysqli_query($con, "INSERT INTO `".$prefix."packages` (`id`, `dir`) VALUES (NULL, '".$package."');");
if(file_exists($package.'/activate.php')){
include($package.'/activate.php');
}

if($data['type']=='theme'){
$zip = new ZipArchive;
if ($zip->open($package.'/'.$data['file']) === TRUE) {
    $zip->extractTo('phpix-imports/themes/'.$data['folder'].'/');
    $zip->close();
}
}

}
}

if($_POST['action']=='DEACTIVATE' || $_POST['action']=='DELETE'){
foreach($_POST['package'] as $package){
$data = json_decode(file_get_contents($package.'/info.json'), true);
mysqli_query($con, "DELETE FROM `".$prefix."packages` WHERE `dir`='".$package."'");
if(file_exists($package.'/deactivate.php')){
include($package.'/deactivate.php');
}

if($data['type']=='theme'){
rrmdir('phpix-imports/themes/'.$data['folder'].'/');
}

if($_POST['action']=='DELETE'){
if(file_exists($package.'/delete.php')){
include($package.'/delete.php');
}
rrmdir($package);
}

}
}



 ?><br />
<div class="row">
<div class="col-xs-12 col-md-3"></div>

<div class="col-xs-12 col-md-6">
<?php 
if(!isset($notify['installer'])){$notify['installer']='';} 
echo $notify['installer']; 
?><br>
<form action="" method="post" enctype="multipart/form-data">
<div class="panel panel-primary">
<div class="panel-heading">Install a ZIP package file</div>
<div class="panel-body">
<input type="file" name="xfile" />
</div>
<div class="panel-footer text-right">
<button type="submit" class="btn btn-small btn-info">Install Package</button>
<a class="btn btn-small btn-warning" target="_blank" href="http://phploaded.com/page/phpix-themes-packages.html">Find packages</a>
</div>
</div>
</form>

</div>

<div class="col-xs-12 col-md-3"></div>
</div>
<div class="clearfix"></div>

<form name="packages-form" action="" method="post">
<div class="page-header"><b class="text-danger">Installed Packages</b>
<div class="pull-right">With selected packages : 
<select name="action">
<option value="">-- NO ACTION --</option>
<option value="ACTIVATE">ACTIVATE</option>
<option value="DEACTIVATE">DEACTIVATE</option>
<option value="DELETE">DELETE</option>
</select>
<button class="btn btn-xs btn-success">APPLY</button>
</div>
</div>


<table id="tbl-installer" class="table table-bordered table-striped table-hover">
<thead>
<tr>
<th width="20"><input type="checkbox" data-chk="package[]" onclick="toggle_all_checkboxes(this)"></th>
<th>Package Name</th>
<th>Type</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<?php 

$dirs = array_filter(glob('phpix-imports/packages/*' , GLOB_ONLYDIR), 'is_dir');
$actv = array();
$active = mysqli_query($con, "SELECT * FROM `".$prefix."packages`");
while($row = mysqli_fetch_assoc($active)){
$actv[] = $row['dir'];
}

foreach($dirs as $dir){
$data = json_decode(file_get_contents($dir.'/info.json'), true);
if(in_array($dir, $actv)){$class='class="success"';} else {$class='';}
echo'<tr '.$class.'>
<td><input type="checkbox" name="package[]" value="'.$dir.'"></td>
<td><b>'.$data['name'].'</b></td>
<td>'.$data['type'].'</td>
<td>'.$data['info'].'<br /><i>Last updated on :'.date("l, d-m-Y, h:i a", $data['date']).', developed by : '.$data['author'].'</i></td>
</tr>';
}

 ?>
</tbody>
</table>
</form>
<script>
$(document).ready( function () {
$('#tbl-installer').DataTable({
responsive: true
});
} );
</script>