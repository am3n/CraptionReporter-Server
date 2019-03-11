<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

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


$crashes = $json_data["crashes"];
$user_identification = $json_data["user_identification"];
$app_version_code = $json_data["app_version_code"];
$os_version = $json_data["os_version"];
$cpu = $json_data["cpu"];
$device_imei = $json_data["device_imei"];
$device_model = $json_data["device_model"];
$device_screenclass = $json_data["device_screenclass"];
$device_dpiclass = $json_data["device_dpiclass"];
$device_screensize = $json_data["device_screensize"];
$device_screen_dimensions_dpis = $json_data["device_screen_dimensions_dpis"];
$device_screen_dimensions_pixels = $json_data["device_screen_dimensions_pixels"];


require_once ('DBHandler.php');
require_once ('DBConfig.php');
$conn = DBHandler::getInstance(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)->connect();
$stmt = null;
$result = array();

foreach ($crashes as $crash) {

    $file_name = $crash["file_name"];
    $occur_date = substr($file_name, 0, 19);

    $stack_trace = $crash["stack_trace"];

    try {
        if (strpos($stack_trace, 'retrace:')===0) {
            $stack_trace = substr($stack_trace, 8, strlen($stack_trace));

            $mappingFileName = 'mappings/mapping-'.$app_version_code.'.txt';
            $stackFileName = $occur_date.'_'.rand(0, 1000).'.txt';

            $temp = fopen($stackFileName, 'w');
            fwrite($temp, $stack_trace, strlen($stack_trace));
            fflush($temp);
            fclose($temp);

            $stackFileNameBackSlashed = str_replace(" ", "\\ ", $stackFileName);
            exec('java -jar retrace.jar -verbose '.$mappingFileName.' '.$stackFileNameBackSlashed.'  2>&1', $retracedStack);

            $stack_trace = "";
            foreach ($retracedStack as $line) {
                $stack_trace .= $line."\n";
            }

            unlink($stackFileName);
        }
    } catch (Exception $e) {
        $stack_trace = 'retrace error: '.$e->getMessage();
        unlink($stackFileName);
    }

    try {

        $query = "INSERT INTO crash_reports (
            timestamp,
            stack_trace,
            occur_date,
            user_identification,
            app_version_code,
            os_version,
            cpu,
            device_imei,
            device_model,
            device_screenclass,
            device_dpiclass,
            device_screensize,
            device_screen_dimensions_dpis,
            device_screen_dimensions_pixels
        ) VALUES (
            :timestamp,
            :stack_trace,
            :occur_date,
            :user_identification,
            :app_version_code,
            :os_version,
            :cpu,
            :device_imei,
            :device_model,
            :device_screenclass,
            :device_dpiclass,
            :device_screensize,
            :device_screen_dimensions_dpis,
            :device_screen_dimensions_pixels
        )";

        $stmt = $conn->prepare($query);
        $flag = $stmt->execute(array(
            ':timestamp' => $ir_time,
            ':stack_trace' => $stack_trace,
            ':occur_date' => $occur_date,
            ':user_identification' => $user_identification,
            ':app_version_code' => $app_version_code,
            ':os_version' => $os_version,
            ':cpu' => $cpu,
            ':device_imei' => $device_imei,
            ':device_model' => $device_model,
            ':device_screenclass' => $device_screenclass,
            ':device_dpiclass' => $device_dpiclass,
            ':device_screensize' => $device_screensize,
            ':device_screen_dimensions_dpis' => $device_screen_dimensions_dpis,
            ':device_screen_dimensions_pixels' => $device_screen_dimensions_pixels
        ));

        if ($flag)
            array_push($result, $file_name);

    } catch (Exception $e) {
        $result = array('insert error: '.$e->getMessage());
    }

}


echo json_encode($result);
if ($stmt!=null)
    $stmt->closeCursor();
$stmt = null;
$conn = null;
exit();