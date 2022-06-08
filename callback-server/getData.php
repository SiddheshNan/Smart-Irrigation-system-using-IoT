<?php

header('Access-Control-Allow-Origin: *');
require __DIR__ . '/../vendor/autoload.php';
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;

function doSendMsg($x, $y){
    // X = number , Y = Message
    global $twilio, $sid, $token;
    
    $message = $twilio->messages
                  ->create("whatsapp:+91{$x}", // to
                           array(
                               "from" => "whatsapp:+14155238886",
                               "body" => $y
                           )
                  );
                  
    return $message;
    
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') 
{
    if(!isset($_GET["state"]))
    {
        die("405 Method Not allowed");
    }
    else if(isset($_GET["state"]))
    {
        if($_GET["state"] == "on" || $_GET["state"] == "off")
        {
            if(!isset($_GET["auth"]))
            {
                die("403 Forbidden");
            }
            else if(isset($_GET["auth"]))
            {
               if($_GET["auth"] == "P8HhxMfdzVVg")
                {
                    $sid = '';
                    $token = '';
                    $twilio = new Client($sid, $token);
                    date_default_timezone_set("Asia/Kolkata");
                    $out_msg = "Motor Turned ". strtoupper($_GET["state"]) ." Automatically.\nAt " . strtoupper(date('m/d/Y h:i:s a', time()));
                    $msg = doSendMsg("",$out_msg);
                    print($msg->sid);
                }
                else
                {
                    die("401 Unauthorized");
                }
            }
            else
            {
                die("501 Something went wrong..");
            }
        }
        else
        {
            die("400 Invalid Query");
        }
    }
    else
    {
        die("501 Something went wrong..");
    }
}
else
{
    die("405 Method Not allowed");
}
