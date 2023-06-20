<?php 
$title = 'Editing '.ucfirst($_GET['type']).' ID = '.$_GET['id']; 
$pid = $_GET['id'];

if(isset($_POST['title'])){
$title = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
$content = htmlentities($_POST['ckeditor'], ENT_QUOTES, "UTF-8");
mysqli_query($con, "UPDATE `".$prefix."content` SET `title` = '$title', `content` = '$content' WHERE `id` = '$pid' limit 1");
}

$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."content` WHERE `id`='$pid' limit 1"));
?>
<script type="text/javascript" src="<?php echo $domain; ?>ckeditor/ckeditor.js"></script>
<div class="page-header" id="banner">
<div class="row">
<div class="col-xs-12">
<h1><?php echo $title; ?></h1>
</div>
</div>
</div>


<form name="newsadd" action="" method="post">
<div class="panel panel-info">
<div class="panel-heading">
<h3 class="panel-title">Add <?php echo ucfirst($_GET['type']); ?> <button type="submit" class="btn btn-success btn-xs pull-right">Save Changes</button></h3>
</div>
<div class="panel-body">
<input type="text" required class="form-control" value="<?php echo html_entity_decode($data['title'], ENT_QUOTES, "UTF-8"); ?>" Placeholder="Enter title here" name="title" />
</div>
<textarea style="width:100%;" name="ckeditor" id="ckeditor" class="ckeditor"><?php echo html_entity_decode($data['content'], ENT_QUOTES, "UTF-8"); ?></textarea>
</div>
</form>

<script>
CKEDITOR.replace( 'ckeditor' );
CKEDITOR.config.contentsCss = main_domain+'css/ckedit-custom.css' ;
</script>