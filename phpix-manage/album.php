<?php 

$album = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='".$_GET['aid']."' limit 1"));

sadmin_title($album['title'].' <small>('.$album['count'].' Photos)</small><a href="#" onclick="album_manage(this, \''.$_GET['aid'].'\')" class="btn btn-info btn-medium pull-right">Upload Photos</a>'); 

?><div id="picurl"></div>
<p><?php echo $album['descr']; ?></p><hr />
<table id="tbl-albums" class="table table-stripped table-bordered table-condensed table-hover display">
    <thead>
        <tr>
            <th>Preview</th>
            <th>Description</th>
			<th>Pics</th>
			<th width="65">Created</th>
			<th width="65">Updated</th>
        </tr>
    </thead>
    <tbody>
<?php 

$res = mysqli_query($con, "SELECT * FROM `".$prefix."photos` ORDER BY `created` DESC");

if($row['thumb']!=''){
$photo = $row['thumb'];
} else {
$photo = $domain.'phpix-libs/images/holder.png';
}

while($row = mysqli_fetch_assoc($res)){
echo'<tr>
<td class="album-thumb"><img style="width:100%;" src="'.$photo.'" /></td>
<td class="album-info"><b class="album-title">'.$row['title'].'</b><p>'.$row['descr'].'</p>
<div class="album-buttons">
<a class="btn btn-sm btn-success" href="'.$admin_url.'album&aid='.$row['id'].'">Manage</a> 
<a class="btn btn-sm btn-info" href="'.$admin_url.'options&aid='.$row['id'].'">Settings</a> 
<a class="confirm btn btn-sm btn-danger" href="'.$admin_url.'albums&delete='.$row['id'].'">Delete</a>
</div>
</td>
<td>'.$row['count'].'</td>
<td>'.xdate($row['created'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
<td>'.xdate($row['updated'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>
</tr>';
}

 ?>
    </tbody>
</table>
<script>
$(document).ready( function () {
$('#tbl-albums').DataTable();
} );
</script>