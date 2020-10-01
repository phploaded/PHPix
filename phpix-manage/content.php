<?php 
$type = $_GET['id'];
?>

<div class="page-header" id="banner"></div>


<?php  

if(isset($_GET['newrow'])){
mysqli_query($con, "INSERT INTO `".$prefix."content` (`id`, `type`, `time`) VALUES (NULL, '$type', '".time()."')");
echo'<div class="alert alert-dismissible alert-success">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Success!</h4>
  <p>Blank <b>'.$type.'</b> item created successfully. Redirecting.....</a></p>
</div><script>document.location.href = \''.$admin_url.'content&id='.$type.'\';</script>';
}


if(isset($_GET['delete'])){
mysqli_query($con, "DELETE FROM `".$prefix."content` WHERE `id`='".$_GET['delete']."'");
echo'<div class="alert alert-dismissible alert-danger">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Success!</h4>
  <p><b>'.$type.'</b> with ID = '.$_GET['delete'].' item deleted successfully. Redirecting.....</a></p>
</div><script>document.location.href = \''.$admin_url.'content&id='.$type.'\';</script>';
}

if(count($_POST)!=0){

foreach($_POST['title'] as $key => $value){
$sql = "UPDATE `".$prefix."content` SET `status` = '".$_POST['status'][$key]."', `slug` = '".slugify($_POST['slug'][$key])."', `title` = '".$_POST['title'][$key]."', `sort` = '".$_POST['order'][$key]."' WHERE `id` = '".$key."'";
mysqli_query($con, $sql);
}

echo'<div class="alert alert-dismissible alert-success">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Success!</h4>
  <p>Changes were saved successfully.</p>
</div>';


}

?>


<form name="newssave" action="" method="post">

<div class="pull-left admpages"></div><br /><br />
<div class="clearfix"></div>

<div class="panel panel-primary">
<div class="panel-heading">
<h3 class="panel-title">Manage existing <?php echo $type; ?> <span class="pull-right"><a href="<?php echo $admin_url; ?>content&id=<?php echo $type; ?>&newrow=1" class="btn btn-warning btn-xs">Insert New</a> <button type="submit" class="btn btn-success btn-xs">Save Changes</button></span></h3>
</div>



<table class="table table-bordered table-striped">
<thead>
<tr>
<th>Title</th>
<th>Date</th>
<th>Slug</th>
<th width="200">Display</th>
<th width="80">Order</th>
<th width="130">Action</th>
</tr>
</thead>
<tbody>
<?php 
$numx = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `".$prefix."content` WHERE `type`='$type'"));
$paginate = quick_paginate($numx);
$res = mysqli_query($con, "SELECT * FROM `".$prefix."content` WHERE `type`='$type' ORDER BY `sort` ASC limit ".$paginate['start'].",".$paginate['per_page']."");
while($data = mysqli_fetch_assoc($res)){
echo'<tr>
<td><input required type="text" placeholder="Title Here" class="form-control" name="title['.$data['id'].']" value="'.$data['title'].'" /></td>
<td>'.date($date_format, $data['time']).'</td>
<td><input required type="text" placeholder="Slug Here" class="form-control" name="slug['.$data['id'].']" value="'.$data['slug'].'" /></td>
<td><select class="form-control" name="status['.$data['id'].']">
<option>'.$data['status'].'</option>
<option>Enabled</option>
<option>Front Page</option>
<option>Disabled</option>
</select></td>
<td><input type="text" placeholder="#" class="form-control" name="order['.$data['id'].']" value="'.$data['sort'].'" /></td>
<td>
<a class="btn btn-info btn-sm" href="'.$admin_url.'edit-content&id='.$data['id'].'&type='.$_GET['id'].'">Edit</a>
<a class="btn btn-danger btn-sm confirm" href="'.$admin_url.'content&id='.$type.'&delete='.$data['id'].'">Delete</a>
</td>
</tr>';
}

 ?></tbody>
</table>
</div>
</form>

<script>
jQuery(function() {
    jQuery('.admpages').pagination({
        items: <?php echo $numx['total']; ?>,
        itemsOnPage:10,
		hrefTextPrefix: "admin.phpcontent&id=<?php echo $_GET['id']; ?>&pagenumber=",
		hrefTextSuffix: "",
		currentPage: <?php echo $paginate['current']; ?>,
        cssStyle: 'light-theme'
    });
});
</script>
<?php include('footer.php'); ?>