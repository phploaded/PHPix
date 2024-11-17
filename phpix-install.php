<?php 

$installed = 0;

// Function to check if a PHP extension is loaded
function checkExtension($extensionName) {
    if (extension_loaded($extensionName)) {
        return true;
    } else {
        return false;
    }
}

   // Function to remove folders and files 
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
                if ($file != "." && $file != "..") rrmdir("$dir/$file");
            rmdir($dir);
        }
        else if (file_exists($dir)) unlink($dir);
    }

function xcopy($src, $dest) {
    foreach (scandir($src) as $file) {
        if (!is_readable($src . '/' . $file)) continue;
        if (is_dir($src .'/' . $file) && ($file != '.') && ($file != '..') ) {
            mkdir($dest . '/' . $file);
            xcopy($src . '/' . $file, $dest . '/' . $file);
        } else {
            copy($src . '/' . $file, $dest . '/' . $file);
        }
    }
}

function create_file($name, $data){
if(!file_exists($name)){
file_put_contents($name, $data);
}
}

function run_query($qry){
$xcon = new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpwd'], $_POST['dbname']);
mysqli_query($xcon, $qry);
mysqli_close($xcon); 
// echo '<pre>'.$qry.'</pre>'; 
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

if(checkExtension('zip')==false){
$error = "<b>zip</b> extention is not enabled on your server. This is needed for unzipping and zipping files.<br>";
$installed = 2;
}
if(checkExtension('gd')==false){
$error = $error."<b>gd</b> extention is not enabled on your server. This is needed to edit and resize images.";
$installed = 2;
}

if(!isset($_POST['sitekey'])){$_POST['sitekey']='';} 
if($_POST['sitekey']!='' && !file_exists('phpix-info.php')){

$con = new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpwd'], $_POST['dbname']);
if( mysqli_connect_errno()){
$error = "Database Connection Failed: ".mysqli_connect_errno()." : ". mysqli_connect_error();
$installed = 2;
} else { // if db connection working

$xthumb_id = uniqid();
$domain = str_replace('phpix-install.php', '', @curPageURL());

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

if(!isset($_SESSION["gallery"]["thumb_width"])){
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
$response = file_get_contents("https://github.com/phploaded/PHPix/archive/master.zip");
file_put_contents($zipFile, $response);
}

// run database stuff
mysqli_close($con);

run_query("CREATE TABLE `".$_POST['dbprefix']."access` (
  `id` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'album',
  `aid` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query("CREATE TABLE `".$_POST['dbprefix']."albums` (
  `id` varchar(20) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `access` varchar(2000) NOT NULL DEFAULT 'public',
  `thumb` varchar(100) NOT NULL,
  `title` varchar(250) NOT NULL,
  `descr` varchar(5000) NOT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `parent` varchar(20) NOT NULL,
  `uid` varchar(100) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query("CREATE TABLE `".$_POST['dbprefix']."content` (
  `id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'Disabled',
  `slug` varchar(5000) NOT NULL,
  `title` varchar(5000) NOT NULL,
  `content` longtext NOT NULL,
  `sort` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query("CREATE TABLE `".$_POST['dbprefix']."dirs` (
  `id` varchar(20) NOT NULL,
  `sort` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `files` int(11) NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query("INSERT INTO `".$_POST['dbprefix']."dirs` (`id`, `sort`, `time`, `files`, `size`) VALUES
('cover', 0, 1603875150, 0, 0),
('fhd', 2, 1603875156, 0, 0),
('full', 1, 1603875152, 0, 0),
('hd', 3, 1603875157, 0, 0),
('qhd', 4, 1603875157, 0, 0),
('thumb', 5, 1603875159, 0, 0);");

run_query("CREATE TABLE `".$_POST['dbprefix']."import` (
  `id` int(11) NOT NULL,
  `title` varchar(5000) NOT NULL,
  `content` varchar(5000) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

run_query("INSERT INTO `".$_POST['dbprefix']."import` (`id`, `title`, `content`, `time`) VALUES
(2, 'Full URL for multiple lines', '%%url%% [%%fullsize%%]&lt;br /&gt;', 1420712613),
(3, 'Non image files as downloads', '&lt;p class=&quot;demo-download&quot;&gt;&lt;img src=&quot;%%thumb%%&quot; /&gt; &lt;b&gt;%%title%% (%%fullsize%%, %%type%% file)&lt;/b&gt; &lt;a href=&quot;%%url%%&quot;&gt;DOWNLOAD&lt;/a&gt;&lt;/p&gt;', 1420714475),
(6, 'thumbs', '&lt;a href=&quot;%%url%%&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;%%thumb%%&quot; /&gt;&lt;/a&gt;', 1420784828),
(8, 'Full img tag', '&lt;img src=&quot;%%domain%%full/%%url%%&quot;&gt;', 1421235450),
(9, 'Thumbnail img tag', '&lt;img src=&quot;%%domain%%thumb/%%thumb%%&quot;&gt;', 1421494938);");

run_query( "CREATE TABLE `".$_POST['dbprefix']."packages` (
  `id` int(11) NOT NULL,
  `dir` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query( "CREATE TABLE `".$_POST['dbprefix']."uploads` (
  `id` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `access` varchar(20) NOT NULL DEFAULT 'public',
  `ctype` varchar(50) NOT NULL DEFAULT 'upload',
  `title` varchar(500) NOT NULL,
  `folder` varchar(100) NOT NULL,
  `caption` varchar(5000) NOT NULL,
  `tags` varchar(2000) NOT NULL,
  `spots` text NOT NULL,
  `url` varchar(5000) NOT NULL,
  `thumb` varchar(5000) NOT NULL,
  `time` int(11) NOT NULL,
  `uid` varchar(1000) NOT NULL,
  `size` int(20) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

run_query( "CREATE TABLE `".$_POST['dbprefix']."users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT 'no_name',
  `email` varchar(50) NOT NULL,
  `pwd` varchar(50) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."access`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."content`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."dirs`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."import`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dir` (`dir`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."uploads`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."users`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."import`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;");

run_query( "CREATE TABLE IF NOT EXISTS `".$_POST['dbprefix']."spots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  `title` varchar(1000) NOT NULL DEFAULT 'no_title',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// unzip folder
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
}


@xcopy('PHPix-master/' , './' );

@rrmdir('PHPix-master/');


// create phpix config file
@unlink('phpix-config.php');
create_file('phpix-config.php', $phpix_data);

// rename xthumb
rename("xthumb-rt37yp.php","xthumb-".$xthumb_id.".php");




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


.spinner {
  -webkit-animation: rotate 2s linear infinite;
          animation: rotate 2s linear infinite;
  z-index: 2;
  position: absolute;
  top: 50%;
  left: 50%;
  margin: -25px 0 0 -25px;
  width: 50px;
  height: 50px;
}
.spinner .path {
  stroke: #93bfec;
  stroke-linecap: round;
  -webkit-animation: dash 1.5s ease-in-out infinite;
          animation: dash 1.5s ease-in-out infinite;
}

@-webkit-keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}
@-webkit-keyframes dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}
@keyframes dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}

.spin-ctr{
height:60px;
position:relative;
}

#installing{display:none;}
</style>
<script type="text/javascript">
function showSpinner(){
document.getElementById("installing").style.display = "block";
document.getElementById("mainctr").style.display = "none";
return true;
}
</script>
</head>
<body>
<div id="installing" class="container">
<h1>Installing PHPix....</h1>
<div class="spin-ctr">
<svg class="spinner" viewBox="0 0 50 50">
  <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
</svg>
</div>
<p><span style="color:red;">Please do not close this window or browser until installation is complete.</span> 
If the installation stops because of PHP script timeout, then this installer wont work for you. Instead, you can simply <a target="_blank" href="https://github.com/phploaded/phpix-packages/tree/main/phpix-full">download the latest zip file manually</a> by yourself from github, extract the contents of the zip file to your server then try again.</p>
</div>
<div id="mainctr" class="container">
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
<form method="post" action="" onsubmit="return showSpinner()" enctype="multipart/form-data">
<fieldset>
<legend>Database settings</legend>
<div class="input-block">
<label for="dbhost">Database Host</label>
<?php 
if(!isset($_POST['dbhost'])){$_POST['dbhost']='';} 
if(!isset($_POST['dbname'])){$_POST['dbname']='';} 
if(!isset($_POST['dbuser'])){$_POST['dbuser']='';} 
if(!isset($_POST['dbpwd'])){$_POST['dbpwd']='';} 
if(!isset($_POST['dbprefix'])){$_POST['dbprefix']='';} 
if(!isset($_POST['admmail'])){$_POST['admmail']='';} 
if(!isset($_POST['admpwd'])){$_POST['admpwd']='';} 
if(!isset($_POST['secretkey'])){$_POST['secretkey']='';} 
 ?>
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