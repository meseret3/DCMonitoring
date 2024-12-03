<?php
// Checkmk Livestatus API endpoint (using TCP connection)
$livestatus_host = '10.0.50.24';  // Checkmk server
$livestatus_port = 6557;  // Default Livestatus TCP port

// The query to fetch all hosts' statuses
$query = "GET hosts\nColumns: name state plugin_output\n\n";

// Create a socket connection
$socket = fsockopen($livestatus_host, $livestatus_port, $errno, $errstr, 10);

if (!$socket) {
    echo "Error: Could not connect to Livestatus API - $errstr ($errno)<br>";
    exit;
}

// Send the query
fwrite($socket, $query);

// Read the response
$response = '';
while (!feof($socket)) {
    $response .= fgets($socket);
}
fclose($socket);

// Parse the response
$lines = explode("\n", trim($response));
if (!empty($lines)) {
    echo "<h2>Host States from Livestatus API</h2>";
    foreach ($lines as $line) {
        $parts = explode(';', $line);
        if (count($parts) >= 3) {
            echo "Host: " . htmlspecialchars($parts[0]) . "<br>";
            echo "State: " . ($parts[1] == 0 ? 'OK' : ($parts[1] == 1 ? 'WARNING' : 'CRITICAL')) . "<br>";
            echo "Plugin Output: " . htmlspecialchars($parts[2]) . "<br><hr>";
        }
    }
} else {
    echo "No data returned from Livestatus API.<br>";
}
?>
