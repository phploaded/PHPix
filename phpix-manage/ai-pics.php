<?php

if (!function_exists('ai_pics_upload_scope_sql')) {
function ai_pics_upload_scope_sql($alias = 'u')
{
$adminSession = $_SESSION['PHPix'] ?? '';
$userSession = $_SESSION['phpixuser'] ?? '';

if ($adminSession != '') {
return array('sql' => '', 'types' => '', 'values' => array());
}

if ($userSession != '') {
return array(
'sql' => " WHERE `".$alias."`.`uid` = ?",
'types' => 's',
'values' => array($userSession),
);
}

return array('sql' => " WHERE 1 = 0", 'types' => '', 'values' => array());
}
}

if (!function_exists('ai_pics_upload_image_mime')) {
function ai_pics_upload_image_mime($tmpPath, $originalName)
{
$ext = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
if (!in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) {
return '';
}

$imageInfo = @getimagesize($tmpPath);
if (!$imageInfo || !isset($imageInfo['mime'])) {
return '';
}

$mime = strtolower((string) $imageInfo['mime']);
if (!in_array($mime, array('image/jpeg', 'image/png', 'image/webp'), true)) {
return '';
}

return $mime;
}
}

if (!function_exists('ai_pics_create_image_resource')) {
function ai_pics_create_image_resource($tmpPath, $mime)
{
if ($mime === 'image/jpeg') {
return @imagecreatefromjpeg($tmpPath);
}
if ($mime === 'image/png') {
return @imagecreatefrompng($tmpPath);
}
if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
return @imagecreatefromwebp($tmpPath);
}
return false;
}
}

if (!function_exists('ai_pics_prepare_canvas')) {
function ai_pics_prepare_canvas($image)
{
if (!$image) {
return false;
}

if (function_exists('imagepalettetotruecolor') && !imageistruecolor($image)) {
imagepalettetotruecolor($image);
}
imagealphablending($image, true);
imagesavealpha($image, true);
return true;
}
}

if (!function_exists('ai_pics_save_image_as_webp')) {
function ai_pics_save_image_as_webp($image, $targetPath, $quality = 90)
{
if (!$image || !function_exists('imagewebp')) {
return false;
}

ai_pics_prepare_canvas($image);
return @imagewebp($image, $targetPath, $quality);
}
}

if (!function_exists('ai_pics_convert_upload_to_webp')) {
function ai_pics_convert_upload_to_webp($tmpPath, $targetPath, $mime, $quality = 90)
{
$image = ai_pics_create_image_resource($tmpPath, $mime);
if (!$image) {
return false;
}

$saved = ai_pics_save_image_as_webp($image, $targetPath, $quality);
imagedestroy($image);
return $saved;
}
}

if (!function_exists('ai_pics_convert_data_url_to_webp')) {
function ai_pics_convert_data_url_to_webp($dataUrl, $targetPath, $quality = 90)
{
if (!is_string($dataUrl) || strpos($dataUrl, 'data:image/') !== 0) {
return false;
}

$parts = explode(',', $dataUrl, 2);
if (count($parts) !== 2) {
return false;
}

$binary = base64_decode($parts[1], true);
if ($binary === false) {
return false;
}

$image = @imagecreatefromstring($binary);
if (!$image) {
return false;
}

$saved = ai_pics_save_image_as_webp($image, $targetPath, $quality);
imagedestroy($image);
return $saved;
}
}

if (!function_exists('ai_pics_target_filename')) {
function ai_pics_target_filename($sourceFilename)
{
$sourceFilename = phpix_normalize_media_file($sourceFilename);
return pathinfo($sourceFilename, PATHINFO_FILENAME).'.webp';
}
}

if (!function_exists('ai_pics_target_disk_path')) {
function ai_pics_target_disk_path($sourceFilename)
{
return dirname(__FILE__).'/../ai/'.ai_pics_target_filename($sourceFilename);
}
}

if (!function_exists('ai_pics_full_disk_path')) {
function ai_pics_full_disk_path($sourceFilename)
{
return dirname(__FILE__).'/../full/'.phpix_normalize_media_file($sourceFilename);
}
}

if (!function_exists('ai_pics_full_dimensions')) {
function ai_pics_full_dimensions($sourceFilename)
{
$path = ai_pics_full_disk_path($sourceFilename);
$size = @getimagesize($path);
if (!$size || empty($size[0]) || empty($size[1])) {
return array(0, 0);
}
return array((int) $size[0], (int) $size[1]);
}
}

if (!function_exists('ai_pics_fetch_upload_row')) {
function ai_pics_fetch_upload_row($con, $prefix, $albumId, $uploadId)
{
$scope = ai_pics_upload_scope_sql('u');
$sql = "SELECT `u`.`id`, `u`.`url`, `u`.`spots` FROM `".$prefix."uploads` `u`".$scope['sql']." AND `u`.`folder` = ? AND `u`.`id` = ? LIMIT 1";
if ($scope['sql'] === '') {
$sql = "SELECT `u`.`id`, `u`.`url`, `u`.`spots` FROM `".$prefix."uploads` `u` WHERE `u`.`folder` = ? AND `u`.`id` = ? LIMIT 1";
}

$stmt = $con->prepare($sql);
if (!$stmt) {
return null;
}

$types = $scope['types'].'ss';
$values = $scope['values'];
$values[] = $albumId;
$values[] = $uploadId;
$bind = array($types);
foreach ($values as $key => $value) {
$bind[] = &$values[$key];
}
call_user_func_array(array($stmt, 'bind_param'), $bind);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
if ($result instanceof mysqli_result) {
$result->free();
}
$stmt->close();
return $row;
}
}

