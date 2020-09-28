<?php 

function create_file($name, $data){
if(!file_exists($name)){
file_put_contents($name, $data);
}
}

function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL.= "s";
    }
    $pageURL.= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL.= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL.= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

error_reporting(E_ALL & ~E_NOTICE);

if($_POST['sitekey']!='' && !file_exists('phpix-info.php')){

$con = @new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpwd'], $_POST['dbname']);
if( mysqli_connect_errno()){
$error = "Database Connection Failed: ".mysqli_connect_errno()." : ". mysqli_connect_error();
$installed = 2;
} else { // if db connection working

$xthumb_id = uniqid();
$domain = str_replace('phpix-install.php', '', curPageURL());

$phpix_data = '<?php 

session_start();
$notify = array();
error_reporting(E_ALL & ~E_NOTICE);

// for recaptcha v2 checkbox
$siteKey = "'.$_POST['sitekey'].'";
$secretKey = "'.$_POST['secretkey'].'";

date_default_timezone_set("Asia/Calcutta");
$domain = "'.$domain.'";
$gallery_domain = $domain;
$admin_url = $domain."phpix-manage.php?page=";
$website_name = "PHPix";
$con = new mysqli("'.$_POST['dbhost'].'","'.$_POST['dbuser'].'","'.$_POST['dbpwd'].'","'.$_POST['dbname'].'");
$prefix = "'.$_POST['dbprefix'].'";
$manager_mail = "'.$_POST['admmail'].'";
$date_format = "l, d-M-Y, h:i a";

$xthumb_secret = "'.$xthumb_id.'";

$admin_key = "'.$_POST['admpwd'].'";

$albumFILE = "phpix-album.php";

$default_gallery_settings = array(
	"thumb_width" => "200"	,
	"thumb_height" => "150"	,
	"thumb_dir" => "thumb"	,
	"image_dir" => "full"	,
	"temp_dir" => "temp"	,
);

if($_SESSION["gallery"]["thumb_width"]==""){
$_SESSION["gallery"] = $default_gallery_settings;
}


if (mysqli_connect_errno()){
echo "Failed to connect to MySQLi: " . mysqli_connect_error();
}';


// download package if not found
$zipFile = 'phpix-latest.zip';
if(!file_exists($zipFile)){
set_time_limit(3600);
//$response = file_get_contents("http://localhost/updates/packages/phpix-latest.zip");
$response = file_get_contents("https://raw.githubusercontent.com/phploaded/PHPix/master/phpix-latest.zip");
file_put_contents($zipFile, $response);
}

// unzip folder
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
}

// create phpix config file
@unlink('phpix-config.php');
create_file('phpix-config.php', $phpix_data);

// rename xthumb
rename("xthumb-rt37yp.php","xthumb-".$xthumb_id.".php");


// run database import
$mysql_file = 'phpix.sql';
$commands = file_get_contents($mysql_file);   
$commands = str_replace('http://localhost/familydb/', $domain, $commands);
$commands = str_replace('`phpix_', '`'.$_POST['dbprefix'], $commands);
mysqli_multi_query($con, $commands);
mysqli_close($con);
@unlink($mysql_file);




$installed = 1;
}
}


?><!DOCTYPE html>
<html>
<head>
<title>PHPix Installer</title>
<style type="text/css">
body{
margin:0;
padding:0;
background-color:#ddd;
font-family: verdana;
font-size: 12px;
color:#333;
}

.container{
margin: 50px auto;
max-width: 600px;
border: 1px solid gray;
background-color: #fff;
padding: 20px;
box-shadow: 0 0 3px #000;
}

.input-block{
margin: 10px 0;
}

.input-block:after{
clear: both;
display:block;
content:' ';
}

.container > h1{
text-align: center;
margin: 0 0 20px 0;
color:#666;
}

.input-block > label{
float: left;
width: 30%;
padding: 10px 0;
box-sizing: border-box;
}

.input-block > input{
float: left;
width: 70%;
display: block;
box-sizing: border-box;
border: 1px solid silver;
padding: 10px;
color:#333;
}

.input-block > input:focus{
border: 1px solid #000;
color:#000;
box-shadow:0 0 5px rgba(0, 0, 0, 0.5) inset;
}

