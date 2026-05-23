<?php 
require_once('phpix-media-functions.php');

if($phpix_user==''){die('You must be logged in.');}
if($_GET['method']=='nocache'){
@unlink('cache/'.$_GET['file']);
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
}



if($_GET['method']=='calculate'){

$filenm = $_SERVER['HTTP_HOST'].'-index-'.date("Ym").'.html';
@unlink('cache/'.$filenm);

$path = $_GET['dir'];
$files = array_diff(scandir($path), array('.', '..'));

$bytes = 0;
foreach($files as $file){
$bytes = $bytes + filesize($path.'/'.$file);
}

mysqli_query($con, "UPDATE `".$prefix."dirs` SET `time`='".time()."', `files`='".count($files)."', `size`='$bytes' WHERE `id`='".$path."'");
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
}



if(!function_exists('phpix_generate_target_exists')){
function phpix_generate_target_exists($dir, $file){
$file = phpix_normalize_media_file($file);
if($file == ''){
return false;
}

if($dir == 'thumb'){
return file_exists(phpix_thumb_disk_path($file));
}

return file_exists(phpix_media_disk_path($dir, $file));
}
}

if(!function_exists('phpix_generate_label')){
function phpix_generate_label($dir){
$labels = array(
'thumb' => 'Thumbnails',
'hd' => 'HD',
'fhd' => 'Full HD',
'2k' => '2K',
'full' => 'Originals',
);

if(isset($labels[$dir])){
return $labels[$dir];
}

return strtoupper($dir);
}
}