if (!function_exists('ai_pics_json_response')) {
function ai_pics_json_response($ok, $message, $extra = array())
{
header('Content-Type: application/json; charset=utf-8');
echo json_encode(array_merge(array(
'ok' => (bool) $ok,
'message' => (string) $message,
), $extra));
exit;
}
}

$aiPicsDir = dirname(__FILE__).'/../ai';

if (!is_dir($aiPicsDir)) {
mkdir($aiPicsDir);
}
if (!file_exists($aiPicsDir.'/index.html')) {
file_put_contents($aiPicsDir.'/index.html', '');
}

if (!isset($notify['ai-pics'])) {
$notify['ai-pics'] = '';
}

$albumId = trim((string) ($_GET['aid'] ?? $_POST['aid'] ?? ''));
$scope = ai_pics_upload_scope_sql('a');
$albumSql = "SELECT `a`.`id`, `a`.`title`, `a`.`descr` FROM `".$prefix."albums` `a`".$scope['sql']." AND `a`.`id` = ? LIMIT 1";
if ($scope['sql'] === '') {
$albumSql = "SELECT `a`.`id`, `a`.`title`, `a`.`descr` FROM `".$prefix."albums` `a` WHERE `a`.`id` = ? LIMIT 1";
}

$albumRow = null;
if ($albumId !== '') {
$albumStmt = $con->prepare($albumSql);
if ($albumStmt) {
$types = $scope['types'].'s';
$values = $scope['values'];
$values[] = $albumId;
$bind = array($types);
foreach ($values as $key => $value) {
$bind[] = &$values[$key];
}
call_user_func_array(array($albumStmt, 'bind_param'), $bind);
$albumStmt->execute();
$albumResult = $albumStmt->get_result();
$albumRow = $albumResult ? $albumResult->fetch_assoc() : null;
if ($albumResult instanceof mysqli_result) {
$albumResult->free();
}
$albumStmt->close();
}
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['ai_crop_submit'])) {
$uploadId = trim((string) ($_POST['upload_id'] ?? ''));
$isAjax = (string) ($_POST['ai_ajax'] ?? '') === '1';

if ($albumId === '' || !is_array($albumRow) || empty($albumRow['id'])) {
$message = 'Please open AI Pics from a valid album first.';
if ($isAjax) {
ai_pics_json_response(false, $message);
}
notify($message, 'ai-pics', 'warning');
} elseif ($uploadId === '') {
$message = 'No image was selected for AI crop save.';
if ($isAjax) {
ai_pics_json_response(false, $message);
}
notify($message, 'ai-pics', 'warning');
} else {
$uploadRow = ai_pics_fetch_upload_row($con, $prefix, $albumId, $uploadId);
if (!is_array($uploadRow) || empty($uploadRow['url'])) {
$message = 'The selected image could not be found for this account.';
if ($isAjax) {
ai_pics_json_response(false, $message);
}
notify($message, 'ai-pics', 'danger');
} else {
$targetPath = ai_pics_target_disk_path((string) $uploadRow['url']);
$saved = false;

if (isset($_FILES['ai_crop_file']) && is_array($_FILES['ai_crop_file']) && (int) ($_FILES['ai_crop_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($_FILES['ai_crop_file']['tmp_name'])) {
$mime = ai_pics_upload_image_mime((string) $_FILES['ai_crop_file']['tmp_name'], (string) ($_FILES['ai_crop_file']['name'] ?? ''));
if ($mime !== '') {
$saved = ai_pics_convert_upload_to_webp((string) $_FILES['ai_crop_file']['tmp_name'], $targetPath, $mime, 90);
}
} elseif (!empty($_POST['ai_crop_data'])) {
$saved = ai_pics_convert_data_url_to_webp((string) $_POST['ai_crop_data'], $targetPath, 90);
}

if ($saved) {
$message = 'AI picture saved successfully for '.(string) $uploadRow['url'].'.';
if ($isAjax) {
ai_pics_json_response(true, $message, array(
'upload_id' => (string) $uploadRow['id'],
'source_file' => (string) $uploadRow['url'],
'ai_file' => ai_pics_target_filename((string) $uploadRow['url']),
));
}
notify($message, 'ai-pics', 'success');
} else {
$message = 'Failed to save the cropped AI image as WebP.';
if ($isAjax) {
ai_pics_json_response(false, $message);
}
notify($message, 'ai-pics', 'danger');
}
}
}
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['ai_upload_submit'])) {
$uploadId = trim((string) ($_POST['upload_id'] ?? ''));

if ($albumId === '' || !is_array($albumRow) || empty($albumRow['id'])) {
notify('Please open AI Pics from a valid album first.', 'ai-pics', 'warning');
} elseif ($uploadId === '') {
notify('No image was selected for AI upload.', 'ai-pics', 'warning');
} elseif (!isset($_FILES['ai_upload_file']) || !is_array($_FILES['ai_upload_file'])) {
notify('Please choose a JPG, JPEG, PNG, or WebP image to upload.', 'ai-pics', 'warning');
} else {
$uploadRow = ai_pics_fetch_upload_row($con, $prefix, $albumId, $uploadId);
if (!is_array($uploadRow) || empty($uploadRow['url'])) {
notify('The selected image could not be found for this account.', 'ai-pics', 'danger');
} else {
$file = $_FILES['ai_upload_file'];
$uploadMime = '';
if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
notify('Upload failed. Please choose a JPG, JPEG, PNG, or WebP file and try again.', 'ai-pics', 'danger');
} elseif (($uploadMime = ai_pics_upload_image_mime($file['tmp_name'], (string) ($file['name'] ?? ''))) === '') {
notify('Only JPG, JPEG, PNG, and WebP image uploads are allowed for AI pictures.', 'ai-pics', 'warning');
} else {
$targetPath = ai_pics_target_disk_path((string) $uploadRow['url']);
if (ai_pics_convert_upload_to_webp($file['tmp_name'], $targetPath, $uploadMime, 90)) {
notify('AI picture uploaded successfully for <b>'.htmlentities((string) $uploadRow['url'], ENT_QUOTES, 'UTF-8').'</b> and converted to WebP. Existing AI image was replaced if present.', 'ai-pics', 'success');
} else {
notify('Failed to convert the uploaded image into WebP in the AI folder.', 'ai-pics', 'danger');
}
}
}
}
}

