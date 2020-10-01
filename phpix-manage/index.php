<?php 



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
sadmin_title('Dashboard'); 
}

?>