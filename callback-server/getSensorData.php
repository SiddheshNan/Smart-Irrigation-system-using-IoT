<?php
header('Access-Control-Allow-Origin: *');

$th_auth_token = '';

$th_username = "";

$th_device1 = "";

$dataObj = null;

// ToDo: ADD device offline sensor data on when interface & whatsapp w/ moist & humid

function getPinStatus($a,$b){ // a = thinger_device b = motor status
    global $th_username,$th_auth_token;
    $res = file_get_contents("https://api.thinger.io/v2/users/{$th_username}/devices/{$a}/{$b}?authorization={$th_auth_token}");
    return $res;
}


    $motor = json_decode(getPinStatus($th_device1,"motor_status"))->out;
    $temp = json_decode(getPinStatus($th_device1,"temp"))->out;
    $moist = json_decode(getPinStatus($th_device1,"moist"))->out;
    
    
    if($motor == 'ON'){
    $dataObj->motor = "on";
    }
    else if($motor == 'OFF'){
    $dataObj->motor = "off";
    }
    else {
    $dataObj->motor = "offline";
    }
    
    if(($temp == null)&&($moist == null)){
        
        $temp = "offline";
        $moist = "offline";
        
    }
    
    $dataObj->temp = $temp;
    $dataObj->moist = $moist;
    
    echo json_encode($dataObj);

exit;


