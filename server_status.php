<?php
header('Content-Type: application/json');

// List of servers (name => IP)
$servers = [
    'BA-HV4' => '10.0.50.8',
    'BA-HV21' => '10.0.50.16',
    'BA-HV6' => '10.0.50.40',
    'BA-HV3' => '10.0.50.55',
    'PNAS' => '10.0.50.14',
    // 'SNAS' => '10.0.52.45',
    'UNV-01' => '10.0.44.10',
    'UNV-02' => '10.0.44.5',
    // 'BA-DC-02' => '10.0.50.5',
    'BA-Wiki' => '10.0.50.21',
    //'BA-PrintSRV' => '10.0.50.11',
    'BA-FileSRV' => '10.0.50.3',
    'BA-srv4' => '10.0.50.23',
    'Osticket' => '10.0.50.47',
    'Attendance' => '10.0.50.6',
    'Snipe-IT' => '10.0.50.25',
    'blogs' => '10.0.50.28',
    'Observium' => '10.0.50.27',
    "bia-dc-01" => '10.0.50.2',
    "bia-dc-02" =>  '10.0.50.7',
    "facility server" => '10.0.50.13',
    "veam backup" => '10.0.50.57',
    "ba-hv3" => '10.0.50.55'
];

$results = [];

foreach ($servers as $name => $ip) {
    $pingResult = exec("ping -n 1 $ip", $output, $status);
    $results[] = [
        'name' => $name,
        'ip' => $ip,
        'online' => ($status === 0)
    ];
}

echo json_encode(['servers' => $results]);
