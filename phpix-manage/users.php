<?php 
admin_only();
sadmin_title('Private Users<a href="'.$admin_url.'new-user" class="btn btn-success btn-medium pull-right">Create new</a><div class="clearfix"></div>'); 

if(isset($_POST['uname'])){
if(count($_POST['uname'])>0){

foreach($_POST['uname'] as $key => $val){
$uname = $_POST['uname'][$key];
$pwd = $_POST['pwd'][$key];
if($pwd!=''){
mysqli_query($con, "UPDATE `".$prefix."users` SET `pwd`='$val', `time`='".time()."' WHERE `id`='$key'");
}
mysqli_query($con, "UPDATE `".$prefix."users` SET `time`='".time()."', `name`='".$uname."' WHERE `id`='$key'");
}

}
}


if(isset($_GET['delete'])){
mysqli_query($con, "DELETE FROM `".$prefix."users` WHERE `id`='".$_GET['delete']."'");
echo'<script>document.location.href = \''.$domain.'phpix-manage.php?page=users\';</script>';
}


?>

<form action="" method="post" autocomplete="off">
<table id="tbl-users" class="table table-stripped table-bordered table-condensed table-hover">
<thead>
<tr>
<th>Name</th>
<th>New Password</th>
<th>Email</th>
<th>Updated on</th>
<th>Options</th>
</tr>
</thead>
<tbody>
<?php 

$sql = "SELECT * FROM `".$prefix."users`";
$res = mysqli_query($con, $sql);

while($row = mysqli_fetch_assoc($res)){
echo'<tr>
<td><input minlength="3" required type="text" class="form-control" placeholder="Name" value="'.$row['name'].'" name="uname['.$row['id'].']"></td>
<td><input minlength="8" maxlength="20" type="text" class="form-control" placeholder="New password" name="pwd['.$row['id'].']"></td>
<td>'.$row['email'].'</td>
<td>'.xdate($row['time']).'</td>
<td>
<a href="'.$admin_url.'users&delete='.$row['id'].'" class="confirm btn btn-sm btn-danger">Delete</a>
</td>
</tr>';
}

 ?>
</tbody>
</table>
<br /><div class="text-center"><button type="submit" class="btn btn-info">Save Changes</button></div>
</form>

<script>
$(document).ready( function () {
$('#tbl-users').DataTable({
responsive: true
});
} );
</script>