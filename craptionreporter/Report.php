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



$uid = "";
if (isset($json_data['uid'])) {
    $uid = $json_data['uid'];
}

$debug = true;
if (isset($json_data['debug'])) {
    $debug = boolval($json_data['debug']);
}

$crashes = $json_data['crashes'];
$exceptions = $json_data['exceptions'];

$logs = "";
if (isset($json_data['logs'])) {
    $logs = $json_data['logs'];
}

$appVersionCode = 0;
if (isset($json_data['appVersionCode'])) {
    $appVersionCode = $json_data['appVersionCode'];
}
    

require_once ('DBHandler.php');
require_once ('DBConfig.php');
$conn = DBHandler::getInstance(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)->connect();
$stmt = null;
$result = array();

$cid = 0;
if (!empty($uid)) {
    try {
        $query = "SELECT * FROM client WHERE uid=:uid";
        $stmt = $conn->prepare($query);
        $flag = $stmt->execute(array(':uid' => $uid));
        if ($flag && $stmt->rowCount() == 1) {
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $cid = intval($client["id"]);
        }
    } catch (\Throwable $th) {}
}


foreach ($crashes as $crash) {

    $file_name = $crash["file_name"];
    $occur_date = substr($file_name, 0, 19);
    $stack_trace = getStackTrace($crash["stack_trace"], $appVersionCode, $occur_date);
    if (insert($conn, $cid, $ir_time, $stack_trace, true, $occur_date, $debug)) {
        array_push($result, $file_name);
    }

}

foreach ($exceptions as $exception) {
    
    $file_name = $exception["file_name"];
    $occur_date = substr($file_name, 0, 19);
    $stack_trace = getStackTrace($exception["stack_trace"], $appVersionCode, $occur_date);
    if (insert($conn, $cid, $ir_time, $stack_trace, false, $occur_date, $debug)) {
        array_push($result, $file_name);
    }

}


if (!empty($logs) && $cid > 0) {
    try {
        $query = "SELECT * FROM log WHERE cid=:cid";
        $stmt = $conn->prepare($query);
        $flag = $stmt->execute(array(':cid' => $cid));
        if ($flag) {
            if ($stmt->rowCount() == 0) {
                $query = "INSERT INTO log (cid, timestamp, body) VALUES ('".$cid."', '".$ir_time."', '".$logs."')";

            } else if ($stmt->rowCount() == 1) {
                // TODO check if body size is big, insert in new row
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $rowId = intval($row["id"]);
                $query = "UPDATE log SET timestamp='".$ir_time."', body=CONCAT(IFNULL(body, ''), '".$logs."') WHERE id=".$rowId;

            } else {
                // TODO append to newest row
            }
            $stmt = $conn->prepare($query);
            $flag = $stmt->execute();
        }
    } catch (\Throwable $th) {}
}



echo json_encode($result);
if ($stmt!=null)
    $stmt->closeCursor();
$stmt = null;
$conn = null;
exit();



//*******************************************************************************

function getStackTrace($stack_trace, $appVersionCode, $occur_date) {

    $stackFileName = null;

    try {
        if (strpos($stack_trace, 'retrace:')===0) {
            $stack_trace = substr($stack_trace, 8, strlen($stack_trace));

            $mappingFileName = 'mappings/mapping-'.$appVersionCode.'.txt';
            $stackFileName = $occur_date.'_'.rand(0, 1000).'.txt';

            $temp = fopen($stackFileName, 'w');
            fwrite($temp, $stack_trace, strlen($stack_trace));
            fflush($temp);
            fclose($temp);

            $stackFileNameBackSlashed = str_replace(" ", "\\ ", $stackFileName);
            $retracedStack = null;
            exec('java -jar retrace.jar -verbose '.$mappingFileName.' '.$stackFileNameBackSlashed.'  2>&1', $retracedStack);

            if ($retracedStack == null) {
                unlink($stackFileName);
                return $stack_trace;
            }

            $stack_trace = "";
            foreach ($retracedStack as $line) {
                $stack_trace .= $line."\n";
            }

            unlink($stackFileName);
        }
    } catch (\Throwable $th) {
        $stack_trace = 'retrace error: '.$e->getMessage();
        if ($stackFileName != null)
            unlink($stackFileName);
    }

    return $stack_trace;

}



function insert($conn, $cid, $ir_time, $stack_trace, $fatal, $occur_date, $debug) {

    try {

        $query = "INSERT INTO reports (
            cid, 
            timestamp, 
            occur_date,
            debug, 
            stack_trace, 
            fatal
        ) VALUES (
            :cid, 
            :timestamp,
            :occur_date,
            :debug, 
            :stack_trace,
            :fatal
        )";

        $stmt = $conn->prepare($query);
        $flag = $stmt->execute(array(
            ':cid' => $cid, 
            ':timestamp' => $ir_time,
            ':occur_date' => $occur_date,
            ':debug' => $debug ? 1 : 0,
            ':stack_trace' => $stack_trace,
            ':fatal' => $fatal ? 1 : 0
        ));

        return $flag;

    } catch (Exception $e) {
        
    }

    return false;

}