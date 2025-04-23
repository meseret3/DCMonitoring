<?php
// Your Checkmk server details
$checkmk_url = 'http://10.0.50.24/monitoring/check_mk/api/1.0/domain-types/host/collections/all';
$username = 'grafanaUser'; 
$automation_secret = 'March151993@'; 

// Initialize cURL session
$ch = curl_init($checkmk_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$automation_secret");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
$response = curl_exec($ch);
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

if ($data === null) {
    echo "JSON decoding failed. Error: " . json_last_error_msg();
} else {
    echo "<h2>Host Data Overview</h2>";

    if (isset($data['value']) && is_array($data['value'])) {
        foreach ($data['value'] as $hostIndex => $host) {
            echo "<h3>Host [$hostIndex]:</h3>";
            echo "ID: " . (isset($host['id']) ? $host['id'] : 'N/A') . "<br>";
            echo "Title: " . (isset($host['title']) ? $host['title'] : 'N/A') . "<br>";
            echo "Name: " . (isset($host['extensions']['name']) ? $host['extensions']['name'] : 'N/A') . "<br>";

            if (isset($host['links'][0]['href'])) {
                $host_url = $host['links'][0]['href'];
                echo "Link to host details: <a href='$host_url'>$host_url</a><br>";

                // Fetch host details from the link
                $ch_host = curl_init($host_url);
                curl_setopt($ch_host, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_host, CURLOPT_USERPWD, "$username:$automation_secret");
                curl_setopt($ch_host, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                $host_response = curl_exec($ch_host);
                curl_close($ch_host);

                $host_data = json_decode($host_response, true);
                
                // Display host state or status
                if (isset($host_data['state'])) {
                    echo "State: " . $host_data['state'] . "<br>";
                } elseif (isset($host_data['status'])) {
                    echo "Status: " . $host_data['status'] . "<br>";
                } else {
                    echo "State information not available.<br>";
                }
            }
            echo "<hr>";
        }
    } else {
        echo "<p>No hosts found in the 'value' array.</p>";
    }
}
?>
