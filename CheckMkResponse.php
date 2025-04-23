<?php
// Your Checkmk server details
$checkmk_url = 'http://10.0.50.24/monitoring/check_mk/api/1.0/domain-types/host/collections/all';
$username = 'grafanaUser'; // Replace with your automation username
$automation_secret = 'March151993@'; // Replace with the actual Automation Secret

// Initialize cURL session
$ch = curl_init($checkmk_url);

// Set cURL options for HTTP Basic Authentication
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$automation_secret");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json'
));

// Execute the API request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// Output the raw API response for verification
echo '<pre>Raw API Response: ';
print_r($response);
echo '</pre>';

// Decode JSON response
$data = json_decode($response, true);

// Output the decoded JSON for analysis
if ($data === null) {
    echo "JSON decoding failed. Error: " . json_last_error_msg();
} else {
    echo '<pre>Decoded JSON Structure: ';
    print_r($data);
    echo '</pre>';
}
?>