if ($albumId === '') {
notify('Open AI Pics from an album row to manage AI uploads for that album only.', 'ai-pics', 'warning');
} elseif (!is_array($albumRow) || empty($albumRow['id'])) {
notify('The selected album could not be found for this account.', 'ai-pics', 'danger');
}

$albumTitle = is_array($albumRow) ? trim((string) ($albumRow['title'] ?? '')) : '';
$albumDescr = is_array($albumRow) ? trim((string) ($albumRow['descr'] ?? '')) : '';

echo '<div class="col-lg-12 ai-pics-page">';

$rows = array();
$clientRows = array();
if (is_array($albumRow) && !empty($albumRow['id'])) {
$scope = ai_pics_upload_scope_sql('u');
$sql = "SELECT `u`.`id`, `u`.`url`, `u`.`title`, `u`.`folder`, `u`.`time`, `u`.`uid`, `u`.`spots`
FROM `".$prefix."uploads` `u`".$scope['sql']." AND `u`.`folder` = ?
ORDER BY `u`.`time` DESC";
if ($scope['sql'] === '') {
$sql = "SELECT `u`.`id`, `u`.`url`, `u`.`title`, `u`.`folder`, `u`.`time`, `u`.`uid`, `u`.`spots`
FROM `".$prefix."uploads` `u`
WHERE `u`.`folder` = ?
ORDER BY `u`.`time` DESC";
}

$stmt = $con->prepare($sql);
if ($stmt) {
$values = $scope['values'];
$values[] = $albumId;
$bind = array($scope['types'].'s');
foreach ($values as $key => $value) {
$bind[] = &$values[$key];
}
call_user_func_array(array($stmt, 'bind_param'), $bind);
$stmt->execute();
$result = $stmt->get_result();
if ($result instanceof mysqli_result) {
while ($row = $result->fetch_assoc()) {
$rows[] = $row;
}
$result->free();
}
$stmt->close();
}
}

$aiCount = 0;
foreach ($rows as &$row) {
$sourceFile = (string) $row['url'];
$thumbUrl = phpix_thumb_url($sourceFile);
$aiFile = ai_pics_target_filename($sourceFile);
$aiPath = ai_pics_target_disk_path($sourceFile);
$aiExists = file_exists($aiPath);
list($fullWidth, $fullHeight) = ai_pics_full_dimensions($sourceFile);

$row['thumb_url'] = $thumbUrl;
$row['ai_file'] = $aiFile;
$row['ai_exists'] = $aiExists ? '1' : '0';
$row['full_width'] = (int) $fullWidth;
$row['full_height'] = (int) $fullHeight;

$clientRows[(string) $row['id']] = array(
'upload_id' => (string) $row['id'],
'album_id' => (string) $albumId,
'source_file' => $sourceFile,
'thumb_url' => $thumbUrl,
'pic_url' => $domain.'phpix-alt.php?u='.rawurlencode($sourceFile),
'full_url' => $domain.'full/'.phpix_normalize_media_file($sourceFile),
'ai_file' => $aiFile,
'ai_url' => $domain.'ai/'.$aiFile,
'ai_exists' => $aiExists,
'spots_raw' => (string) ($row['spots'] ?? ''),
'full_width' => (int) $fullWidth,
'full_height' => (int) $fullHeight,
);

if ($aiExists) {
$aiCount++;
}
}
unset($row);
?>

<style>
.ai-pics-thumb {
width: 100%;
height: 120px;
object-fit: cover;
object-position: center center;
display: block;
background: #f4f4f4;
}

#tbl-ai-pics tbody tr:hover .ai-pics-thumb {
animation: aiPicsAnimatedThumb 1s linear infinite alternate;
}

@keyframes aiPicsAnimatedThumb {
from {
object-position: 0 0;
}
to {
object-position: 100% 100%;
}
}

.ai-pics-status {
font-weight: bold;
display: inline-block;
padding: 4px 8px;
border-radius: 3px;
}

.ai-pics-status-ready {
background: #dff0d8;
color: #3c763d;
}

.ai-pics-status-missing {
background: #f2dede;
color: #a94442;
}

.ai-pics-upload-form input[type="file"] {
display: inline-block;
max-width: 240px;
}

.ai-pics-upload-help {
margin-top: 6px;
font-size: 12px;
color: #666;
}

.ai-pics-subtext {
color: #777;
font-size: 12px;
margin-top: 4px;
}

.ai-pics-page {
padding-bottom: 12px;
}

.ai-pics-notify .alert {
margin-bottom: 16px;
border-radius: 0;
box-shadow: none;
}

.ai-pics-notify .alert p {
margin: 0;
line-height: 1.45;
}

.ai-pics-notify .alert-success {
background: #eef7ea;
border: 1px solid #cfe2c6;
color: #2f5d2f;
}

.ai-pics-notify .alert-success .close {
color: #2f5d2f;
opacity: 0.7;
}

.ai-pics-header {
margin: 0 0 20px 0;
}

.ai-pics-header-meta {
background: #f8f8f8;
border: 1px solid #e6e6e6;
padding: 12px 14px;
display: flex;
align-items: flex-start;
justify-content: space-between;
gap: 15px;
flex-wrap: wrap;
}