.button-block{
text-align:center;
padding: 10px;
}

.button-block > button, .button-block > input{
border: 0;
background-color: teal;
color: #fff;
font: bold 16px arial;
padding: 10px 20px;
cursor:pointer;
}

fieldset{
margin-bottom:20px;
background-color: #eee;
}


legend{
border: 1px solid gray;
padding: 5px 10px;
background-color: #ccc;
color: #000;
}
</style>
</head>
<body>
<div class="container">
<h1>PHPix Installer</h1>
<?php if($installed == 1){ ?>
<p style="color:green;"><b>PHPix</b> was installed successfully!</p>
<p><a href="phpix-manage.php">Click here</a> for admin panel.</p>
<p><a href="phpix-album.php">Click here</a> to view your website.</p>
<?php } elseif($installed == 2) { ?>
<p style="color:red;"><b>PHPix</b> could not be installed!</p>
<p style="color:red;"><?php echo $error; ?></p>
<p>Press back button and edit to retry with new details.</p>
<?php } elseif(file_exists('phpix-info.php')) { ?>
<p style="color:red;"><b>PHPix</b> is already installed on this url. </p>
<p>If you are trying to update it to the latest version, first login to admin panel, then goto update tab and update it from there.</p>
<p>If old installation is not working, you may try tweaking various options in <b>phpix-config.php</b>. However, before you start editing, it is recommended to make a backup so that you can replace the file back later, if something goes wrong.</p>
<?php } else { ?>
<div class="form">
<form method="post" enctype="multipart/form-data">
<fieldset>
<legend>Database settings</legend>
<div class="input-block">
<label for="dbhost">Database Host</label>
<input required="required" value="<?php echo $_POST['dbhost'] ?>" type="text" name="dbhost" />
</div>

<div class="input-block">
<label for="dbname">Database Name</label>
<input required="required" value="<?php echo $_POST['dbname'] ?>" type="text" name="dbname" />
</div>

<div class="input-block">
<label for="dbuser">Database Username</label>
<input required="required" value="<?php echo $_POST['dbuser'] ?>" type="text" name="dbuser" />
</div>

<div class="input-block">
<label for="dbpwd">Database Password</label>
<input value="<?php echo $_POST['dbpwd'] ?>" type="password" name="dbpwd" />
</div>

<div class="input-block">
<label for="dbprefix">Table Prefix</label>
<input value="<?php echo $_POST['dbprefix'] ?>" type="text" name="dbprefix" />
</div>
<p>If you are going to use a database that is also being used with another application, you should specify a table prefix. If database is blank, you can leave this empty!</p>
</fieldset>

<fieldset>
<legend>Admin Panel settings</legend>
<div class="input-block">
<label for="admmail">Admin Email</label>
<input required="required" value="<?php echo $_POST['admmail'] ?>" type="email" name="admmail" />
</div>

<div class="input-block">
<label for="admpwd">Admin Password</label>
<input required="required" minlength="8" value="<?php echo $_POST['admpwd'] ?>" type="password" name="admpwd" />
</div>
<p>Admin email may be used for communication purpose. Please keep a strong password for admin and keep it a secret.</p>
</fieldset>


<fieldset>
<legend>ReCaptcha settings</legend>
<div class="input-block">
<label for="sitekey">ReCaptcha v2 SiteKey</label>
<input required="required" minlength="8" value="<?php echo $_POST['sitekey'] ?>" type="password" name="sitekey" />
</div>

<div class="input-block">
<label for="secretkey">ReCaptcha v2 SecretKey</label>
<input required="required" minlength="8" value="<?php echo $_POST['secretkey'] ?>" type="password" name="secretkey" />
</div>
<p>ReCaptcha is used to protect your PHPix server from hackers, bots and bad users. You must have a google account to access it. To create ReCaptcha(v2 checkbox), <a rel="nofollow" target="_blank" href="https://www.google.com/recaptcha/admin/create">Click here</a>. Fill the details and you will be shown <b>site key</b> and <b>secret key</b>, fill them in boxes above.</p>
</fieldset>

<div class="button-block">
<button type="submit">Install</button>
</div>
</form>
</div>
<?php } ?>
</div>
<script>

</script>
</body>
</html>