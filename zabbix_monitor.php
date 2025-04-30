<?php
// Zabbix server configuration
$zabbix_url = "http://10.0.50.26/zabbix/api_jsonrpc.php"; // Replace with your URL
$username = "Admin"; // Replace with your Zabbix username
$password = "B@gr8.1m"; // Replace with your Zabbix password
function zabbixApiRequest($url, $method, $params, $auth = null) {
    $data = [
        "jsonrpc" => "2.0",
        "method" => $method,
        "params" => $params,
        "id" => 1,
        "auth" => $auth,
    ];
    if ($method == "user.login") {
        unset($data["auth"]);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result["result"] ?? null;
}

// Authenticate
$authToken = zabbixApiRequest($zabbix_url, "user.login", [
    "user" => $username,
    "password" => $password
]);

// Hostname mapping
$hosts = [
    "Attendance",
    "BA-HV4",
    "DC-01",
    "DC-02",
    "Dismissal",
    "FileSRV",
    "Printer Server"
];

$data = [];

foreach ($hosts as $hostname) {
    // Get host ID
    $hostInfo = zabbixApiRequest($zabbix_url, "host.get", [
        "filter" => ["host" => $hostname],
        "output" => ["hostid"]
    ], $authToken);

    if (empty($hostInfo)) continue;

    $hostid = $hostInfo[0]["hostid"];

    // Get CPU, Memory, and Disk items using 'name' search
    $itemResponse = zabbixApiRequest($zabbix_url, "item.get", [
        "output" => ["itemid", "name", "key_", "lastvalue", "units"],
        "hostids" => $hostid,
        "search" => ["name" => ""], // Empty search gets all
        "sortfield" => "name"
    ], $authToken);

    $hostData = [
        "CPU Utilization" => null,
        "Memory Utilization" => null,
        "Disk Utilization" => null
    ];

    foreach ($itemResponse as $item) {
        if (stripos($item["name"], "CPU") !== false && stripos($item["name"], "utilization") !== false) {
            $hostData["CPU Utilization"] = $item["lastvalue"] . $item["units"];
        }
        if (stripos($item["name"], "Memory") !== false && stripos($item["name"], "utilization") !== false) {
            $hostData["Memory Utilization"] = $item["lastvalue"] . $item["units"];
        }
        if (stripos($item["name"], "Disk") !== false && (stripos($item["name"], "utilization") !== false || stripos($item["name"], "usage") !== false)) {
            $hostData["Disk Utilization"] = $item["lastvalue"] . $item["units"];
        }
    }

    $data[$hostname] = $hostData;
}

// Output as JSON
header("Content-Type: application/json");
echo json_encode($data, JSON_PRETTY_PRINT);
?>