.ai-pics-header-title {
font-size: 22px;
font-weight: 600;
line-height: 1.2;
margin: 0;
}

.ai-pics-header-descr {
margin: 0;
color: #666;
}

.ai-pics-header-copy {
flex: 1 1 420px;
}

.ai-pics-header-actions {
flex: 0 0 auto;
align-self: flex-start;
}

.ai-pics-header-actions .btn {
white-space: nowrap;
}

.ai-pics-crop-ctr {
position: fixed;
inset: 0;
z-index: 1500;
display: none;
}

.ai-pics-crop-ctr.is-open {
display: block;
}

.ai-pics-crop-bg {
position: absolute;
inset: 0;
background: rgba(0,0,0,0.72);
}

.ai-pics-crop-dialog {
position: relative;
z-index: 2;
width: min(1360px, calc(100vw - 30px));
max-height: calc(100vh - 30px);
margin: 15px auto;
background: #fff;
padding: 16px;
overflow: auto;
box-shadow: 0 8px 40px rgba(0,0,0,0.45);
}

.ai-pics-crop-topbar {
display: flex;
align-items: flex-start;
justify-content: space-between;
gap: 16px;
margin-bottom: 14px;
}

.ai-pics-crop-title {
font-size: 22px;
font-weight: 600;
margin: 0 0 4px 0;
line-height: 1.2;
}

.ai-pics-crop-copy p {
margin: 0;
color: #666;
}

.ai-pics-crop-layout {
display: flex;
gap: 18px;
align-items: flex-start;
}

.ai-pics-crop-main {
flex: 1 1 auto;
min-width: 0;
}

.ai-pics-crop-image-wrap {
background: #111;
padding: 10px;
text-align: center;
min-height: 280px;
}

#ai-pics-crop-image {
max-width: 100%;
max-height: calc(100vh - 250px);
display: block;
margin: 0 auto;
}

.ai-pics-preview-stack {
flex: 0 0 360px;
display: flex;
flex-direction: column;
gap: 16px;
}

.ai-pics-preview-card {
background: #f8f8f8;
border: 1px solid #e3e3e3;
padding: 10px;
}

.ai-pics-preview-title {
font-weight: 600;
margin-bottom: 8px;
}

.ai-pics-preview-stage {
position: relative;
width: 100%;
margin: 0 auto;
background: #fff;
overflow: hidden;
}

.ai-pics-preview-stage img,
.ai-pics-preview-stage canvas {
width: 100%;
height: 100%;
display: block;
object-fit: fill;
}

.ai-pics-preview-canvas,
.ai-pics-preview-canvas .rcrop-preview-wrapper,
.ai-pics-preview-canvas canvas {
width: 100% !important;
height: 100% !important;
display: block;
}

.ai-pics-spots-layer {
position: absolute;
inset: 0;
pointer-events: none;
}

.ai-pics-spot-box {
position: absolute;
background: rgba(255,255,255,0.3);
border: 1px solid rgba(255,255,255,0.85);
box-sizing: border-box;
}

.ai-pics-crop-buttons {
display: flex;
justify-content: flex-end;
gap: 10px;
margin-top: 16px;
}

.ai-pics-crop-status {
margin-top: 12px;
font-size: 13px;
color: #666;
}

.ai-pics-crop-ctr .rcrop-handler-wrapper {
display: block;
}

.ai-pics-crop-ctr .rcrop-handler-border {
display: none !important;
}

.ai-pics-crop-ctr .rcrop-handler-corner {
width: 16px !important;
height: 16px !important;
margin: 0 !important;
border: 2px solid #000 !important;
background: #fff !important;
box-shadow: 0 0 0 1px rgba(255,255,255,0.85);
}

.ai-pics-crop-ctr .rcrop-handler-top-left {
top: -8px !important;
left: -8px !important;
}

.ai-pics-crop-ctr .rcrop-handler-top-right {
top: -8px !important;
right: -8px !important;
}

.ai-pics-crop-ctr .rcrop-handler-bottom-left {
bottom: -8px !important;
left: -8px !important;
}

.ai-pics-crop-ctr .rcrop-handler-bottom-right {
bottom: -8px !important;
right: -8px !important;
}

.ai-pics-crop-ctr .rcrop-croparea-inner {
border: 2px solid #fff;
box-shadow: 0 0 0 1px rgba(0,0,0,0.55);
}

.ai-pics-crop-ctr .rcrop-outer-wrapper {
opacity: 0.55;
}

@media (max-width: 991px) {
.ai-pics-crop-layout {
flex-direction: column;
}

.ai-pics-preview-stack {
flex: 1 1 auto;
width: 100%;
}
}

@media (max-width: 767px) {
.ai-pics-header-actions {
width: 100%;
}

.ai-pics-header-actions .btn {
width: 100%;
}

.ai-pics-crop-dialog {
width: calc(100vw - 14px);
margin: 7px auto;
padding: 12px;
max-height: calc(100vh - 14px);
}

.ai-pics-crop-topbar {
flex-direction: column;
}

#ai-pics-crop-image {
max-height: 42vh;
}
}
</style>

<div class="ai-pics-notify"><?php echo $notify['ai-pics']; ?></div>

