<?php 

require_once('phpix-media-functions.php');

function randomColor(){
    $result = array('rgb' => array(), 'hex' => '');
    foreach(array('r', 'b', 'g') as $col){
        $rand = mt_rand(0, 255);
        $result['rgb'][$col] = $rand;
        $dechex = dechex($rand);
        if(strlen($dechex) < 2){
            $dechex = '0' . $dechex;
        }
        $result['hex'] .= $dechex;
    }
    return $result;
}


function gal_enqueue($url){
global $gallery_domain;
return $gallery_domain.''.$url.'?t='.filemtime($url);
}


function gal_display_albums($parent = '', $key = ''){
//	print_r($_GET);
global $con, $prefix, $gallery_domain, $date_format, $albumFILE;

if($parent=='' && $key == ''){ // no search, display top folders
$esql = " parent = '' AND";
} elseif($parent!='' && $key == ''){ // no search, display folder content
$esql = " parent = '$parent' AND";
} else { // search in parent or subfolders
$esql = "";
}

$classes = array(
"imghvr-fade", "imghvr-push-up", "imghvr-push-down", "imghvr-push-left",
"imghvr-push-right", "imghvr-slide-up", "imghvr-slide-down", "imghvr-slide-left",
"imghvr-slide-right", "imghvr-reveal-up", "imghvr-reveal-down", "imghvr-reveal-left",
"imghvr-reveal-right", "imghvr-hinge-up", "imghvr-hinge-down", "imghvr-hinge-left", 
"imghvr-hinge-right", "imghvr-flip-horiz", "imghvr-flip-vert", "imghvr-flip-diag-1",
"imghvr-flip-diag-2", "imghvr-shutter-out-horiz", "imghvr-shutter-out-vert", 
"imghvr-shutter-out-diag-1", "imghvr-shutter-out-diag-2", "imghvr-shutter-in-horiz",
"imghvr-shutter-in-vert", "imghvr-shutter-in-out-horiz", "imghvr-shutter-in-out-vert",
"imghvr-shutter-in-out-diag-1", "imghvr-shutter-in-out-diag-2", "imghvr-fold-up",
"imghvr-fold-down", "imghvr-fold-left", "imghvr-fold-right", "imghvr-zoom-in",
"imghvr-zoom-out", "imghvr-zoom-out-up", "imghvr-zoom-out-down", "imghvr-zoom-out-left",
"imghvr-zoom-out-right", "imghvr-zoom-out-flip-horiz", "imghvr-zoom-out-flip-vert",
"imghvr-blur"
);




    if (!isset($_SESSION['PHPix'])) $_SESSION['PHPix'] = '';
    if (!isset($_SESSION['phpixuser'])) $_SESSION['phpixuser'] = '';

    if ($_SESSION['PHPix'] !== '') {
        $sql = "SELECT * FROM `".$prefix."albums` WHERE".$esql." (`access`='public' OR `access`='private')";
    } elseif ($_SESSION['phpixuser'] === '') {
        $sql = "SELECT * FROM `".$prefix."albums` WHERE".$esql." `access`='public'";
    } else {
        $tql = mysqli_query($con, "SELECT * FROM `".$prefix."access` WHERE `uid`='".$_SESSION['phpixuser']."'");
        $nsql = '';
        while ($row = mysqli_fetch_assoc($tql)) {
            $nsql .= " OR `id`='".$row['aid']."'";
        }
        $sql = "SELECT * FROM `".$prefix."albums` WHERE".$esql." (`access`='public'".$nsql.")";
    }

    // Add search condition if key is not empty
    if ($key !== '') {
        $sql .= " AND (`title` LIKE '%$key%' OR `descr` LIKE '%$key%')";
    }
    $sql .= " ORDER BY `title` ASC";

    echo '<div class="album-ctr"><div class="album-list ximghvr">';
	
// echo $sql; 

$data = mysqli_query($con, $sql);
$totdata = mysqli_num_rows($data);
    if ($totdata === 0) {
        if($parent=='' && $key==''){
        echo '<div class="album-empty-state"><h2>No albums to display</h2><div class="album-descr">There are no albums available right now.</div></div></div></div><script>album_search_text(0);</script>';
        } else {
        echo '</div></div><script>album_search_text(0);</script>';
        }
        return;
    }

while($row = mysqli_fetch_assoc($data))
{ 

$folders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM `".$prefix."albums` WHERE `parent`='".$row['id']."'"));


if($row['thumb']!=''){
$photo = '<div class="album-photo-wrap"><img class="loadslow" src="'.$gallery_domain.'css/point.png" xsrc="'.gal_enqueue('cover/'.$row['thumb']).'"></div>';
} else {
$photo = '<div><img class="loadslow" src="'.$gallery_domain.'css/point.png" xsrc="'.$gallery_domain.'phpix-libs/images/holder.svg"></div>';
}

if($row['count']==1){$pictext='Photo';} else {$pictext='Photos';}
if($folders['total']==1){$ftext='Folder';} else {$ftext='Folders';}

if($row['count']==0 && $folders['total']==0){
$ptext = 'Empty';
} elseif($row['count']==0 && $folders['total']!=0){
$ptext = $folders['total'].' '.$ftext;
} elseif($row['count']!=0 && $folders['total']==0){
$ptext = $row['count'].' '.$pictext;
} else {
$ptext = $row['count'].' '.$pictext.', '.$folders['total'].' '.$ftext;
}

$key = array_rand($classes,1);
unset($color);
$color = randomColor();
//print_r($color);

if($row['uid']==1){$user = 'admin';} else {

$udata = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."users` WHERE `email`='".$row['uid']."'"));
$user = $udata['name'];
}

