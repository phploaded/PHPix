<?php 

include('phpix-info.php');

 ?>
<div class="page-header" id="banner">
</div>

<div class="well">You are currently running <b>PHPix <?php echo $software_version; ?></b> released on <i><?php echo xdate($software_updated); ?></i></div>

<div class="well">
<?php 
$filedata = file_get_contents($software_jsonURL);
$data = json_decode($filedata, true);
$stable = $data['stable'];

function nextitem($item){
global $stable;
global $data;
$found = 0;
$i=$item+0.01;

	while($i <= $data['latest']){
	if(in_array((string)$i, $stable) && $found==0){
	$found = $i;
	}
	$i = $i + 0.01;
	}

if($found!=''){
return $found;
} else {
return $data['latest'];
}

}

$curr_ver = $software_version;

if($curr_ver==$data['latest']){
echo 'No new updates found! You are using the latest version of PHPix!';
} else {
$next = nextitem($curr_ver);
if($next!=$data['latest']){
echo '<h3><b class="text-success">New updates found!</b> PHPix '.$data['latest'].' is the latest version.</h3>';
echo '<p><button onclick="start_update(\''.$next.'\')" class="btn btn-lg btn-success">UPDATE to '.$next.'</button></p>
<p>You must update PHPix to '.$next.' before you can update to latest version because it contains important database upgrades.</p>';
} else {
echo '<h3><b class="text-success">New updates found!</b> PHPix '.$data['latest'].' is the latest version.</h3>';
echo '<p><button onclick="start_update(\''.$next.'\')" class="btn btn-lg btn-success">UPDATE NOW</button></p>
<p>You can update PHPix to '.$next.' (latest version) now.</p>';
}

echo'<h3>Whats new!</h3>
<p>'.$data['info'].'</p>';
}



 ?>
</div>


<script>

function start_update(ver){

var xhtml = '<div id="system-updater">\
<div style="height:50px;" class="progress progress-striped active"><div id="system-updater-progress" class="progress-bar progress-bar-warning" style="width: 0%"></div></div>\
<div id="system-updater-info"><h4>UPDATING PHPix to '+ver+' PLEASE DO NOT CLOSE UNTIL COMPLETED.</h4>\
<p>Starting file download. Please be patient.</p></div>\
</div>';
$('body').append(xhtml);

$('#system-updater-progress').css('width', '20%');
$('#system-updater-info').append('<p>Downloading...</p>');

$.get(main_domain+"phpix-ajax.php?method=update_download&v="+ver, function(data){
$('#system-updater-info').append('<p>'+data+'</p>');
$('#system-updater-progress').css('width', '40%');
});

}



function verify_update(ver){
$('#system-updater-info').append('<p>Verifying downloaded file...</p>');
$.get(main_domain+"phpix-ajax.php?method=update_verify&v="+ver, function(data){
$('#system-updater-progress').css('width', '80%');
$('#system-updater-info').append('<p>'+data+'</p>');
});
}


function install_update(ver){
$('#system-updater-info').append('<p>Installing...</p>');
$.get(main_domain+"phpix-ajax.php?method=update_install&v="+ver, function(data){
$('#system-updater-progress').css('width', '100%');
$('#system-updater-info').append('<p>'+data+'</p>');
});
}

function completed_update(){
document.location.href = main_domain+'phpix-manage.php?page=index&welcome=1';
}
</script>