<div class="ai-pics-header">
<div class="ai-pics-header-meta">
<div class="ai-pics-header-copy">
<?php if ($albumTitle !== '') { ?>
<div id="ai-pics-album-title" class="ai-pics-header-title"><?php echo htmlentities($albumTitle, ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $aiCount; ?>/<?php echo count($rows); ?> Ai Images)</div>
<?php } else { ?>
<div id="ai-pics-album-title" class="ai-pics-header-title">AI Pics (<?php echo (int) $aiCount; ?>/<?php echo count($rows); ?> Ai Images)</div>
<?php } ?>
</div>
<div class="ai-pics-header-actions">
<a href="<?php echo $admin_url; ?>albums" class="btn btn-default btn-medium"><i class="fa fa-lg fa-fw fa-arrow-left"></i> Albums</a>
<?php if ($albumId !== '') { ?>
<a href="<?php echo $domain; ?>phpix-alt.php?a=<?php echo urlencode($albumId); ?>" class="btn btn-info btn-medium" target="_blank" rel="noopener noreferrer"><i class="fa fa-lg fa-fw fa-external-link"></i> View Album</a>
<?php } ?>
</div>
</div>

<table id="tbl-ai-pics" class="table table-striped table-bordered table-condensed table-hover display">
<thead>
<tr>
<th width="130">Preview</th>
<th width="130">AI Status</th>
<th width="240">File</th>
<th width="120">Uploaded</th>
<th>Choose / Replace AI WebP</th>
</tr>
</thead>
<tbody>
<?php
foreach ($rows as $row) {
$sourceFile = (string) $row['url'];
$thumbUrl = (string) $row['thumb_url'];
$picUrl = $domain.'phpix-alt.php?u='.rawurlencode($sourceFile);
$fullUrl = $domain.'full/'.phpix_normalize_media_file($sourceFile);
$aiFile = (string) $row['ai_file'];
$aiUrl = $domain.'ai/'.$aiFile;
$aiExists = (string) $row['ai_exists'] === '1';
$fullWidth = (int) $row['full_width'];
$fullHeight = (int) $row['full_height'];
echo '<tr id="ai-pics-row-'.htmlentities((string) $row['id'], ENT_QUOTES, 'UTF-8').'">';
echo '<td><a class="ai-pics-thumb-link" href="'.htmlentities($picUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener noreferrer" title="Open album image"><img class="ai-pics-thumb" src="'.htmlentities($thumbUrl, ENT_QUOTES, 'UTF-8').'" alt=""></a></td>';
echo '<td class="ai-pics-status-cell" data-sort="'.($aiExists ? '1' : '0').'">';
if ($aiExists) {
echo '<span class="ai-pics-status ai-pics-status-ready">Uploaded</span>';
echo '<div class="ai-pics-subtext"><a href="'.htmlentities($aiUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener noreferrer">'.htmlentities($aiFile, ENT_QUOTES, 'UTF-8').'</a></div>';
} else {
echo '<span class="ai-pics-status ai-pics-status-missing">Missing</span>';
echo '<div class="ai-pics-subtext">'.htmlentities($aiFile, ENT_QUOTES, 'UTF-8').'</div>';
}
echo '</td>';
echo '<td><b><a href="'.htmlentities($fullUrl, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener noreferrer">'.htmlentities($sourceFile, ENT_QUOTES, 'UTF-8').'</a></b><div class="ai-pics-subtext">'.htmlentities((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8').'</div></td>';
echo '<td data-sort="'.(int) $row['time'].'">'.xdate((int) $row['time'], "d-m-Y, h:i a", "both", '<br><i>', '</i>').'</td>';
echo '<td>';
echo '<form class="ai-pics-upload-form" method="post" enctype="multipart/form-data" data-upload-id="'.htmlentities((string) $row['id'], ENT_QUOTES, 'UTF-8').'" data-source-file="'.htmlentities($sourceFile, ENT_QUOTES, 'UTF-8').'" data-ai-file="'.htmlentities($aiFile, ENT_QUOTES, 'UTF-8').'" data-full-width="'.$fullWidth.'" data-full-height="'.$fullHeight.'">';
echo '<input type="hidden" name="aid" value="'.htmlentities($albumId, ENT_QUOTES, 'UTF-8').'">';
echo '<input type="hidden" name="upload_id" value="'.htmlentities((string) $row['id'], ENT_QUOTES, 'UTF-8').'">';
echo '<input class="ai-pics-upload-input" type="file" name="ai_upload_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>';
if ($aiExists) {
echo '<div style="margin-top:6px;"><button type="button" class="btn btn-sm btn-info ai-pics-edit-btn" data-upload-id="'.htmlentities((string) $row['id'], ENT_QUOTES, 'UTF-8').'">Edit</button></div>';
}
echo '<div class="ai-pics-upload-help">'.($aiExists ? 'Choose a new file to recrop and replace the existing AI WebP.' : 'Choose a file to open the crop tool before saving the AI WebP.').'</div>';
echo '</form>';
echo '</td>';
echo '</tr>';
}
?>
</tbody>
</table>

<div id="ai-pics-crop-modal" class="ai-pics-crop-ctr" aria-hidden="true">
<div class="ai-pics-crop-bg"></div>
<div class="ai-pics-crop-dialog">
<div class="ai-pics-crop-topbar">
<div class="ai-pics-crop-copy">
<h3 id="ai-pics-crop-title" class="ai-pics-crop-title">Crop AI Image</h3>
<p id="ai-pics-crop-copy">Move the locked crop box until the AI preview lines up with the original face boxes, then save.</p>
</div>
<button type="button" id="ai-pics-crop-cancel-top" class="btn btn-default btn-sm">Close</button>
</div>
<div class="ai-pics-crop-layout">
<div class="ai-pics-crop-main">
<div class="ai-pics-crop-image-wrap">
<img id="ai-pics-crop-image" src="" alt="">
</div>
<div id="ai-pics-crop-status" class="ai-pics-crop-status"></div>
</div>
<div class="ai-pics-preview-stack">
<div class="ai-pics-preview-card">
<div class="ai-pics-preview-title">AI Crop Preview</div>
<div id="ai-pics-ai-stage" class="ai-pics-preview-stage">
<div id="ai-pics-ai-preview-canvas" class="ai-pics-preview-canvas"></div>
<div id="ai-pics-ai-overlay" class="ai-pics-spots-layer"></div>
</div>
</div>
<div class="ai-pics-preview-card">
<div class="ai-pics-preview-title">Original Preview</div>
<div id="ai-pics-original-stage" class="ai-pics-preview-stage">
<img id="ai-pics-original-preview-image" src="" alt="">
<div id="ai-pics-original-overlay" class="ai-pics-spots-layer"></div>
</div>
</div>
</div>
</div>
<div class="ai-pics-crop-buttons">
<button type="button" id="ai-pics-crop-save" class="btn btn-success btn-medium">Save AI WebP</button>
<button type="button" id="ai-pics-crop-cancel" class="btn btn-default btn-medium">Cancel</button>
</div>
</div>
</div>

</div>

<script>
var aiPicsAlbumId = <?php echo json_encode((string) $albumId); ?>;
var aiPicsAlbumBaseTitle = <?php echo json_encode($albumTitle !== '' ? $albumTitle : 'AI Pics'); ?>;
var aiPicsTotalImages = <?php echo (int) count($rows); ?>;
var aiPicsSaveUrl = <?php echo json_encode($domain.'phpix-manage/ai-pics-save.php'); ?>;
var aiPicsRows = <?php echo json_encode($clientRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var aiPicsState = {
activeUploadId: '',
activeMeta: null,
activeFile: null,
activeObjectUrl: '',
cropReady: false,
saving: false
};

function aiPicsShowNotice(type, message) {
var html = '<div class="alert alert-' + type + '"><p>' + message + '</p></div>';
$('.ai-pics-notify').html(html);
}

function aiPicsUpdateAlbumTitle() {
var uploadedCount = 0;
Object.keys(aiPicsRows).forEach(function(key) {
if (aiPicsRows[key] && aiPicsRows[key].ai_exists) {
uploadedCount++;
}
});
$('#ai-pics-album-title').text(aiPicsAlbumBaseTitle + ' (' + uploadedCount + '/' + aiPicsTotalImages + ' Ai Images)');
}

function aiPicsEscapeHtml(text) {
return $('<div>').text(text == null ? '' : String(text)).html();
}

function aiPicsCleanupObjectUrl() {
if (aiPicsState.activeObjectUrl) {
URL.revokeObjectURL(aiPicsState.activeObjectUrl);
aiPicsState.activeObjectUrl = '';
}
}

function aiPicsCloseCropper() {
aiPicsState.cropReady = false;
aiPicsState.saving = false;

var $image = $('#ai-pics-crop-image');
if ($image.data('rcrop')) {
try {
$image.rcrop('destroy');
} catch (error) {}
}

$('#wrapper, .mlib-main').removeClass('blur');
$('#ai-pics-crop-modal').removeClass('is-open').attr('aria-hidden', 'true');
$('#ai-pics-crop-image').attr('src', '');
$('#ai-pics-original-preview-image').attr('src', '');
$('#ai-pics-ai-preview-canvas').empty();
$('#ai-pics-original-overlay, #ai-pics-ai-overlay').empty();
$('#ai-pics-crop-save').prop('disabled', false).text('Save AI WebP');
$('#ai-pics-crop-status').text('');
$('.ai-pics-upload-input').val('');
aiPicsCleanupObjectUrl();
}

function aiPicsParseSpots(raw) {
var text = (raw || '').trim();
if (!text) {
return [];
}
if (text.charAt(text.length - 1) === ',') {
text = text.substring(0, text.length - 1);
}
if (!text) {
return [];
}
try {
var parsed = JSON.parse('[' + text + ']');
var spots = [];
for (var i = 0; i < parsed.length; i++) {
var item = parsed[i];
for (var key in item) {
if (!Object.prototype.hasOwnProperty.call(item, key)) {
continue;
}
spots.push(item[key]);
}
}
return spots;
} catch (error) {
return [];
}
}

function aiPicsCssMap(cssText) {
var map = {};
String(cssText || '').split(';').forEach(function(part) {
var bits = part.split(':');
if (bits.length < 2) {
return;
}
var key = $.trim(bits[0]).toLowerCase();
var value = $.trim(bits.slice(1).join(':'));
if (key) {
map[key] = value;
}
});
return map;
}

function aiPicsPercentOrDefault(value, fallback) {
if (typeof value !== 'string' || value === '') {
return fallback;
}
var parsed = parseFloat(value);
return isNaN(parsed) ? fallback : parsed;
}

function aiPicsRenderSpotOverlay($target, spots) {
$target.empty();
for (var i = 0; i < spots.length; i++) {
var spot = spots[i];
var map = aiPicsCssMap(spot.css || '');
var top = aiPicsPercentOrDefault(map.top, 0);
var left = aiPicsPercentOrDefault(map.left, 0);
var width = aiPicsPercentOrDefault(map.width, 4);
var height = aiPicsPercentOrDefault(map.height, 4);

var $box = $('<div class="ai-pics-spot-box"></div>');
$box.css({
top: top + '%',
left: left + '%',
width: width + '%',
height: height + '%'
});
if (spot.txt) {
$box.attr('title', spot.txt);
}
$target.append($box);
}
}

function aiPicsComputeCropSize(meta, imageWidth, imageHeight) {
var targetAspect = meta.full_width / meta.full_height;
var imageAspect = imageWidth / imageHeight;
var cropWidth;
var cropHeight;

if (imageAspect >= targetAspect) {
cropHeight = imageHeight;
cropWidth = Math.round(cropHeight * targetAspect);
} else {
cropWidth = imageWidth;
cropHeight = Math.round(cropWidth / targetAspect);
}

return {
width: cropWidth,
height: cropHeight
};
}

function aiPicsComputeMinCropSize(meta, cropWidth, cropHeight) {
var minWidth = Math.round(cropWidth * 0.35);
var minHeight = Math.round(cropHeight * 0.35);

if (minWidth < 160) {
minWidth = 160;
}
if (minHeight < 160) {
minHeight = 160;
}

if (meta.full_width && meta.full_height) {
var aspect = meta.full_width / meta.full_height;
if (aspect > 1) {
minHeight = Math.round(minWidth / aspect);
} else {
minWidth = Math.round(minHeight * aspect);
}
}

return {
width: Math.max(80, minWidth),
height: Math.max(80, minHeight)
};
}

function aiPicsComputePreviewSize(meta) {
var maxWidth = Math.min(340, Math.max(180, window.innerWidth < 768 ? window.innerWidth - 90 : 340));
var maxHeight = window.innerWidth < 768 ? 180 : 220;
var aspect = meta.full_width / meta.full_height;
var width = maxWidth;
var height = Math.max(80, Math.round((meta.full_height / meta.full_width) * width));

if (height > maxHeight) {
height = maxHeight;
width = Math.max(120, Math.round(height * aspect));
}

return { width: width, height: height };
}

function aiPicsApplyPreviewSize(meta) {
var size = aiPicsComputePreviewSize(meta);
$('#ai-pics-original-stage, #ai-pics-ai-stage').css({
width: size.width + 'px',
height: size.height + 'px'
});
return size;
}

function aiPicsUpdateStatus(text) {
$('#ai-pics-crop-status').text(text || '');
}

function aiPicsOpenCropper(meta, file) {
if (!meta || !file) {
return;
}
if (!meta.full_width || !meta.full_height) {
aiPicsShowNotice('danger', 'Original image size could not be detected for ' + aiPicsEscapeHtml(meta.source_file) + '.');
return;
}

aiPicsCloseCropper();

aiPicsState.activeUploadId = meta.upload_id;
aiPicsState.activeMeta = meta;
aiPicsState.activeFile = file;
aiPicsState.activeObjectUrl = URL.createObjectURL(file);

$('#wrapper, .mlib-main').addClass('blur');
$('#ai-pics-crop-title').text('Crop AI Image for ' + meta.source_file);
$('#ai-pics-original-preview-image').attr('src', meta.thumb_url);
$('#ai-pics-crop-image').attr('src', aiPicsState.activeObjectUrl);
$('#ai-pics-crop-modal').addClass('is-open').attr('aria-hidden', 'false');
aiPicsUpdateStatus('Loading crop tool...');

var spots = aiPicsParseSpots(meta.spots_raw || '');
aiPicsRenderSpotOverlay($('#ai-pics-original-overlay'), spots);
aiPicsRenderSpotOverlay($('#ai-pics-ai-overlay'), spots);

var previewSize = aiPicsApplyPreviewSize(meta);
$('#ai-pics-ai-preview-canvas').empty();

var $cropImage = $('#ai-pics-crop-image');
var startCropper = function() {
var imageEl = $cropImage.get(0);
var cropSize = aiPicsComputeCropSize(meta, imageEl.naturalWidth, imageEl.naturalHeight);
var minCropSize = aiPicsComputeMinCropSize(meta, cropSize.width, cropSize.height);
aiPicsUpdateStatus('Drag the crop area to align the AI image with the face boxes.');

$cropImage.off('.aiPicsCropper');
$cropImage.on('rcrop-ready.aiPicsCropper', function() {
$cropImage.rcrop('resize', cropSize.width, cropSize.height, 'center', 'center');
aiPicsState.cropReady = true;
aiPicsUpdateStatus('Crop ready. It starts at the largest possible size for the original aspect. Move or resize from corners, then save.');
});
$cropImage.on('rcrop-change.aiPicsCropper rcrop-changed.aiPicsCropper', function() {
aiPicsUpdateStatus('Move the crop box until the AI preview lines up with the original face boxes.');
});

$cropImage.rcrop({
grid: true,
full: false,
preserveAspectRatio: true,
minSize: [minCropSize.width, minCropSize.height],
maxSize: [imageEl.naturalWidth, imageEl.naturalHeight],
preview: {
display: true,
size: [previewSize.width, previewSize.height],
wrapper: '#ai-pics-ai-preview-canvas'
}
});
};

if ($cropImage.get(0).complete && $cropImage.get(0).naturalWidth > 0) {
startCropper();
} else {
$cropImage.off('load.aiPicsInit').on('load.aiPicsInit', function() {
$cropImage.off('load.aiPicsInit');
startCropper();
});
}
}

function aiPicsOpenExistingAi(uploadId) {
var meta = aiPicsRows[String(uploadId)] || null;
if (!meta || !meta.ai_exists || !meta.ai_url) {
aiPicsShowNotice('danger', 'Existing AI image could not be opened for recrop.');
return;
}

fetch(meta.ai_url + '?t=' + Date.now())
.then(function(response) {
if (!response.ok) {
throw new Error('failed');
}
return response.blob();
})
.then(function(blob) {
var file = new File([blob], meta.ai_file || (meta.source_file + '.webp'), {
type: blob.type || 'image/webp'
});
aiPicsOpenCropper(meta, file);
})
.catch(function() {
aiPicsShowNotice('danger', 'Failed to load the existing AI image for recrop.');
});
}

function aiPicsBlobFromCanvas(canvas, callback) {
if (canvas.toBlob) {
canvas.toBlob(function(blob) {
if (blob) {
callback({ blob: blob });
return;
}
callback({ blob: null });
}, 'image/webp', 0.9);
return;
}
callback({ dataUrl: canvas.toDataURL('image/webp', 0.9) });
}

function aiPicsUpdateRowSaved(meta) {
var $row = $('#ai-pics-row-' + meta.upload_id);
$row.find('.ai-pics-status').removeClass('ai-pics-status-missing').addClass('ai-pics-status-ready').text('Uploaded');
$row.find('.ai-pics-status-cell').attr('data-sort', '1');
$row.find('.ai-pics-status-cell .ai-pics-subtext').html('<a href="' + aiPicsEscapeHtml(meta.ai_url) + '" target="_blank" rel="noopener noreferrer">' + aiPicsEscapeHtml(meta.ai_file) + '</a>');
$row.find('.ai-pics-upload-help').text('Choose a new file to recrop and replace the existing AI WebP.');
if (aiPicsRows[meta.upload_id]) {
aiPicsRows[meta.upload_id].ai_exists = true;
aiPicsRows[meta.upload_id].ai_url = meta.ai_url;
aiPicsRows[meta.upload_id].ai_file = meta.ai_file;
}
if ($row.find('.ai-pics-edit-btn').length === 0) {
$row.find('.ai-pics-upload-input').after('<div style="margin-top:6px;"><button type="button" class="btn btn-sm btn-info ai-pics-edit-btn" data-upload-id="' + aiPicsEscapeHtml(meta.upload_id) + '">Edit</button></div>');
}
aiPicsUpdateAlbumTitle();
}

function aiPicsSaveCurrentCrop() {
if (aiPicsState.saving || !aiPicsState.activeMeta || !aiPicsState.cropReady) {
return;
}

var meta = aiPicsState.activeMeta;
var cropValues = $('#ai-pics-crop-image').rcrop('getValues');
var imageEl = $('#ai-pics-crop-image').get(0);
if (!cropValues || !imageEl) {
aiPicsShowNotice('danger', 'Crop data is not ready yet.');
return;
}

var outputWidth = Math.max(1, Math.round(cropValues.width));
var outputHeight = Math.max(1, Math.round(cropValues.height));

aiPicsState.saving = true;
$('#ai-pics-crop-save').prop('disabled', true).text('Saving...');
aiPicsUpdateStatus('Saving cropped AI WebP at highest available crop resolution...');

var canvas = document.createElement('canvas');
canvas.width = outputWidth;
canvas.height = outputHeight;
var ctx = canvas.getContext('2d');
ctx.drawImage(
imageEl,
cropValues.x,
cropValues.y,
cropValues.width,
cropValues.height,
0,
0,
outputWidth,
outputHeight
);

aiPicsBlobFromCanvas(canvas, function(result) {
var formData = new FormData();
formData.append('aid', meta.album_id);
formData.append('upload_id', meta.upload_id);
formData.append('ai_crop_submit', '1');
formData.append('ai_ajax', '1');
if (result.blob) {
formData.append('ai_crop_file', result.blob, meta.ai_file);
} else if (result.dataUrl) {
formData.append('ai_crop_data', result.dataUrl);
} else {
aiPicsState.saving = false;
$('#ai-pics-crop-save').prop('disabled', false).text('Save AI WebP');
aiPicsUpdateStatus('Failed to prepare the cropped image.');
return;
}

$.ajax({
url: aiPicsSaveUrl,
type: 'POST',
data: formData,
processData: false,
contentType: false,
dataType: 'json'
}).done(function(response) {
if (response && response.ok) {
response.ai_file = response.ai_file || meta.ai_file;
response.ai_url = (aiPicsRows[meta.upload_id] && aiPicsRows[meta.upload_id].ai_url) ? aiPicsRows[meta.upload_id].ai_url : (meta.ai_url || '');
aiPicsUpdateRowSaved(response);
aiPicsShowNotice('success', aiPicsEscapeHtml(response.message));
admin_toast('AI WebP saved successfully');
aiPicsCloseCropper();
} else {
var message = response && response.message ? response.message : 'Failed to save the cropped AI image.';
aiPicsShowNotice('danger', aiPicsEscapeHtml(message));
aiPicsState.saving = false;
$('#ai-pics-crop-save').prop('disabled', false).text('Save AI WebP');
aiPicsUpdateStatus(message);
}
}).fail(function() {
var message = 'Failed to save the cropped AI image.';
aiPicsShowNotice('danger', aiPicsEscapeHtml(message));
aiPicsState.saving = false;
$('#ai-pics-crop-save').prop('disabled', false).text('Save AI WebP');
aiPicsUpdateStatus(message);
});
});
}

$(document).ready(function() {
$('#ai-pics-crop-modal').appendTo('body');

$('#tbl-ai-pics').DataTable({
responsive: true,
pageLength: 10,
dom: '<"top"fp>rt<"bottom"ip><"clear">',
order: [[1, 'asc'], [3, 'desc']]
});

$('#tbl-ai-pics').on('change', '.ai-pics-upload-input', function() {
var input = this;
if (!input.files || input.files.length !== 1) {
return;
}
var form = $(input).closest('form');
var uploadId = String(form.data('upload-id') || '');
var meta = aiPicsRows[uploadId] || null;
if (!meta) {
aiPicsShowNotice('danger', 'Image metadata could not be loaded for cropping.');
input.value = '';
return;
}
aiPicsOpenCropper(meta, input.files[0]);
});

$('#tbl-ai-pics').on('click', '.ai-pics-edit-btn', function() {
var uploadId = String($(this).data('upload-id') || '');
if (uploadId === '') {
return;
}
aiPicsOpenExistingAi(uploadId);
});

$('#ai-pics-crop-cancel, #ai-pics-crop-cancel-top, #ai-pics-crop-modal .ai-pics-crop-bg').on('click', function() {
aiPicsCloseCropper();
});

$('#ai-pics-crop-save').on('click', function() {
aiPicsSaveCurrentCrop();
});
});
</script>
