<?php 

sadmin_title('<i class="fa fa-photo"></i> Albums<a href="'.$admin_url.'new-album" class="btn btn-success btn-medium pull-right">Create new</a>'); 

if(!isset($_GET['delete'])){$_GET['delete']='';} 

if($_GET['delete']!=''){

$qry = "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['delete']."'";
$xdata = mysqli_fetch_assoc(mysqli_query($con, $qry));

if($xdata['count']!=0){
notify('There are <b>'.$xdata['count'].' photos</b> in <b>'.$xdata['title'].'</b>. An album can only be deleted after deleting all photos in that album!', 'albums', 'danger');
} elseif($xdata['title']==''){
notify('No album found with ID = <b>'.$_GET['delete'].'</b>', 'albums', 'warning');
} else {
@unlink('cover/'.$xdata['thumb']);
if($_SESSION['PHPix']!=''){
mysqli_query($con, "DELETE FROM `".$prefix."albums` WHERE `id`='".$_GET['delete']."'");
} elseif($_SESSION['phpixuser']!=''){
mysqli_query($con, "DELETE FROM `".$prefix."albums` WHERE `id`='".$_GET['delete']."' AND `uid`='".$_SESSION['phpixuser']."'");
} else {
die('request aborted!');
}
notify('<b>'.$xdata['title'].'</b> was deleted successfully!', 'albums', 'success');
}

}

?>
<div class="clearfix"></div>
<?php 
if(!isset($notify['albums'])){$notify['albums']='';} 
echo $notify['albums']; ?>

<table id="tbl-albums" class="table table-stripped table-bordered table-condensed table-hover display">
    <thead>
        <tr>
            <th width="100">Preview</th>
			<th width="50">Photos</th>
			<th width="85">Created</th>
			<th width="85">Updated</th>
			<th>Description</th>
        </tr>
    </thead>
    <tbody>
<?php 

if($_SESSION['PHPix']!=''){
$sql2 = "SELECT * FROM `".$prefix."albums` 
ORDER BY `".$prefix."albums`.`created` DESC";
} 

if($_SESSION['phpixuser']!=''){
$sql2 = "SELECT * FROM `".$prefix."albums` WHERE `uid`='".$_SESSION['phpixuser']."'
ORDER BY `".$prefix."albums`.`created` DESC";
}

$res = mysqli_query($con, $sql2);

$i=0;
$albums = '';
while($row = mysqli_fetch_assoc($res)){
++$i;

if($row['thumb']!=''){
$photo = $domain.'cover/'.$row['thumb'];
} else {
$photo = $domain.'phpix-libs/images/holder.svg';
}

$albums = $albums.'<option value="'.$row['id'].'">'.$row['title'].'</option>';

if($row['parent']==''){
$parent = '(Top folder)';
} else {
$parent = '(Sub folder)';
}

echo'<tr id="row-'.$row['id'].'">
<td class="nopadding"><img class="album-thumb" src="'.$photo.'"></td>
<td class="album-count">'.$row['count'].'</td>
<td data-sort="'.$row['created'].'">'.xdate($row['created'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
<td data-sort="'.$row['updated'].'">'.xdate($row['updated'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
<td class="album-info"><b class="album-title">'.$row['title'].' '.$parent.'</b><p>'.$row['descr'].'</p>
<div class="album-buttons">
<a class="btn btn-sm btn-success" target="_blank" href="'.$domain.''.$albumFILE.'?aid='.$row['id'].'">Browse</a> 
<a class="btn btn-sm btn-warning" onclick="album_manage(this, \''.$row['id'].'\')" href="javascript:void(0)">Manage</a> 
<a class="btn btn-sm btn-info" href="'.$admin_url.'settings&aid='.$row['id'].'">Settings</a> 
<a class="btn btn-sm btn-primary" href="'.$admin_url.'backup&aid='.$row['id'].'">Backup</a> 
<a class="confirm btn btn-sm btn-danger" href="'.$admin_url.'albums&delete='.$row['id'].'">Delete</a>
</div>
</td>
</tr>';
}

 ?>
    </tbody>
</table>

<select style="display:none;" id="album-ids"><?php echo $albums; ?></select>

<p>&nbsp;</p>

<script>
$(document).ready( function () {
$('#tbl-albums').DataTable({
responsive: true,
"columnDefs": [
        { "targets": [1,2,3], "searchable": false }
    ]
});
} );
</script>