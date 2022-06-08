#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ThingerESP8266.h>
#include <ThingerWiFi.h>
#include "DHT.h"

#define DHTTYPE DHT11
#define dht_dpin 4
DHT dht(dht_dpin, DHTTYPE);

#define USERNAME ""
#define DEVICE_ID ""
#define DEVICE_CREDENTIAL ""
#define SSID ""
#define SSID_PASSWORD ""
ThingerESP8266 thing(USERNAME, DEVICE_ID, DEVICE_CREDENTIAL);

String motor_status;
String temp;
String hum;
String moist;

int motor = 5;
int dhtSensor = 4;
int moistSensor = 12;

String donem;

void setup() {
  Serial.begin(115200);
  pinMode(motor, OUTPUT);
  digitalWrite(motor, HIGH);
  pinMode(12, INPUT_PULLUP);

  dht.begin();
  delay(1000);
  thing.add_wifi(SSID, SSID_PASSWORD);
  thing["Device1"] << digitalPin(motor);
  thing["motor_status"] >> outputValue(motor_status);
  thing["temp"] >> outputValue(temp);
  thing["hum"] >> outputValue(hum);
  thing["moist"] >> outputValue(moist);

  if (digitalRead(12) == 0) {
    moist = "wet";
    donem = "wet";
    digitalWrite(motor, LOW);

  }

  else if (digitalRead(12) == 1) {
    moist = "dry";
    donem = "dry";
    digitalWrite(motor, HIGH);

  }


}

void loop() {

  float te = dht.readTemperature();
  float hu = dht.readHumidity();

  temp = String(te);
  hum = String(hu);

  if (digitalRead(5) == 1) {
    motor_status = "OFF";
  }

  if (digitalRead(5) == 0) {
    motor_status = "ON";
  }

  if ((digitalRead(12) == 0) && (donem == "wet")) {
    moist = "wet";
    donem = "dry";
    digitalWrite(5, HIGH);
    Serial.println("Starting HTTP Request for - Motor off");
    doHttpReq("off");
  }

  if ((digitalRead(12) == 1) && (donem == "dry")) {
    moist = "dry";
    donem = "wet";
    digitalWrite(5, LOW);
    Serial.println("Starting HTTP Request for - Motor on");
    doHttpReq("on");

  }

  thing.handle();
}

void doHttpReq(String m_state) {

  HTTPClient http;
  String urldata = "http://api.siddhesh.me/v2/hackathon-2019/new/getData?state=" + m_state + "&auth=P8HhxMfdzVVg";
  http.begin(urldata);
  int httpCode = http.GET();
  if (httpCode > 0) {
  String payload = http.getString();
  Serial.println(payload);
  }
  http.end();
  Serial.println("Ending HTTP Request for - Motor "+ m_state);

}
