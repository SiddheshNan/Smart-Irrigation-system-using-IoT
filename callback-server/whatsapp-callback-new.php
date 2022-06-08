<?php
header('Access-Control-Allow-Origin: *');
header("content-type: text/xml");

require __DIR__ . '/../vendor/autoload.php';

use Twilio\TwiML\MessagingResponse;

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("HTTP/1.0 405 Method Not Allowed");
    die ("method not allowed");
}

$th_auth_token = '';

$th_username = "";

$th_device1 = "";

$dataObj = null;

$postedData = strtolower($_REQUEST['Body']);

function createURL($x,$y,$z){ // x = th_device y = th_resource[1 / 2 / 3 / 4] z = v1/v2
    global $th_username,$th_auth_token;
    $url = "https://api.thinger.io/{$z}/users/{$th_username}/devices/{$x}/{$y}?authorization={$th_auth_token}";
    return $url;
}


function doGetReq($z){ // z = url
    $res = file_get_contents($z);
    return $res;
}

function doPostReq($a,$b){ // a = URL b = Body
    $ch = curl_init($a);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $b);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function doDeviceResponse($i,$j,$k,$l,$m){
    $response = new MessagingResponse();
    if($i == '1'){
        $response->message("Device connected : YES");
        $response->message("IP is : ". $j);
        $response->message("Transmitted Data : ".$k." Bytes");
        $response->message("Received Data : ".$l." Bytes");
        $geoip = json_decode(file_get_contents("https://ipinfo.io/{$j}/json"));
        $ip_geo_concat = "Approximate location is : ".$geoip->city .", ".$geoip->region.", ".$geoip->country;
        $response->message($ip_geo_concat);
        $outdate =  date_create(date('Y-m-d H:i:s', $m/1000), new DateTimeZone("UTC"))->setTimezone(new DateTimeZone("Asia/Kolkata"))->format("r");
       // $outdate = date('r', $m/1000);
        $response->message("Last Connected : " . $outdate);
        
    }
    else if($i == '0'){
        $response->message("Device connected : NO");
        $response->message("Please make sure Irrigation Device is Connected to the Internet.");
    }
    else{
        $response->message("An error occurred while getting data from server.");
    }
    return $response;

}





if($postedData  == 'motor on')
{

    $dataObj->in = false;
    $dataObj = json_encode($dataObj);
    $url = createURL($th_device1,"Device1","v2");
    $req = doPostReq($url,$dataObj);
    
    
    if($req == '')
    {
        $response = new MessagingResponse();
        $response->message("Motor is turned ON");
        echo $response;
    }
    else
    {
        $req = json_decode($req);
        $res =  $req->error->message;
        if (empty($res)){
            $res = "Failed to Connect to the Irrigation device.";
        }
        $response = new MessagingResponse();
        $response->message("Error : ". $res);
        $response->message("Please make sure Irrigation Device is Connected to the Internet");
        echo $response;
    }
}

else if($postedData  == 'motor off')
{

    $dataObj->in = true;
    $dataObj = json_encode($dataObj);
    $url = createURL($th_device1,"Device1","v2");
    $req = doPostReq($url,$dataObj);
    
    
    if($req == '')
    {
        $response = new MessagingResponse();
        $response->message("Motor is turned OFF");
        echo $response;
    }
    else
    {
        $req = json_decode($req);
        $res =  $req->error->message;
        if (empty($res)){
            $res = "Failed to Connect to the Irrigation device.";
        }
        $response = new MessagingResponse();
        $response->message("Error : ". $res);
        $response->message("Please make sure Irrigation Device is Connected to the Internet");
        echo $response;
    }
}



else if($postedData  == 'device status')
{
    $url = createURL($th_device1,"stats","v1");

    $req = json_decode(doGetReq($url));

    $conn = $req->connected;
    $ip = $req->ip_address;
    $tx = $req->tx_bytes;
    $rx = $req->rx_bytes;
    $last_time = $req->connected_ts;
    
    $resp = doDeviceResponse($conn,$ip,$tx,$rx,$last_time);
    echo $resp;

}


else if($postedData  == 'motor status')
{

    $motor = json_decode(doGetReq(createURL($th_device1,"motor_status","v2")))->out;
    $temp = json_decode(doGetReq(createURL($th_device1,"temp","v2")))->out;
    $moist = json_decode(doGetReq(createURL($th_device1,"moist","v2")))->out;

    if(($temp == null)&&($moist == null)){
        $temp = "offline";
        $moist = "offline";
    }

    if($temp!="offline"){
        $temp = $temp . " C";
    }

    $response = new MessagingResponse();

    if($motor == 'ON'){
        $response->message("Motor is ON");
        $response->message("Moisture: ". $moist . " | Temp: ". $temp);
        echo $response;
    }
    else if($motor == 'OFF'){
        $response->message("Motor is OFF");
        $response->message("Moisture: ". $moist . " | Temp: ". $temp);
        echo $response;
    }
    else{
        if (empty($motor)){
            $response->message("Failed to get Motor State from the Server.");
            $response->message("Please make sure Irrigation Device is Connected to the Internet.");
            echo $response;
        }
        else{
            $response->message("Error : ". $motor);
            echo $response;
        }

    }

}



else
{ // Error return for invalid commands
    $response = new MessagingResponse();
    $response->message("Error : Invalid Request");
    echo $response;
}
exit;
