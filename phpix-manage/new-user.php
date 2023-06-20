<br /><br /><?php 
if(!isset($_POST['email'])){$_POST['email']='';} 
if(!isset($_POST['pass'])){$_POST['pass']='';} 
if(!isset($_POST['cpass'])){$_POST['cpass']='';} 

$email = $_POST['email'];
$pass = $_POST['pass'];
$cpass = $_POST['cpass'];


if($email!=''){

if($pass != $cpass){
notify('<b>Error :</b> <b>password</b> and <b>confirm password</b> do not match.', 'newuser', 'danger');
} else {

$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `".$prefix."users` WHERE `email`='$email'"));

if($data['total']==0){

	mysqli_query($con, "INSERT INTO `".$prefix."users` (`id`, `email`, `pwd`, `time`)
	 VALUES (NULL, '$email', '$pass', '".time()."')");
	notify('<b>Success :</b> User <b>'.$email.'</b> was created successfully.', 'newuser', 'success');

	if(isset($_POST['sendmail'])){
	$msg ='This request was generated from PHPix gallery page from '.$domain.'phpix-album.php . After logging in you can change the password yourself. Your current password is : '.$pass;
	mail($email, "PHPix password", $msg);
	notify('<b>Info :</b> Email notification sent to the user.', 'newuser', 'warning');
	}

} else {

	notify('<b>Error :</b> '.$email.' already exists', 'newuser', 'danger');

}


}



}



 ?>
<div class="row">

<div class="col-xs-12 col-md-2"></div>
<div class="col-xs-12 col-md-8">
<?php 
if(!isset($notify['newuser'])){$notify['newuser']='';} 
echo $notify['newuser']; ?>
<form action="" autocomplete="off" method="post" enctype="multipart/form-data" class="form-horizontal">
  <div class="panel panel-primary">
    <div class="panel-heading text-center">Add User</div>
	<div class="panel-body">
    <div class="form-group">
      <label for="title" class="col-lg-2 control-label">Email</label>
      <div class="col-lg-10">
        <input value="<?php echo $email; ?>" type="email" name="email" class="form-control" id="email" placeholder="Your full email">
      </div>
    </div>

    <div class="form-group">
      <label for="title" class="col-lg-2 control-label">Password</label>
      <div class="col-lg-10">
        <input value="<?php echo $pass; ?>" type="password" name="pass" class="form-control" id="pass" placeholder="Desired password">
      </div>
    </div>

    <div class="form-group">
      <label for="title" class="col-lg-2 control-label">Confirm Password</label>
      <div class="col-lg-10">
        <input value="<?php echo $cpass; ?>" type="password" name="cpass" class="form-control" id="cpass" placeholder="Confirm password">
      </div>
    </div>

    <div class="form-group">
<label for="title" class="col-lg-2 control-label"> </label>
      <div class="col-lg-10">
        <input value="<?php echo $cpass; ?>" type="checkbox" checked name="sendmail" id="sendmail"> Send notification to the new user by email.
      </div>
    </div>

    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="submit" class="btn btn-primary">Create User</button>
      </div>
    </div>
	</div>
  </div>
</form>
</div>
<div class="col-xs-12 col-md-2"></div>

</div>
