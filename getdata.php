<?php
require 'config.php';

// Fetch the latest temperature and humidity data
$sql = "SELECT * FROM tbl_temperature ORDER BY id DESC LIMIT 1";
$result = $db->query($sql);
if (!$result) {
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

$row = $result->fetch_assoc();
$latestTemperature = $row['temperature'];
$latestHumidity = $row['humidity'];

// Function to check if a host is reachable via ping
function pingHost($host) {
    $pingResult = exec("ping -n 1 -w 1000 $host", $output, $status);
    return $status === 0 ? 'Online' : 'Offline';
}

// Check EthioTelecom and Safaricom statuses
$ethioStatus = pingHost("10.128.80.38");

$safaricomStatus = pingHost("10.47.3.25");

$safaricomStatus = pingHost("10.46.36.26");


// Return JSON response
echo json_encode([
    'temperature' => $latestTemperature,
    'humidity' => $latestHumidity,
    'EthioTelecom_Status' => $ethioStatus,
    'Safaricom_Status' => $safaricomStatus
]);
?>
