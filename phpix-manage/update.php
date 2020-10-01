<?php 

include('phpix-info.php');

 ?>
<div class="page-header" id="banner">
</div>

<div class="well">You are currently running <b>PHPix <?php echo $software_version; ?></b> released on <i><?php echo xdate($software_updated); ?></i></div>

<div class="well">
<?php 
$filedata = file_get_contents($software_jsonURL.'?v='.$software_version);
$x = json_decode($filedata, true);
echo $filedata;


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