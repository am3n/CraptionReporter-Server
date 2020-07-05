<?php

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */
error_reporting(E_ALL);

ini_set('default_socket_timeout', 30);
$ctx = stream_context_create(array('http'=> array(
    'timeout' => 30,
)));

//-----------------------------------------------------

date_default_timezone_set("Asia/Tehran");
$ir_time = date('Y-m-d H:i:s');


$data = file_get_contents('php://input', false, $ctx);
if (empty($data))
    exit();

$count = substr_count($data, "\":");
if ($count>100)
    exit();

$json_data = json_decode($data, TRUE);
if ($json_data===null && json_last_error()!==JSON_ERROR_NONE)
    exit();


$uid = $json_data['uid'];
$identification = $json_data['identification'];
$extraInfo = $json_data['extraInfo'];
$debug = boolval($json_data['debug']);
$appVersion = $json_data['appVersion'];
$osVersion = $json_data['osVersion'];
$psVersion = $json_data['psVersion'];
$cpu = $json_data['cpu'];
$deviceImei = $json_data['deviceImei'];
$deviceModel = $json_data['deviceModel'];
$deviceScreenClass = $json_data['deviceScreenClass'];
$deviceDpiClass = $json_data['deviceDpiClass'];
$deviceScreenSize = $json_data['deviceScreenSize'];
$deviceScreenDimensionsDpis = $json_data['deviceScreenDimensionsDpis'];
$deviceScreenDimensionsPixels = $json_data['deviceScreenDimensionsPixels'];

if (empty($uid)) {
    $uid = uniqid();
}

require_once ('DBHandler.php');
require_once ('DBConfig.php');
$conn = DBHandler::getInstance(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)->connect();
$stmt = null;

$query = "INSERT INTO client (uid, timestamp, identification, extra_info, debug, app_version, os_version, ps_version, cpu, device_imei, device_model, device_screen_class, device_dpi_class, device_screen_size, device_screen_dimensions_dpis, device_screen_dimensions_pixels) ".
         "VALUES ('".$uid."', '".$ir_time."', '".$identification."', '".$extraInfo."', ".($debug ? 1 : 0).", '".$appVersion."', '".$osVersion."', '".$psVersion."', '".$cpu."', '".$deviceImei."', '".$deviceModel."', '".$deviceScreenClass."', '".$deviceDpiClass."', '".$deviceScreenSize."', '".$deviceScreenDimensionsDpis."', '".$deviceScreenDimensionsPixels."') ".
         "ON DUPLICATE KEY UPDATE identification='".$identification."', extra_info='".$extraInfo."', debug=".($debug ? 1 : 0).", app_version='".$appVersion."', os_version='".$osVersion."', ps_version='".$psVersion."', cpu='".$cpu."', device_imei='".$deviceImei."', device_model='".$deviceModel."', device_screen_class='".$deviceScreenClass."', device_dpi_class='".$deviceDpiClass."', device_screen_size='".$deviceScreenSize."', device_screen_dimensions_dpis='".$deviceScreenDimensionsDpis."', device_screen_dimensions_pixels='".$deviceScreenDimensionsPixels."'";

$stmt = $conn->prepare($query);
$flag = $stmt->execute();
if ($flag) {
    echo $uid;
} else {
    echo "";
}