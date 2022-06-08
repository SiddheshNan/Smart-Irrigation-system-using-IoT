#define THINGER_SERVER ""
#include <ESP8266WiFi.h>
#include <ThingerESP8266.h>

#define _DEBUG_
#define USERNAME ""
#define DEVICE_ID ""
#define DEVICE_CREDENTIAL ""
#define SSID ""
#define SSID_PASSWORD ""
ThingerESP8266 thing(USERNAME, DEVICE_ID, DEVICE_CREDENTIAL);

int sw = 5;

String state;
String donem;

void setup() {
  Serial.begin(115200);
  pinMode(sw, INPUT_PULLUP);
  
  delay(1000);
  thing.add_wifi(SSID, SSID_PASSWORD);
  
  thing["sw_status"] >> outputValue(state);

  if (digitalRead(sw)==1){
    state = "high";
    donem = "high";
    }

 if (digitalRead(sw)==0){
    state = "low";
    donem = "low";
    }

}

void loop() {
 


  if ((digitalRead(sw)==1)&&(donem=="high")){
    state = "high";
    donem = "low";
    pson data;
    data["sw_state"] = "high";
    thing.call_endpoint("one", data);
  }

 if ((digitalRead(sw)==0)&&(donem=="low")){
    state = "low";
    donem = "high";
    pson data1;
    data1["sw_state"] = "low";
    thing.call_endpoint("one", data1);
    }

  thing.handle();
}