echo '<div class="album-box"><div class="album-ctr album-type-'.$row['access'].'"><figure style="background-color:#'.$color['hex'].'" xclass="imghvr-reveal-left" class="'.$classes[$key].'">
'.$photo.'
<div class="album-preview"><div class="album-preview-title">'.$row['title'].'<span>'.$ptext.'</span></div></div>
<figcaption>
<div class="album-info">
<ul class="album-date">
<li>'.$row['count'].' photos - <b onclick="gal_gotoURL(\''.$gallery_domain.''.$albumFILE.'?aid='.$row['id'].'\')" xurl="'.$row['id'].'">VIEW ALBUM</b></li>
<li>Last updated on '.date($date_format, $row['updated']).'</li>
</ul>
<p>Managed by '.$user.'</p>
</div>
</figcaption>
</figure></div></div>';

}

echo'</div></div>
<script>
album_search_text('.$totdata.');
album_loadslow();
</script>';

}



function convertToReadableSize($size){
  $base = log($size) / log(1024);
  $suffix = array("", " KB", " MB", " GB", " TB");
  $f_base = floor($base);
  return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
}

function gal_html_tags($text, $prefix_url){
$text = str_replace(' ', '', $text);
$tags = explode(',', $text);

$str = '';
foreach($tags as $tag){
	if($tag!=''){
	$str = $str.'<li><a href="'.$prefix_url.''.$tag.'">'.$tag.'</a></li>';
	}
}

if($str==''){$str='<i>No tags</i>';}

return $str;
} 

function get_thumb($path, $quality='full'){
	$thumb_file_name = phpix_thumb_filename($path);
	phpix_prepare_thumb_file($path, 90);

	if($quality!='full'){
	phpix_prepare_media_file($quality, $path, 80);
	}

	return $thumb_file_name;
}




function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
           rrmdir($dir."/".$object); 
        else unlink   ($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
 }


function quick_paginate($numrows){

if($_GET['ipp']>0){
$per_page = $_GET['ipp'];
} else {
$per_page = 10;
}

if($_GET['pagenumber']>0){
$paginate['current'] = $_GET['pagenumber'];
$start = ($_GET['pagenumber']-1)*$per_page;
} else {
$paginate['current'] = 1;
$start = 0;
}

$paginate[start] = $start;
$paginate[per_page] = $per_page;

return $paginate;
}
