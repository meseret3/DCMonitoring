<?php
header('Content-Type: application/json');

// List of cameras and IPs
$cameras = [
    "C_Elem2F" => "10.0.51.43",
    "C_ElemGF" => "10.0.51.41",
    "C_GC004" => "10.0.48.41",
    "C_GC101" => "10.0.48.40",
    "C_GC105" => "10.0.48.45",
    "C_GC207" => "10.0.48.47",
    "C_GCBalcony" => "10.0.48.43",
    "C_GCCourt" => "10.0.48.42",
    "C_GCFence" => "10.0.48.44",
    "C_GToilet" => "10.0.53.41",
    "C_GarageBack" => "10.0.59.44",
    "C_Gate2Down" => "10.0.62.41",
    "C_Gate2Up" => "10.0.62.40",
    "C_GymBack" => "10.0.59.43",
    "C_GymDoor" => "10.0.59.41",
    "C_GymToilet" => "10.0.59.40",
    "C_Hub" => "10.0.47.40",
    "C_KGPlayGround" => "10.0.49.41",
    "C_MainGate" => "10.0.58.41",
    "C_MobilePhoneRoom1" => "10.0.16.45",
    "C_MobilePhoneRoom2" => "10.0.16.43",
    "C_OroLoundRiver" => "10.0.61.40",
    "C_OromiyaFence" => "10.0.60.41",
    "C_OromiyaRiver" => "10.0.60.40",
    "C_OromiyaTower" => "10.0.44.33",
    "C_PTZ" => "10.0.52.43",
    "C_Parking" => "10.0.59.42",
    "C_PigeonHole" => "10.0.52.44",
    "C_RFL" => "10.0.52.41",
    "C_RFR" => "10.0.52.40",
    "C_RickerField" => "10.0.16.46",
    "C_StudyHall1" => "10.0.16.41",
    "C_StudyHall2" => "10.0.16.42",
    "C_Tana" => "10.0.53.42",
    "C_Tukul" => "10.0.59.45",
    "GYMFlex" => "10.0.59.48"
];

$downCameras = [];

foreach ($cameras as $name => $ip) {
    $pingResult = shell_exec("ping -n 1 -w 1000 $ip");
    if (strpos($pingResult, 'Received = 1') === false) {
        $downCameras[] = $name;
    }
}

echo json_encode(["down" => $downCameras]);
?>

