<?php 

if(isset($_POST['pwd'])){

$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']); 
$responseData = json_decode($verifyResponse); 

if($responseData->success){
if($_POST['pwd']==$admin_key){
$_SESSION[$website_name]=$_POST['pwd'];
$error = '<div class="alert alert-dismissible alert-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<p><b>Success!</b> login successful! Redirecting...</p>
</div><script>document.location.href="phpix-manage.php";</script>';
} else {
$error = '<div class="alert alert-dismissible alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<p><b>Error!</b> Wrong password! Please retry!</p>
</div>';
}
} else {
$error = '<div class="alert alert-dismissible alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<p><b>Error!</b> ReCaptcha Failed! Please retry!</p>
</div>';
}

}
?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
<?php echo $error; ?>
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" action="phpix-manage.php?page=login" method="post">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Admin Key" name="pwd" type="password" value="">
                                </div>
                                <div class="form-group">
                                    <div style="margin:0 auto;" class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <button type="submit" class="btn btn-lg btn-success btn-block"><i class="fa fa-lock"></i> Login</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>