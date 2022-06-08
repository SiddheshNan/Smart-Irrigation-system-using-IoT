<?php
header('Access-Control-Allow-Origin: *');


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
header('Access-Control-Allow-Headers: authorization');
header('Access-Control-Allow-Methods: POST');
exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("HTTP/1.0 405 Method Not Allowed");
    die ("method not allowed");
}


if ($_SERVER['HTTP_AUTHORIZATION']!=="P8HhxMfdzVVg"){
    header("HTTP/1.0 401 Unauthorized ");
    die ("Unauthorized");
}



$th_auth_token = '';

$th_username = "";

$th_device1 = "";

$dataObj = null;
$dataResObj = null;

$posedData = $_POST['state'];


function url_v2($x,$y){ // x = th_device y = th_resource[1 / 2 / 3 / 4]
    global $th_username,$th_auth_token;
    $url = "https://api.thinger.io/v2/users/{$th_username}/devices/{$x}/Device{$y}?authorization={$th_auth_token}";
    return $url;
}


function doPostReq($a,$b){
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


if($posedData == 'on') {$dataObj->in = false;}
else if($posedData == 'off'){$dataObj->in = true;}

if($posedData == 'on' || $posedData == 'off'){

    $dataObj = json_encode($dataObj);

    $url = url_v2($th_device1,'1');

    $req = doPostReq($url,$dataObj);

    if($req  == '')
    {
        $dataResObj->output = "success";
    }
    else
    {
        $dataResObj->output = "fail";;
        $dataResObj->fail_cause = $req;
    }
    $dataResObj = json_encode($dataResObj);
    echo $dataResObj ;

}
else
{ // Error return for invalid commands
    $dataResObj->error = "invalid request";
    $dataResObj = json_encode($dataResObj);
    echo $dataResObj;
}

exit;

