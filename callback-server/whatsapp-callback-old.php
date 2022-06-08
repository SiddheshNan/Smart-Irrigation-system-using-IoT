<?php
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;

$thinger_auth_token = '';

$thinger_username = "";

$thinger_device1 = "";

//$thinger_resource_1 = "Device1";

$sid = '';
$token = '';

$client = new Client($sid, $token);

header("content-type: text/xml");

$postedDataFrmTwilio = $_REQUEST['Body'];

function postURL_v2($x,$y){
    // x = thinger_device y = thinger_resource[1 / 2 / 3 / 4]
    global $thinger_username,$thinger_auth_token;
    $postURL_thinger_v2 = "https://api.thinger.io/v2/users/{$thinger_username}/devices/{$x}/Device{$y}?authorization={$thinger_auth_token}";
    return $postURL_thinger_v2;
}


function doGetReq($z){
    // z = thinger_device
    global $thinger_username,$thinger_auth_token;
    $doGetReq_response = file_get_contents("https://api.thinger.io/v1/users/{$thinger_username}/devices/{$z}/stats?authorization={$thinger_auth_token}");
    return $doGetReq_response;
}


function doGet_PinStatus($a,$b){
    // z = thinger_device b = motor status
    global $thinger_username,$thinger_auth_token;
    $doGetReq_response = file_get_contents("https://api.thinger.io/v2/users/{$thinger_username}/devices/{$a}/{$b}?authorization={$thinger_auth_token}");


    return $doGetReq_response;
}


function doPostReq($a,$b){
    $ch1 = curl_init($a);
    curl_setopt($ch1, CURLOPT_POST, 1);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $b);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch1, CURLOPT_TIMEOUT, 30);
    $doPostReq_response = curl_exec($ch1);
    curl_close($ch1);
    return $doPostReq_response;
}


function doResponse($i,$j,$k,$l){
    $response = new MessagingResponse();
    if($i == '1'){
        $i = "true";
        $response->message("IP is : ". $j);
        $response->message("Transmitted Data : ".$k." Bytes");
        $response->message("Recived Data : ".$l." Bytes");
        $get_ip_geoloaction = json_decode(file_get_contents("https://ipinfo.io/{$j}/json"));
        $geo_concat = "Approximate location is : ".$get_ip_geoloaction->city .", ". $get_ip_geoloaction->region .", ". $get_ip_geoloaction->country;
        $response->message($geo_concat);
    }
    else if($i == '0')
    {$i = "false. Please check if irrigation device is connected to the internet.";}
    else{$i = "Error occured while getting state from server";}
    $response->message("is device connected : ".$i);
    return $response;

}

function doGetTempData(){
    global $thinger_device1;
    $getpin_data_temp = doGet_PinStatus($thinger_device1,"temp");
    $getpin_data_moist = doGet_PinStatus($thinger_device1,"moist");


    $getpin_data_tempD = json_decode($getpin_data_temp);
    $getpin_StateTempInfo = $getpin_data_tempD->out;

    $getpin_data_moistD = json_decode($getpin_data_moist);
    $getpin_StateMoistInfo = $getpin_data_moistD->out;
    
    $getpin_StateTempInfo = $getpin_StateTempInfo . " C";

    if(($getpin_StateTempInfo == null)&&($getpin_StateMoistInfo == null)){
        $getpin_StateTempInfo = "unavail";
        $getpin_StateMoistInfo = "unavail";
    }

    $SendnewDataObj11->temp=$getpin_StateTempInfo;
    $SendnewDataObj11->moist=$getpin_StateMoistInfo;
    return  $SendnewDataObj11;

}


if($postedDataFrmTwilio  == 'motor on' || $postedDataFrmTwilio  == 'Motor on' || $postedDataFrmTwilio  == 'motor On' || $postedDataFrmTwilio  == 'Motor On')
{

    $newDataObj->in = true;
    $newDataObjJSON = json_encode($newDataObj);
    $get_url_v2 = postURL_v2($thinger_device1,'1');
    $doPostReq_response = doPostReq($get_url_v2,$newDataObjJSON);
    if($doPostReq_response == '')
    {
        $response = new MessagingResponse();
        $response->message("Motor is turned on");
        echo $response;
    }
    else
    {
        $doPostReq_responseD = json_decode($doPostReq_response);
        $get_error_msg =  $doPostReq_responseD->error->message;
        if (empty($get_error_msg)){$get_error_msg="Failed to connect to the device."; }
        $response = new MessagingResponse();
        $response->message("Error : ". $get_error_msg);
        $response->message("Please check if irrigation device is connected to the internet");
        echo $response;
    }
}



else if($postedDataFrmTwilio  == 'motor off' || $postedDataFrmTwilio  == 'Motor off' || $postedDataFrmTwilio  == 'motor Off' || $postedDataFrmTwilio  == 'Motor Off')
{

    $newDataObj->in = false;
    $newDataObjJSON = json_encode($newDataObj);
    $get_url_v2 = postURL_v2($thinger_device1,'1');
    $doPostReq_response = doPostReq($get_url_v2,$newDataObjJSON);

    if($doPostReq_response == '')
    {
        $response = new MessagingResponse();
        $response->message("Motor is turned off");
        echo $response;
    }
    else
    {
        $doPostReq_responseD = json_decode($doPostReq_response);
        $get_error_msg =  $doPostReq_responseD->error->message;
        $response = new MessagingResponse();
        if (empty($get_error_msg)){$get_error_msg="Failed to connect to the device."; }
        $response->message("Error : ". $get_error_msg);
        $response->message("Please check if irrigation device is connected to the internet");
        echo $response;
    }
}


else if($postedDataFrmTwilio  == 'device status' || $postedDataFrmTwilio  == 'Device status' || $postedDataFrmTwilio  == 'device Status' || $postedDataFrmTwilio  == 'Device Status')
{
    $doGetReq_response = doGetReq($thinger_device1);
    $doGetReq_JSONd = json_decode($doGetReq_response);

    $checkConnected_res = $doGetReq_JSONd->connected;
    $checkIP_res = $doGetReq_JSONd->ip_address;
    $checkTX_res = $doGetReq_JSONd->tx_bytes;
    $checkRX_res = $doGetReq_JSONd->rx_bytes;

    $doResponse12 = doResponse($checkConnected_res,$checkIP_res,$checkTX_res,$checkRX_res);
    echo $doResponse12;

}


else if($postedDataFrmTwilio  == 'motor status' || $postedDataFrmTwilio  == 'Motor status' || $postedDataFrmTwilio  == 'motor Status' || $postedDataFrmTwilio  == 'Motor Status')
{
    $getpin_data = doGet_PinStatus($thinger_device1,"motor_status");
    $getpin_dataD = json_decode($getpin_data);
    $getpin_Stateinfo = $getpin_dataD->out;
    $response = new MessagingResponse();

    $getTemp = doGetTempData();

    $response->message("Moisture: ". $getTemp->moist . " | Temp: ". $getTemp->temp);

    if($getpin_Stateinfo == 'ON'){
        $response->message("Motor is ON");
        echo $response;
    }
    else if($getpin_Stateinfo == 'OFF'){
        $response->message("Motor is OFF");
        echo $response;
    }
    else{
        if (empty($getpin_data)){$getpin_data="Failed to get motor state from server, please check if irrigation device is connected to the internet.";}
        $response->message("Error : ".$getpin_data);
        echo $response;
    }

}


else
{ // Error return for invalid commands
    $response = new MessagingResponse();
    $response->message("Error : Invalid Request");
    echo $response;
}
exit;
