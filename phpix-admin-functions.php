<?php 

function admin_only(){
global $phpix_user;
if($phpix_user!=1){die('<h4 class="text-danger"><br />Admin leavel access needed. You are not authorised to access this area!</h4>');}
}

function file_mod($path){
return filemtime($path);
}

function xsize($size){
if($size==0){
return '0 B';
} else {
$base = log($size) / log(1024);
$suffix = array(" B", " Kb", " Mb", " Gb", " Tb");
$f_base = floor($base);
return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
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

function xdate($time, $format = 'global', $livestamp = 'both', $before_livestamp = ' (', $after_livestamp = ')'){
if($format=='global'){
global $date_format;
} else {
$date_format = $format;
}

if($livestamp=='both'){
return date($date_format, $time).''.$before_livestamp.'<span data-livestamp="'.$time.'"></span>'.$after_livestamp;
} elseif($livestamp=='static'){
return date($date_format, $time);
} else {
return '<span data-livestamp="'.$time.'"></span>';
}
}

function formatTime($xtime){
$hour = 60*60;
$day = 60*60*24;
$month = 60*60*24*30.5;
$year = 60*60*24*365.25;
if($xtime<0){$str = (formatTime((-1)*$xtime)).' ago';}
if($xtime==0){$str = '';}
if($xtime>0 && $xtime<60){$str = round($xtime, 1) .' Seconds';}
if($xtime>=60 && $xtime<$hour){$str = round(($xtime/60), 1) .' Minutes';}
if($xtime>=$hour && $xtime<$day){$str = round(($xtime/$hour), 1) .' Hours';}
if($xtime>=$day && $xtime<$month){$str = round(($xtime/$day), 1) .' Days';}
if($xtime>=$month && $xtime<$year){$str = round(($xtime/$month), 1) .' Months';}
if($xtime>=$year){$str = round(($xtime/$year), 1) .' Years';}
return $str;
}

function sadmin_title($title){
echo'<div class="col-lg-12"><div class="row"><h2 class="page-header">'.$title.'</h2></div></div>';
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


function clean_text($text){
$text = htmlentities($text, ENT_QUOTES, "UTF-8");
$text = str_replace("'", "''", $text);
return $text;
}

/* creates notifications on various predefined indexes for later displayed */
function notify($text, $index, $class='success'){
global $notify;
$notify[$index] = $notify[$index].'<div class="alert alert-dismissible alert-'.$class.'">
<button type="button" class="close" data-dismiss="alert">×</button>
<p>'.$text.'</p>
</div>';
}

function admin_enqueue($url){
global $gallery_domain;
return $gallery_domain.''.$url.'?t='.filemtime($url);
}

function slugify($text){
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}


/* creates a proper list of emails from input text. Removed unwanted chars, lines, spaces, etc */
function clean_emails($emails){
$emails = str_replace("\r\n", " ", $emails);
$emails = str_replace("\n", " ", $emails);
$emails = str_replace("  ", " ", $emails);
$emails = str_replace(" ", ",", $emails);

$ids = explode(",", $emails);

foreach($ids as $key => $email){
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
unset($ids[$key]);
}
}

$emails = implode(",", $ids);

return $emails;
}





// reads all folder names and outputs as html-select-options
function dir_to_html($xdir, $type = 'dir'){

if($type=='dir'){
$dirs = array_filter(glob($xdir . '/*' , GLOB_ONLYDIR), 'is_dir');
} else {
$dirs = glob($xdir . '/*.'.$type);
}

$dirname = basename($xdir);

$out['options'] = '';
$out['checkboxes'] = '';
$i=1;
foreach($dirs as $dir){
$file = basename($dir, ".".$type);
$out['options'] = $out['options'].'<option value="'.$file.'">'.$i.'. '.$file.'</option>';
$out['checkboxes'] = $out['checkboxes'].'<li><input type="checkbox" name="'.$dirname.'[]" id="'.$dirname.'-'.$file.'" value="'.$file.'"> '.$file.'</li>';
++$i;
}

$out['checkboxes'] = '<li><input type="checkbox" data-chk="'.$dirname.'[]" onclick="toggle_all_checkboxes(this)"> Toggle all</li>'.$out['checkboxes'];

return $out;
}