<?php 

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

if($_POST['sitekey']!='' && !file_exists('phpix-info.php')){

$con = new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpwd'], $_POST['dbname']);
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
$response = file_get_contents("https://github.com/phploaded/PHPix/archive/master.zip");
file_put_contents($zipFile, $response);
}

// run database stuff
mysqli_close($con);

run_query("CREATE TABLE `".$_POST['dbprefix']."access` (
  `id` int(11) NOT NULL,
  `uid` varchar(500) NOT NULL,
  `aid` varchar(500) NOT NULL
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
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query("INSERT INTO `".$_POST['dbprefix']."albums` (`id`, `slug`, `access`, `thumb`, `title`, `descr`, `created`, `updated`, `count`) VALUES
('5f5e2df33396d', 'sample-photos', 'public', '5f5e2df33396d.jpeg', 'Sample photos', 'This is a public sample gallery, preloaded with basic installation of PHPix. To delete this gallery, you have to first delete all photos in it. You can manage, edit or delete from admin management interface.', 1600007667, 1600007667, 5),
('5f5f182344a91', 'blank-album', 'public', '', 'Blank album', 'This album is kept blank to demonstrate how it looks when there are no uploaded photos and no cover photo.', 1600067619, 1600067619, 0);
");

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

run_query("INSERT INTO `".$_POST['dbprefix']."content` (`id`, `type`, `status`, `slug`, `title`, `content`, `sort`, `time`) VALUES
(73, 'note', 'Enabled', 'sample-note-1', 'Sample note 1', '&lt;div id=&quot;lipsum&quot;&gt;\r\n&lt;p&gt;&lt;img src=&quot;".$domain."full/5f5e2ca68060f.jpg&quot; style=&quot;float:left; height:546px; width:729px&quot; /&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam non finibus velit. Ut dignissim hendrerit pulvinar. Ut eu dui mauris. Quisque sit amet efficitur velit. Nulla tempor nisi tortor, nec porta velit convallis vel. Nunc dapibus lorem ut velit dapibus, eu porta nunc rutrum. Quisque tempor augue risus, eu pharetra lacus maximus at. Sed in ante non lectus hendrerit tincidunt. Ut ullamcorper, augue non commodo venenatis, sem purus eleifend nisl, a molestie sem risus non diam. Nam id tempus felis.&lt;/p&gt;\r\n\r\n&lt;p&gt;Integer id pretium ipsum. Cras dapibus porta ex vitae ultrices. Quisque in quam ullamcorper, vulputate magna sed, lacinia nunc. Nulla facilisi. Pellentesque vehicula nibh at ex commodo, vitae interdum lacus pretium. Morbi congue ligula sit amet dictum vestibulum. Aenean consequat eu nibh ut blandit. Aliquam maximus auctor auctor. Sed semper molestie sem, ut porttitor neque malesuada a. Vestibulum pretium velit eu ipsum finibus euismod. Mauris interdum ultricies dolor at cursus. Nunc libero quam, congue ut mattis nec, imperdiet sit amet diam.&lt;/p&gt;\r\n\r\n&lt;p&gt;Sed convallis leo arcu, rutrum auctor diam dignissim ac. Pellentesque in vulputate enim, in dapibus nunc. Donec sed vestibulum leo. Sed vel malesuada nibh, eget varius justo. Mauris eu nulla tempus, cursus dolor et, lacinia sem. Vivamus quis sapien turpis. In euismod, justo hendrerit egestas mattis, ipsum sem lacinia dui, vel mattis magna diam euismod libero.&lt;/p&gt;\r\n\r\n&lt;p&gt;Maecenas eget enim feugiat, pretium ligula sed, consequat quam. Donec id nunc id tellus scelerisque vehicula sed sed tellus. Etiam eleifend, tellus vitae egestas fringilla, odio lacus commodo turpis, at aliquam sapien ante sit amet velit. Nulla facilisi. In hac habitasse platea dictumst. Morbi aliquam tincidunt est, ut maximus mauris aliquet et. Quisque consequat bibendum mi, et porta sem ultricies a. Curabitur consequat vestibulum pharetra. Nulla interdum ullamcorper malesuada. Quisque non mauris in tortor varius ultricies a sed enim. Integer nec elit mauris.&lt;/p&gt;\r\n\r\n&lt;p&gt;Donec eu maximus massa. Integer vel lacus sit amet lectus sodales dictum. Quisque non velit finibus lectus pharetra viverra. Quisque viverra augue ut purus posuere pretium. Fusce iaculis eleifend augue, ut accumsan justo. Morbi vitae risus sit amet magna aliquam dignissim at consectetur neque. Etiam interdum est a ipsum ultrices aliquet. Etiam nunc eros, hendrerit sed maximus ac, maximus sit amet urna. Pellentesque nibh tortor, volutpat et blandit in, sodales nec massa. Duis egestas non magna sed fermentum. Curabitur vel porttitor magna. Ut in turpis eu diam pharetra sodales.&lt;/p&gt;\r\n\r\n&lt;p&gt;Suspendisse vel sapien tincidunt, feugiat est eget, tempus augue. Nam sed magna sagittis, hendrerit tortor nec, semper eros. Ut at eleifend mi. Duis dignissim diam in tempus interdum. Ut ullamcorper sollicitudin fermentum. Duis non ultrices ante, quis hendrerit felis. Nullam in mollis felis. Morbi quis nunc nec arcu placerat tristique. Suspendisse et turpis a libero volutpat maximus. Cras vitae dui a arcu accumsan imperdiet in eu sapien.&lt;/p&gt;\r\n\r\n&lt;p&gt;Aenean non augue orci. Pellentesque eget mattis odio. Maecenas in est quis metus volutpat bibendum. Nam sollicitudin velit at ultrices vestibulum. Nullam libero mi, fringilla at augue non, posuere porta nisl. Maecenas blandit lectus id maximus condimentum. Vestibulum ut velit blandit, molestie urna quis, tincidunt eros.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', 0, 1599982079),
(74, 'note', 'Enabled', 'sample-note-2', 'Sample note 2', '&lt;div id=&quot;lipsum&quot;&gt;\r\n&lt;p style=&quot;text-align:justify&quot;&gt;&lt;img src=&quot;".$domain."full/5f5e2eda10062.jpg&quot; style=&quot;float:right; height:422px; width:318px&quot; /&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla placerat lacus sit amet massa placerat fringilla. Duis auctor erat sed tortor tempus placerat. Donec consequat, velit semper mattis sollicitudin, augue est faucibus mi, vel dignissim justo sem id nisl. Aliquam ac placerat nisi, sed aliquet mi. Quisque tristique odio quis tincidunt consequat. Nunc sit amet justo in neque feugiat iaculis. Sed eget ligula odio. Duis euismod est vel finibus efficitur. Maecenas sollicitudin egestas congue. Aliquam egestas nunc et nulla dapibus egestas. Vestibulum eget nulla sagittis, scelerisque tortor vel, sodales velit. Duis commodo placerat arcu, a hendrerit dolor facilisis tincidunt. Donec convallis sed est sit amet facilisis. Mauris eget ligula luctus, imperdiet nulla ut, efficitur dolor. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Curabitur mattis non leo id tristique.&lt;/p&gt;\r\n\r\n&lt;p style=&quot;text-align:justify&quot;&gt;Nulla facilisi. Curabitur vulputate, felis eget interdum placerat, libero orci facilisis purus, a porttitor nisl urna vel lectus. Nam vitae mi in libero lacinia dapibus vel vel nibh. Ut consectetur risus sit amet ligula mollis facilisis. Ut viverra turpis sed quam mattis varius. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Integer suscipit ullamcorper lectus vitae mattis. Mauris porttitor lectus sit amet convallis placerat. In vel mi ut augue ornare aliquam. Curabitur ultrices tincidunt nulla, viverra ultricies lorem dignissim eget. Proin rutrum massa ut leo auctor commodo.&lt;/p&gt;\r\n\r\n&lt;p style=&quot;text-align:justify&quot;&gt;Sed a magna magna. Suspendisse nec augue eget lacus aliquam hendrerit a a nibh. Sed vitae orci vitae dolor auctor tempor id vitae justo. Vestibulum in purus dolor. Integer sit amet neque tempor, fringilla turpis sit amet, ullamcorper felis. Mauris pellentesque sem id dapibus pharetra. Phasellus non molestie ex, ut iaculis nunc. Morbi neque augue, elementum id fringilla non, auctor id neque. Suspendisse porta enim odio, a tincidunt nulla placerat a. Mauris tincidunt a elit vitae mattis. Curabitur sed purus lobortis, gravida massa eu, dapibus nisl. Donec commodo vehicula dolor ac consectetur. Fusce ullamcorper dui ipsum, non laoreet nisl ultrices elementum. Mauris et ligula quis arcu maximus lobortis nec vel risus.&lt;/p&gt;\r\n\r\n&lt;p style=&quot;text-align:justify&quot;&gt;Integer fermentum risus sem, ut commodo neque maximus id. Nulla vitae nunc ut purus rutrum sollicitudin et ornare risus. Quisque maximus fermentum lectus in laoreet. Quisque id risus risus. Duis placerat massa id nisi eleifend, in interdum elit blandit. Pellentesque laoreet diam sed sagittis viverra. In aliquet metus justo, quis lacinia ligula sagittis ut.&lt;/p&gt;\r\n\r\n&lt;p style=&quot;text-align:justify&quot;&gt;Vivamus quam massa, elementum vel vestibulum in, consequat eu purus. Donec egestas et magna vel malesuada. Aliquam aliquet justo ac orci mollis mollis. Quisque fermentum sodales accumsan. Sed tellus quam, interdum ut turpis at, consequat dictum ipsum. Vestibulum vestibulum euismod sem, id laoreet leo vehicula a. Cras in venenatis nisi. Sed semper vulputate quam finibus eleifend. Maecenas fringilla metus nec sapien aliquet ornare. Etiam purus est, iaculis a pellentesque et, lacinia nec nulla. Suspendisse laoreet lorem id nulla euismod auctor. Pellentesque vitae velit enim. Maecenas vitae ultrices felis, at blandit nunc. Etiam sollicitudin mauris vel justo finibus vestibulum. Donec euismod mattis lorem quis laoreet.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', 0, 1599982114),
(75, 'note', 'Enabled', 'sample-note-3', 'Sample note 3', '&lt;div id=&quot;lipsum&quot;&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tempor tellus scelerisque magna gravida vulputate. Phasellus tempus leo vel iaculis pretium. Donec fringilla sollicitudin felis, ut fringilla velit dictum eu. Nullam leo est, facilisis non volutpat id, tempus sed justo. Nam interdum egestas consectetur. Nullam fringilla dapibus elit, vitae fringilla diam cursus et. Proin massa diam, porttitor non turpis et, hendrerit posuere diam. Proin porttitor porttitor mi, sit amet pellentesque mauris. Nulla hendrerit, odio finibus interdum vulputate, lacus tortor placerat turpis, id elementum ante nulla vitae lorem. Nullam cursus purus sodales urna ornare venenatis.&lt;/p&gt;\r\n\r\n&lt;p&gt;Donec eu leo vel eros mollis euismod. Nunc lacinia massa metus, maximus interdum mauris venenatis eget. Morbi consequat velit ac nisl mollis porttitor. Fusce accumsan elementum commodo. Etiam venenatis aliquam magna at fringilla. Mauris hendrerit lacus bibendum mauris ullamcorper, quis blandit odio ornare. Sed blandit nibh non semper maximus. Duis sodales aliquam mauris, eu tincidunt urna luctus sed. Cras dapibus ex at augue scelerisque, vel hendrerit nisi dapibus. Quisque tincidunt libero volutpat congue scelerisque.&lt;/p&gt;\r\n\r\n&lt;p&gt;In scelerisque, ipsum id auctor facilisis, lorem magna convallis velit, eleifend rhoncus arcu urna in est. Nulla velit est, finibus sit amet fringilla at, ullamcorper vel nunc. Curabitur laoreet neque tempus felis bibendum tincidunt. Nunc ut congue sapien. Duis bibendum, sem et maximus auctor, lectus magna iaculis enim, id commodo eros leo elementum dolor. Nullam non convallis eros, tempus rutrum risus. Nunc iaculis euismod dolor, et volutpat dui imperdiet pharetra. Ut vehicula pharetra libero, eget tempor eros commodo id. Maecenas dignissim cursus tortor, eget porttitor diam venenatis ac.&lt;/p&gt;\r\n\r\n&lt;p&gt;Nunc congue leo in augue porta scelerisque. Integer aliquam rutrum magna interdum ultrices. Etiam eget magna bibendum, viverra sem vel, viverra risus. Praesent tellus ipsum, pulvinar sed dictum maximus, luctus sed ex. Duis vehicula volutpat laoreet. Maecenas urna ante, viverra sit amet lacus eget, laoreet cursus turpis. Ut sit amet tortor at libero sollicitudin congue. Cras sed est vitae libero sodales cursus. Sed condimentum vestibulum neque in vestibulum. In gravida sodales ligula ut consectetur. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Phasellus ipsum sem, varius sit amet lectus quis, tempus dapibus dolor. Mauris suscipit, turpis ut vulputate sodales, dolor felis dignissim urna, ut luctus enim sapien commodo diam.&lt;/p&gt;\r\n\r\n&lt;p&gt;Aenean in enim eleifend, varius metus non, pellentesque eros. Quisque at ante iaculis, blandit odio et, fermentum ex. Proin nunc dolor, imperdiet quis pharetra et, accumsan ullamcorper tellus. Proin eget interdum diam. Integer luctus nulla at mauris scelerisque dictum. In id congue dui. Cras in diam at nibh condimentum imperdiet. Aliquam eu imperdiet quam. Vestibulum tempus orci nec diam molestie molestie. In sit amet est felis. Nulla euismod ex id dui eleifend, a fermentum est pulvinar. Etiam et ultricies nisl, at ultrices ligula. Praesent sit amet dapibus purus, et tincidunt mauris. Nulla vel orci ornare, egestas orci ut, facilisis tortor. Sed vitae dolor quam.&lt;/p&gt;\r\n\r\n&lt;p&gt;Etiam congue sem eu est malesuada, vitae lobortis odio semper. In hac habitasse platea dictumst. Suspendisse at quam vel ipsum condimentum hendrerit eget et nisl. Praesent aliquam ex risus, at elementum risus aliquam vitae. Morbi non gravida erat. In fermentum elit eget dui viverra, nec gravida purus gravida. Aliquam erat volutpat. Sed vel rutrum sem. Nam elementum augue sed ornare suscipit. Nunc id malesuada nisl. Quisque auctor mattis elit et maximus. Cras fringilla tincidunt sodales. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam nisl tortor, faucibus eget porttitor faucibus, mollis id augue. Nullam dolor dui, gravida et porttitor et, convallis in ante.&lt;/p&gt;\r\n\r\n&lt;p&gt;Etiam et tincidunt orci, eget faucibus diam. Praesent at dui vitae mauris ultricies pharetra eu ac ligula. Pellentesque et dui ultrices, scelerisque magna quis, pharetra arcu. Nunc id urna id velit vestibulum maximus nec nec mauris. Aenean cursus sodales placerat. Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras quis luctus nisl. Cras nulla libero, semper ac lorem id, pretium sodales justo. Ut in mauris neque. Proin elementum eget nulla id sodales. Etiam sit amet molestie dui. Phasellus id tortor sem. Sed eu libero a sem aliquet suscipit. Sed id urna sapien. Suspendisse urna lectus, convallis viverra molestie vitae, mollis vel libero. Aenean faucibus placerat aliquet.&lt;/p&gt;\r\n\r\n&lt;p&gt;Suspendisse facilisis vulputate odio nec posuere. In hac habitasse platea dictumst. Quisque tincidunt tincidunt libero, eu maximus sem facilisis quis. In odio lectus, bibendum a sem vel, pharetra sodales nisl. Curabitur laoreet non dui at iaculis. Curabitur vitae efficitur ante, vitae consequat nibh. Quisque lorem risus, facilisis eget ante non, facilisis pretium urna.&lt;/p&gt;\r\n\r\n&lt;p&gt;Pellentesque porta augue vitae nibh gravida, quis placerat neque vulputate. Quisque commodo purus ut massa suscipit, sed fringilla neque tempus. Maecenas placerat dui elit, sit amet faucibus arcu blandit id. Quisque eu magna nisi. Fusce pretium diam tellus, vitae dictum nunc sodales auctor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur convallis libero eget mollis ultricies. Nullam tincidunt nisl tortor, sed interdum mi bibendum vel. Etiam a dolor quis mi commodo maximus. Curabitur pharetra, velit eu pharetra aliquam, nisi sapien porta ipsum, at blandit nisl diam ut augue. Donec nibh ante, convallis a purus non, ultricies lacinia elit. Mauris fermentum neque vitae nibh sagittis, in porta urna convallis. Suspendisse porttitor, erat sed facilisis ullamcorper, sapien sapien pharetra augue, ac aliquet nibh risus eu urna. Suspendisse vitae venenatis dui.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', 0, 1599982116);");

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

run_query( "CREATE TABLE `".$_POST['dbprefix']."uploads` (
  `id` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
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

run_query( "INSERT INTO `".$_POST['dbprefix']."uploads` (`id`, `type`, `ctype`, `title`, `folder`, `caption`, `tags`, `spots`, `url`, `thumb`, `time`, `uid`, `size`) VALUES
('5f5ebf7171b4e', 'jpg', 'upload', 'IMG 20140103 165051', '5f5e2df33396d', 'IMG 20140103 165051', '', '', '5f5ebf7171b4e.jpg', '5f5ebf7171b4e.jpg', 1600044913, '1', 183912),
('5f5ebfe4bb7a0', 'jpg', 'upload', 'IMG 20180326 075745', '5f5e2df33396d', 'IMG 20180326 075745', '', '', '5f5ebfe4bb7a0.jpg', '5f5ebfe4bb7a0.jpg', 1600045028, '1', 155017),
('5f5ec04da8b6b', 'jpg', 'upload', 'IMG 20191231 115503', '5f5e2df33396d', 'IMG 20191231 115503', '', '', '5f5ec04da8b6b.jpg', '5f5ec04da8b6b.jpg', 1600045133, '1', 175441),
('5ef1cfe6dbd2c-5ef583cec42d2', 'png', 'upload', '5ef1cfe6dbd2c', '', '5ef1cfe6dbd2c', '', '', '".$domain."mlib-uploads/full/5ef1cfe6dbd2c-5ef583cec42d2.png', '5ef1cfe6dbd2c-5ef583cec42d2.png', 1593148366, '', 1),
('5ef1cfea839e3-5ef5841f71b21', 'jpg', 'upload', '5ef1cfea839e3', '', '5ef1cfea839e3', '', '', '".$domain."mlib-uploads/full/5ef1cfea839e3-5ef5841f71b21.jpg', '5ef1cfea839e3-5ef5841f71b21.jpg', 1593148447, '', 1),
('cute-nicknames-5ef58506ea016', 'jpg', 'upload', 'cute-nicknames', '', 'cute-nicknames', '', '', '".$domain."mlib-uploads/full/cute-nicknames-5ef58506ea016.jpg', 'cute-nicknames-5ef58506ea016.jpg', 1593148682, '', 1),
('5ef5a3b9142b0', 'jpg', 'upload', 'cute-nicknames', '', 'cute-nicknames', '', '', '".$domain."full/5ef5a3b9142b0.jpg', '5ef5a3b9142b0.jpg', 1593156540, '', 1),
('5f5e2ca68060f', 'jpg', 'upload', 'IMG 20180326 075021', '', 'IMG 20180326 075021', '', '', '5f5e2ca68060f.jpg', '5f5e2ca68060f.jpg', 1600007334, '1', 296260),
('5f5e2eda10062', 'jpg', 'upload', 'IMG 20180326 075541', '', 'IMG 20180326 075541', '', '', '5f5e2eda10062.jpg', '5f5e2eda10062.jpg', 1600007898, '1', 275869),
('5f5ebf57ce78b', 'jpg', 'upload', 'IMG 20140303 141909', '5f5e2df33396d', 'IMG 20140303 141909', '', '{\"5f6edd47d6cc9\":{\"pic\":\"5f5ebf57ce78b\",\"css\":\"top:48.046875%;left:54.95958853783982%;\",\"txt\":\"Pea flower\",\"clas\":\"xtag-pos-left-top\"}},', '5f5ebf57ce78b.jpg', '5f5ebf57ce78b.jpg', 1600044888, '1', 332291),
('yt[6stlCkUDG_s]5f719f53d643a', 'jpeg', 'youtube', 'Youtube Videos example', '5f5e2df33396d', 'Youtube videos can be added by providing their URL.', '', '', 'yt[6stlCkUDG_s]5f719f53d643a.jpeg', 'yt[6stlCkUDG_s]5f719f53d643a.jpeg', 1601281876, '1', 167630);");

run_query( "CREATE TABLE `".$_POST['dbprefix']."users` (
  `id` int(11) NOT NULL,
  `email` varchar(500) NOT NULL,
  `pwd` varchar(500) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."access`  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."albums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."content`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."import`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."uploads`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."users`
  ADD PRIMARY KEY (`id`);");

run_query( "ALTER TABLE `".$_POST['dbprefix']."access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."import`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;");

run_query( "ALTER TABLE `".$_POST['dbprefix']."users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");


// unzip folder
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
}


xcopy('PHPix-master/' , './' );

rrmdir('PHPix-master/');


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