<?php 

sadmin_title('Albums<a href="'.$admin_url.'new-album" class="btn btn-success btn-medium pull-right">Create new</a>'); 

if($_GET['delete']!=''){

$qry = "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['delete']."'";
$xdata = mysqli_fetch_assoc(mysqli_query($con, $qry));

if($xdata['count']!=0){
notify('There are <b>'.$xdata['count'].' photos</b> in <b>'.$xdata['title'].'</b>. An album can only be deleted after deleting all photos in that album!', 'albums', 'danger');
} elseif($xdata['title']==''){
notify('No album found with ID = <b>'.$_GET['delete'].'</b>', 'albums', 'warning');
} else {
@unlink('cover/'.$xdata['thumb']);
mysqli_query($con, "DELETE FROM `".$prefix."albums` WHERE `id`='".$_GET['delete']."'");
notify('<b>'.$xdata['title'].'</b> was deleted successfully!', 'albums', 'success');
}

}

?>
<div class="clearfix"></div>
<?php echo $notify['albums']; ?>

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

$res = mysqli_query($con, "SELECT * FROM `".$prefix."albums` 
ORDER BY `".$prefix."albums`.`created` DESC");

$i=0;
while($row = mysqli_fetch_assoc($res)){
++$i;

if($row['thumb']!=''){
$photo = $domain.'cover/'.$row['thumb'];
} else {
$photo = $domain.'phpix-libs/images/holder.svg';
}

echo'<tr id="row-'.$row['id'].'">
<td class="nopadding"><img src="'.$photo.'"></td>
<td class="album-count">'.$row['count'].'</td>
<td>'.xdate($row['created'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
<td>'.xdate($row['updated'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
<td class="album-info"><b class="album-title">'.$row['title'].'</b><p>'.$row['descr'].'</p>
<div class="album-buttons">
<a class="btn btn-sm btn-success" target="_blank" href="'.$domain.''.$albumFILE.'?aid='.$row['id'].'">Browse</a> 
<a class="btn btn-sm btn-warning" onclick="album_manage(this, \''.$row['id'].'\')" href="javascript:void(0)">Manage</a> 
<a class="btn btn-sm btn-info" href="'.$admin_url.'settings&aid='.$row['id'].'">Settings</a> 
<a class="confirm btn btn-sm btn-danger" href="'.$admin_url.'albums&delete='.$row['id'].'">Delete</a>
</div>
</td>
</tr>';
}

 ?>
    </tbody>
</table>

<p>&nbsp;</p>

<script>
$(document).ready( function () {
$('#tbl-albums').DataTable({
responsive: true
});
} );
</script>