if($_GET['method']=='generate'){
$requested_dir = phpix_normalize_media_quality($_GET['dir']);
if($requested_dir == 'thumb'){
$dir = 'full';
} else {
$dir = $requested_dir;
}

if(isset($_GET['albumid'])){
$return_url = $domain."phpix-manage.php?page=backup&aid=".$_GET['albumid']."&make=yes&dir=".$requested_dir;
$return_label = 'Return to backup';
} else {
$return_url = $domain."phpix-manage.php?page=operations&method=calculate&dir=".$requested_dir;
$return_label = 'Return to storage report';
}

$data = mysqli_query($con, "SELECT `url` FROM `".$prefix."uploads`");
$queue = array();
$library_total = 0;
while($row = mysqli_fetch_assoc($data)){
++ $library_total;
if(!phpix_generate_target_exists($requested_dir, $row['url'])){
$queue[] = $row['url'];
}
}
?>
<style type="text/css">
.gen-dashboard{
margin-top:20px;
}
.gen-panel{
box-shadow:0 1px 4px rgba(0,0,0,0.08);
}
.gen-hero{
padding:18px 20px 6px;
}
.gen-progress-number{
font-size:44px;
line-height:1;
font-weight:700;
letter-spacing:-1px;
margin:0 0 8px;
}
.gen-progress-copy{
font-size:15px;
color:#5f6b77;
margin-bottom:14px;
}
.gen-progress-shell{
height:20px;
margin-bottom:16px;
overflow:hidden;
}
.gen-progress-shell .progress-bar{
line-height:20px;
font-weight:700;
min-width:48px;
}
.gen-file-box{
background:#f8fafc;
border:1px solid #dce5ee;
border-radius:4px;
padding:12px 14px;
margin-bottom:16px;
}
.gen-file-label{
font-size:11px;
font-weight:700;
text-transform:uppercase;
letter-spacing:.08em;
color:#7f8c98;
margin-bottom:4px;
}
.gen-file-name{
font-family:Consolas, Monaco, monospace;
font-size:13px;
word-break:break-all;
color:#2c3e50;
}
.gen-stat-grid{
margin:0 -6px 6px;
}
.gen-stat-card{
padding:0 6px 12px;
}
.gen-stat-tile{
background:#f8fafc;
border:1px solid #e1e8ef;
border-radius:4px;
padding:12px;
min-height:90px;
}
.gen-stat-kicker{
font-size:11px;
font-weight:700;
text-transform:uppercase;
letter-spacing:.08em;
color:#7f8c98;
margin-bottom:6px;
}
.gen-stat-value{
font-size:26px;
line-height:1.1;
font-weight:700;
color:#23313f;
}
.gen-stat-note{
margin-top:6px;
font-size:12px;
color:#7b8894;
}
.gen-meta-list{
margin:0;
padding:0;
list-style:none;
}
.gen-meta-list li{
display:flex;
justify-content:space-between;
gap:12px;
padding:8px 0;
border-bottom:1px solid #eef2f6;
font-size:13px;
}
.gen-meta-list li:last-child{
border-bottom:0;
}
.gen-meta-list code{
white-space:normal;
word-break:break-word;
}
.gen-feed{
max-height:300px;
overflow:auto;
margin:0;
padding:0;
list-style:none;
}
.gen-feed li{
display:flex;
justify-content:space-between;
gap:10px;
padding:10px 0;
border-bottom:1px solid #eef2f6;
font-size:13px;
}
.gen-feed li:last-child{
border-bottom:0;
}
.gen-feed .label{
min-width:64px;
display:inline-block;
text-align:center;
}
.gen-feed .gen-file-name{
font-size:12px;
}
.gen-empty{
padding:16px 0;
text-align:center;
color:#7f8c98;
}
.gen-actions{
margin-top:10px;
display:flex;
gap:10px;
flex-wrap:wrap;
}
.gen-status-banner{
display:none;
margin-bottom:15px;
}
@media (max-width: 767px){
.gen-progress-number{
font-size:34px;
}
.gen-meta-list li,
.gen-feed li{
display:block;
}
.gen-meta-list li span:last-child,
.gen-feed li span:last-child{
display:block;
margin-top:4px;
}
}
</style>

<div class="gen-dashboard">
<div class="page-header">
<h3><i class="fa fa-cogs fa-fw"></i> Generate <?php echo htmlspecialchars(phpix_generate_label($requested_dir), ENT_QUOTES, 'UTF-8'); ?></h3>
<p class="text-muted">Creating missing <code><?php echo htmlspecialchars($requested_dir, ENT_QUOTES, 'UTF-8'); ?></code> files for your media library.</p>
</div>

<div id="gen-status-banner" class="alert gen-status-banner"></div>

<div class="row">
<div class="col-md-8">
<div class="panel panel-primary gen-panel">
<div class="panel-heading"><i class="fa fa-refresh fa-fw"></i> Generation Progress</div>
<div class="panel-body gen-hero">
<div id="gen-progress-number" class="gen-progress-number">0%</div>
<div id="gen-progress-copy" class="gen-progress-copy">Preparing queue...</div>
<div class="progress progress-striped active gen-progress-shell">
<div id="gen-progress-bar" class="progress-bar progress-bar-info" style="width: 0%">0%</div>
</div>

<div class="gen-file-box">
<div class="gen-file-label">Current file</div>
<div id="gen-current-file" class="gen-file-name">Waiting to start</div>
</div>

<div class="row gen-stat-grid">
<div class="col-xs-6 col-sm-3 gen-stat-card">
<div class="gen-stat-tile">
<div class="gen-stat-kicker">Queued</div>
<div id="gen-stat-total" class="gen-stat-value"><?php echo count($queue); ?></div>
<div class="gen-stat-note">Missing files found</div>
</div>
</div>
<div class="col-xs-6 col-sm-3 gen-stat-card">
<div class="gen-stat-tile">
<div class="gen-stat-kicker">Completed</div>
<div id="gen-stat-completed" class="gen-stat-value">0</div>
<div class="gen-stat-note">Generated successfully</div>
</div>
</div>
<div class="col-xs-6 col-sm-3 gen-stat-card">
<div class="gen-stat-tile">
<div class="gen-stat-kicker">Remaining</div>
<div id="gen-stat-remaining" class="gen-stat-value"><?php echo count($queue); ?></div>
<div class="gen-stat-note">Still in queue</div>
</div>
</div>
<div class="col-xs-6 col-sm-3 gen-stat-card">
<div class="gen-stat-tile">
<div class="gen-stat-kicker">Failed</div>
<div id="gen-stat-failed" class="gen-stat-value">0</div>
<div class="gen-stat-note">Needs manual retry</div>
</div>
</div>
</div>
</div>
</div>
</div>

<div class="col-md-4">
<div class="panel panel-info gen-panel">
<div class="panel-heading"><i class="fa fa-info-circle fa-fw"></i> Job Details</div>
<div class="panel-body">
<ul class="gen-meta-list">
<li><span>Target output</span><span><strong><?php echo htmlspecialchars(phpix_generate_label($requested_dir), ENT_QUOTES, 'UTF-8'); ?></strong></span></li>
<li><span>Generation mode</span><span><code><?php echo htmlspecialchars($dir, ENT_QUOTES, 'UTF-8'); ?></code> via <code>thumb-gen.php</code></span></li>
<li><span>Library files</span><span id="gen-library-total"><?php echo (int) $library_total; ?></span></li>
<li><span>Started</span><span id="gen-started-at">Waiting...</span></li>
<li><span>Elapsed</span><span id="gen-elapsed">0s</span></li>
<li><span>Speed</span><span id="gen-speed">0 files/min</span></li>
<li><span>ETA</span><span id="gen-eta">Calculating...</span></li>
<li><span>Status</span><span id="gen-status-pill" class="label label-info">Queued</span></li>
</ul>
<div class="gen-actions">
<a href="<?php echo htmlspecialchars($return_url, ENT_QUOTES, 'UTF-8'); ?>" id="gen-return-link" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo htmlspecialchars($return_label, ENT_QUOTES, 'UTF-8'); ?></a>
<button type="button" id="gen-scroll-errors" class="btn btn-warning" style="display:none;"><i class="fa fa-exclamation-triangle"></i> Show failures</button>
</div>
</div>
</div>
</div>
</div>

<div class="row">
<div class="col-md-6">
<div class="panel panel-success gen-panel">
<div class="panel-heading"><i class="fa fa-check-circle fa-fw"></i> Recent Activity</div>
<div class="panel-body">
<ul id="gen-recent-list" class="gen-feed">
<li class="gen-empty">Generation activity will appear here.</li>
</ul>
</div>
</div>
</div>
<div class="col-md-6">
<div class="panel panel-default gen-panel">
<div class="panel-heading"><i class="fa fa-list fa-fw"></i> Queue Preview</div>
<div class="panel-body">
<ul id="gen-pending-list" class="gen-feed">
<?php if(count($queue) == 0){ ?>
<li class="gen-empty">No missing files were found.</li>
<?php } ?>
</ul>
</div>
</div>
</div>
</div>
</div>

<script type="text/javascript">
var quality = <?php echo json_encode($dir); ?>;
var genQueue = <?php echo json_encode(array_values($queue)); ?>;
var genInitialQueue = genQueue.slice(0);
var genTotal = genQueue.length;
var genCompleted = 0;
var genFailed = [];
var genRecent = [];
var genActiveFile = '';
var genStartedAt = null;
var genFinishRedirect = null;
var genReturnUrl = <?php echo json_encode($return_url); ?>;

function genEscapeHtml(text){
return String(text).replace(/[&<>"']/g, function(match) {
return {
'&': '&amp;',
'<': '&lt;',
'>': '&gt;',
'"': '&quot;',
"'": '&#39;'
}[match];
});
}

function genFormatDuration(totalSeconds){
totalSeconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
var hours = Math.floor(totalSeconds / 3600);
var minutes = Math.floor((totalSeconds % 3600) / 60);
var seconds = totalSeconds % 60;
var parts = [];
if(hours > 0){parts.push(hours+'h');}
if(minutes > 0 || hours > 0){parts.push(minutes+'m');}
parts.push(seconds+'s');
return parts.join(' ');
}

function genFormatRate(processed, elapsedSeconds){
if(processed < 1 || elapsedSeconds < 1){
return '0 files/min';
}
return (processed / elapsedSeconds * 60).toFixed(1)+' files/min';
}

function genPendingCount(){
return genQueue.length + (genActiveFile !== '' ? 1 : 0);
}

function genProcessedCount(){
return genCompleted + genFailed.length;
}

function genProgressPercent(){
if(genTotal === 0){
return 100;
}
return (genProcessedCount() / genTotal) * 100;
}

function genPushRecent(file, state, note){
genRecent.unshift({
file: file,
state: state,
note: note
});
if(genRecent.length > 8){
genRecent = genRecent.slice(0, 8);
}
}

function genRenderRecent(){
if(genRecent.length === 0){
$('#gen-recent-list').html('<li class="gen-empty">Generation activity will appear here.</li>');
return;
}

var listHtml = '';
$.each(genRecent, function(index, item){
var labelClass = item.state === 'success' ? 'label-success' : 'label-danger';
listHtml += '<li><span><span class="label '+labelClass+'">'+genEscapeHtml(item.note)+'</span></span><span class="gen-file-name">'+genEscapeHtml(item.file)+'</span></li>';
});
$('#gen-recent-list').html(listHtml);
}

function genRenderPending(){
var preview = genQueue.slice(0, 8);
var listHtml = '';

if(genActiveFile !== ''){
listHtml += '<li><span class="label label-info">Now</span><span class="gen-file-name">'+genEscapeHtml(genActiveFile)+'</span></li>';
}

if(preview.length === 0){
if(listHtml !== ''){
$('#gen-pending-list').html(listHtml);
} else {
$('#gen-pending-list').html('<li class="gen-empty">Queue is clear.</li>');
}
return;
}

$.each(preview, function(index, file){
var queueLabel = index === 0 && genActiveFile === '' ? 'Next' : 'Queued';
listHtml += '<li><span class="label label-default">'+queueLabel+'</span><span class="gen-file-name">'+genEscapeHtml(file)+'</span></li>';
});
$('#gen-pending-list').html(listHtml);
}

function genSetBanner(type, message){
if(message === ''){
$('#gen-status-banner').hide().removeClass('alert-success alert-warning alert-danger alert-info').html('');
return;
}

$('#gen-status-banner')
.removeClass('alert-success alert-warning alert-danger alert-info')
.addClass('alert-'+type)
.html(message)
.show();
}

function genRender(){
var processed = genProcessedCount();
var pending = genPendingCount();
var elapsedSeconds = genStartedAt ? Math.floor((Date.now() - genStartedAt) / 1000) : 0;
var percent = genProgressPercent();
var statusText = 'Queued';
var statusClass = 'label-info';
var progressClass = 'progress-bar-info';
var bannerType = '';
var bannerText = '';
var etaText = 'Calculating...';

if(genStartedAt){
$('#gen-started-at').text(new Date(genStartedAt).toLocaleTimeString());
}

if(genActiveFile !== ''){
statusText = 'Generating';
statusClass = 'label-primary';
progressClass = 'progress-bar-info';
etaText = processed > 0 && elapsedSeconds > 0 ? genFormatDuration(Math.ceil((pending / processed) * elapsedSeconds)) : 'Estimating...';
$('#gen-progress-copy').text('Processing one file at a time through AJAX.');
$('#gen-current-file').text(genActiveFile);
} else if(genTotal === 0){
statusText = 'Up to date';
statusClass = 'label-success';
progressClass = 'progress-bar-success';
etaText = 'None';
$('#gen-progress-copy').text('No missing files were found for this target.');
$('#gen-current-file').text('Nothing to generate');
bannerType = 'success';
bannerText = '<strong>Nothing to do.</strong> All requested files already exist.';
} else if(pending === 0 && genFailed.length === 0){
statusText = 'Completed';
statusClass = 'label-success';
progressClass = 'progress-bar-success';
etaText = 'Done';
$('#gen-progress-copy').text('Every missing file for this target was generated successfully.');
$('#gen-current-file').text('Queue complete');
bannerType = 'success';
bannerText = '<strong>Generation finished.</strong> Redirecting to the next step...';
} else if(pending === 0 && genFailed.length > 0){
statusText = 'Completed with issues';
statusClass = 'label-warning';
progressClass = 'progress-bar-warning';
etaText = 'Stopped';
$('#gen-progress-copy').text('The queue finished, but some files could not be generated.');
$('#gen-current-file').text('Review recent failures below');
bannerType = 'warning';
bannerText = '<strong>Generation finished with issues.</strong> '+genFailed.length+' file(s) failed and need a retry.';
} else if(genStartedAt){
statusText = 'Queued';
statusClass = 'label-info';
progressClass = 'progress-bar-info';
etaText = processed > 0 && elapsedSeconds > 0 ? genFormatDuration(Math.ceil((pending / processed) * elapsedSeconds)) : 'Estimating...';
$('#gen-progress-copy').text('Preparing the next file...');
$('#gen-current-file').text('Waiting for next request');
}

$('#gen-progress-number').text(percent.toFixed(2)+'%');
$('#gen-progress-bar')
.removeClass('progress-bar-info progress-bar-success progress-bar-warning progress-bar-danger')
.addClass(progressClass)
.css('width', percent+'%')
.text(percent.toFixed(2)+'%');
$('#gen-stat-total').text(genTotal);
$('#gen-stat-completed').text(genCompleted);
$('#gen-stat-remaining').text(pending);
$('#gen-stat-failed').text(genFailed.length);
$('#gen-elapsed').text(genFormatDuration(elapsedSeconds));
$('#gen-speed').text(genFormatRate(processed, elapsedSeconds));
$('#gen-eta').text(etaText);
$('#gen-status-pill').removeClass('label-info label-primary label-success label-warning label-danger').addClass(statusClass).text(statusText);
$('#gen-scroll-errors').toggle(genFailed.length > 0);

genSetBanner(bannerType, bannerText);
genRenderRecent();
genRenderPending();
}

function genFinish(){
genActiveFile = '';
genRender();

if(genFailed.length === 0){
if(genFinishRedirect === null){
genFinishRedirect = setTimeout(function(){
document.location.href = genReturnUrl;
}, 1400);
}
} else {
$('#gen-return-link').removeClass('btn-default').addClass('btn-warning');
}
}

function start_generating(){
if(genStartedAt === null){
genStartedAt = Date.now();
}

if(genQueue.length === 0){
genFinish();
return;
}

var currentFile = genQueue.shift();
genActiveFile = currentFile;
genRender();

$.ajax({
url: main_domain+'thumb-gen.php',
type: 'POST',
data: {id: currentFile, q: quality},
timeout: 120000
})
.done(function(){
genCompleted++;
genPushRecent(currentFile, 'success', 'Done');
})
.fail(function(xhr, textStatus){
genFailed.push(currentFile);
genPushRecent(currentFile, 'danger', textStatus === 'timeout' ? 'Timed out' : 'Failed');
})
.always(function(){
genActiveFile = '';
genRender();
setTimeout(start_generating, 20);
});
}

$(document).ready(function(){
genRender();
start_generating();

$('#gen-scroll-errors').on('click', function(){
if($('#gen-recent-list').length === 1){
$('html, body').animate({scrollTop: $('#gen-recent-list').closest('.panel').offset().top - 20}, 250);
}
});
});
</script>
<?php

}




if($_GET['method']=='delete'){
admin_only();
$filenm = $_SERVER['HTTP_HOST'].'-index-'.date("Ym").'.html';
@unlink('cache/'.$filenm);

$path = $_GET['dir'];
if($path!='full'){
$files = array_diff(scandir($path), array('.', '..'));

foreach($files as $file){
unlink($path.'/'.$file);
}

// create empty index.html
file_put_contents($path.'/index.html', '');

mysqli_query($con, "UPDATE `".$prefix."dirs` SET `time`='".time()."', `files`='1', `size`='0' WHERE `id`='".$path."'");
echo "<script>document.location.href = '".$domain."phpix-manage.php?page=index';</script>";
} else {
echo'<br><br><br><br>Cannot delete original images!';
}
}

 ?>
