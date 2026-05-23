<?php
include('../phpix-config.php');
include('../phpix-front-functions.php');

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

if (!function_exists('ai_pics_fetch_upload_row')) {
function ai_pics_fetch_upload_row($con, $prefix, $albumId, $uploadId)
{
$scope = ai_pics_upload_scope_sql('u');
$sql = "SELECT `u`.`id`, `u`.`url` FROM `".$prefix."uploads` `u`".$scope['sql']." AND `u`.`folder` = ? AND `u`.`id` = ? LIMIT 1";
if ($scope['sql'] === '') {
$sql = "SELECT `u`.`id`, `u`.`url` FROM `".$prefix."uploads` `u` WHERE `u`.`folder` = ? AND `u`.`id` = ? LIMIT 1";
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

$albumId = trim((string) ($_POST['aid'] ?? $_GET['aid'] ?? ''));
$uploadId = trim((string) ($_POST['upload_id'] ?? ''));

$scope = ai_pics_upload_scope_sql('a');
$albumSql = "SELECT `a`.`id` FROM `".$prefix."albums` `a`".$scope['sql']." AND `a`.`id` = ? LIMIT 1";
if ($scope['sql'] === '') {
$albumSql = "SELECT `a`.`id` FROM `".$prefix."albums` `a` WHERE `a`.`id` = ? LIMIT 1";
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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
ai_pics_json_response(false, 'Only POST is allowed.');
}

if ($albumId === '' || !is_array($albumRow) || empty($albumRow['id'])) {
ai_pics_json_response(false, 'Please open AI Pics from a valid album first.');
}

if ($uploadId === '') {
ai_pics_json_response(false, 'No image was selected for AI crop save.');
}

$uploadRow = ai_pics_fetch_upload_row($con, $prefix, $albumId, $uploadId);
if (!is_array($uploadRow) || empty($uploadRow['url'])) {
ai_pics_json_response(false, 'The selected image could not be found for this account.');
}

$saved = false;
$targetPath = ai_pics_target_disk_path((string) $uploadRow['url']);

if (isset($_FILES['ai_crop_file']) && is_array($_FILES['ai_crop_file']) && (int) ($_FILES['ai_crop_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($_FILES['ai_crop_file']['tmp_name'])) {
$mime = ai_pics_upload_image_mime((string) $_FILES['ai_crop_file']['tmp_name'], (string) ($_FILES['ai_crop_file']['name'] ?? ''));
if ($mime !== '') {
$saved = ai_pics_convert_upload_to_webp((string) $_FILES['ai_crop_file']['tmp_name'], $targetPath, $mime, 90);
}
} elseif (!empty($_POST['ai_crop_data'])) {
$saved = ai_pics_convert_data_url_to_webp((string) $_POST['ai_crop_data'], $targetPath, 90);
}

if (!$saved) {
ai_pics_json_response(false, 'Failed to save the cropped AI image as WebP.');
}

ai_pics_json_response(true, 'AI picture saved successfully for '.(string) $uploadRow['url'].'.', array(
'upload_id' => (string) $uploadRow['id'],
'source_file' => (string) $uploadRow['url'],
'ai_file' => ai_pics_target_filename((string) $uploadRow['url']),
));
