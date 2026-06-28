#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// Define sensor's pins
#define DHTPIN 4
#define DHTTYPE DHT11
#define MQ2_PIN 34
#define LDR_PIN 35
#define RELAY_PIN 27
#define BUZZER_PIN 25
#define BUTTON_PIN 18

// Create DHT sensor object
DHT dht(DHTPIN,DHTTYPE);

// OLED display width and height
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64

// Create OLED display object
Adafruit_SSD1306 display(SCREEN_WIDTH,SCREEN_HEIGHT,&Wire,-1);

// =============================
// WIFI ACCESS POINT SETTINGS
// =============================

// ESP32 WiFi hotspot name
const char* ssid = "SmartHome_ESP32";

// ESP32 WiFi password
const char* password = "12345678";

// =============================
// PHP SERVER LINKS
// =============================

// URL used to upload sensor data into database
String insertURL =
"http://192.168.4.2/smart_home/insert_data.php";


// URL used to get relay status from database
String getRelayURL =
"http://192.168.4.2/smart_home/get_relay.php?device_id=ESP32_01";


// URL used to update relay status in database
String updateRelayURL =
"http://192.168.4.2/smart_home/update_relay.php";


// URL used to get threshold settings from database
String getSettingsURL =
"http://192.168.4.2/smart_home/get_settings.php?device_id=ESP32_01";

// =============================
// SENSOR VARIABLES
// =============================

// Stores temperature reading from DHT11
float temperature;
// Stores humidity reading from DHT11
float humidity;
// Stores gas value from MQ2 sensor
int gas;
// Stores light value from LDR sensor
int light;
// Stores system condition (IDEAL / NOT IDEAL)
String status;
// Stores warning reason
String reason;
// Stores light condition (BRIGHT / DARK)
String lightState;

// =============================
// DATABASE SETTINGS
// =============================

// If temperature exceeds this value, warning happens
float temperatureThreshold = 30;
// If MQ2 value exceeds this value, warning happens
int gasThreshold = 400;

// =============================
// RELAY VARIABLES
// =============================

// Stores relay ON/OFF condition
// true = ON
// false = OFF
bool relayState=false;

// Stores previous button state
// Used to detect button press
bool lastButtonState=HIGH;

// =============================
// TIMER VARIABLES
// =============================

// Stores last sensor upload time
unsigned long lastUpload=0;

// Stores last database setting check time
unsigned long lastSettingsCheck=0;

// Stores last relay check time
unsigned long lastRelayCheck=0;

// Upload sensor data every 5 seconds
const long uploadInterval=5000;

// Check database settings every 5 seconds
const long settingsInterval=5000;

// Check relay status every 1 second
const long relayInterval=1000;

void setup()

{

// Start serial communication for debugging
Serial.begin(115200);

// Set relay pin as output
pinMode(RELAY_PIN,OUTPUT);

// Set buzzer pin as output
pinMode(BUZZER_PIN,OUTPUT);

// Set button pin as input with internal pull-up resistor
pinMode(BUTTON_PIN,INPUT_PULLUP);

// Relay module is active LOW
// HIGH means relay OFF
digitalWrite(RELAY_PIN,HIGH);

// Turn buzzer OFF at startup
digitalWrite(BUZZER_PIN,LOW);

// Start DHT11 sensor
dht.begin();

// Start OLED communication
// SDA = GPIO21
// SCL = GPIO22
Wire.begin(21,22);

// Start OLED display
display.begin(SSD1306_SWITCHCAPVCC,0x3C);

// Clear OLED screen
display.clearDisplay();

// Set text size
display.setTextSize(1);

// Set text colour
display.setTextColor(WHITE);

// Set cursor position
display.setCursor(0,0);

// Display startup message
display.println("SMART HOME");
display.println("STARTING");

// Show text on OLED
display.display();


// Wait 2 seconds
delay(2000);

// Create ESP32 WiFi hotspot
WiFi.softAP(ssid,password);

// Print ready message
Serial.println("ESP32 READY");

// Print ESP32 IP address
Serial.println(WiFi.softAPIP());
}

// =============================
// GET SETTINGS FUNCTION
// Receives threshold values from database
// Example: temperature limit and gas limit
// =============================

void getSettings()
{
HTTPClient http;

http.begin(
getSettingsURL
);

int response =
http.GET();

if(response > 0){

String json =
http.getString();

StaticJsonDocument<512> doc;

deserializeJson(doc,json);

temperatureThreshold =doc["temperature_threshold"];

gasThreshold =doc["gas_threshold"];

Serial.println("SETTINGS UPDATED");

Serial.print("Temp Limit:");

Serial.println(temperatureThreshold);

Serial.print("Gas Limit:");

Serial.println(gasThreshold);

}

http.end();

}


// =============================
// SENSOR LOGIC
// =============================


void updateSensors()

{
humidity = dht.readHumidity();
temperature =dht.readTemperature();
if(
isnan(humidity) || isnan(temperature)
)
{
humidity=0;
temperature=0;
}

gas =analogRead(MQ2_PIN);
light =analogRead(LDR_PIN);

// LIGHT ONLY

if(light < 2000){
lightState="BRIGHT";
}
else{
lightState="DARK";
}

bool tempBad =temperature > temperatureThreshold;
bool gasBad = gas > gasThreshold;

// STATUS + BUZZER

if( tempBad ||gasBad){
status="NOT IDEAL";

if(tempBad &&gasBad){
reason="Temp + Gas high";
}

else if(tempBad){
reason="Temp high";
}

else{
reason="Gas detected";
}

digitalWrite(BUZZER_PIN,HIGH);
}

else{
status="IDEAL";
reason="Good environment";
digitalWrite(BUZZER_PIN,LOW);
}
}

// =============================
// GET RELAY STATUS
// =============================

void getRelayState(){
HTTPClient http;
http.begin(getRelayURL);

int response =http.GET();

if(response > 0){
String state =http.getString();
state.trim();
if(state=="ON"){
relayState=true;
}
else{
  relayState=false;
}
}


http.end();


}

// =============================
// UPDATE RELAY DATABASE
// =============================

void updateRelayDatabase(){
HTTPClient http;
http.begin(updateRelayURL);
http.addHeader("Content-Type","application/x-www-form-urlencoded");

String data =
String("device_id=ESP32_01")
+
"&actuator_status="
+
String(relayState ? "ON":"OFF");
int response =
http.POST(data);
Serial.print("RELAY UPDATE:");
Serial.println(response);
http.end();
}

// =============================
// SEND SENSOR DATA
// =============================

void sendData(){
HTTPClient http;
http.begin(insertURL);
http.addHeader("Content-Type","application/x-www-form-urlencoded");

String data =String("device_id=ESP32_01");
data += "&temperature=";
data += String(temperature);
data += "&humidity=";
data += String(humidity);
data += "&gas_level=";
data += String(gas);
data += "&light_level=";
data += String(light);
data += "&light_status=";
data += lightState;
data += "&status=";
data += status;

int response = http.POST(data);
  
Serial.print("PHP:");

Serial.println(response);
http.end();
}

// =============================
// LOOP
// =============================


void loop()
{

// GET DATABASE SETTINGS


if(
millis()-lastSettingsCheck >= settingsInterval
)
{

lastSettingsCheck = millis();
getSettings();
}

// READ SENSOR
updateSensors();

// GET RELAY FROM WEB

if(millis()-lastRelayCheck >= relayInterval)
{lastRelayCheck = millis();
getRelayState();
}

// =============================
// BUTTON CONTROL
// =============================
bool buttonState =digitalRead(BUTTON_PIN);

if(
buttonState == LOW &&
lastButtonState == HIGH
)

{
relayState =!relayState;
updateRelayDatabase();
Serial.print("BUTTON RELAY:"); 
Serial.println(relayState ? "ON":"OFF");

delay(300);
}

lastButtonState =buttonState;

// =============================
// RELAY OUTPUT
// =============================
if(relayState){
digitalWrite(RELAY_PIN,LOW);
}
else
{
digitalWrite(RELAY_PIN,HIGH);
}

// =============================
// OLED
// =============================

display.clearDisplay();
display.setCursor(0,0);
display.println("SMART HOME");
display.println("------------");
display.print("Temp:");
display.println(temperature);
display.print("Hum:");
display.println(humidity);
display.print("Gas:");
display.println(gas);
display.print("Light:");
display.println(lightState);
display.print("Relay:");
display.println(relayState ? "ON":"OFF");
display.print("Status:");
display.println(status);

display.display();

// =============================
// UPLOAD DATA
// =============================
if(millis()-lastUpload >= uploadInterval)
{
lastUpload = millis();
sendData();
}

// =============================
// SERIAL MONITOR
// =============================
Serial.println("================");
Serial.print("Temperature:");
Serial.println(temperature);
Serial.print("Humidity:");
Serial.println(humidity);
Serial.print("Gas:");
Serial.println(gas);
Serial.print("Light State:");
Serial.println(lightState);
Serial.print("Status:");
Serial.println(status);
Serial.print("Reason:");
Serial.println(reason);
Serial.print("Relay:");
Serial.println(relayState ? "ON":"OFF");

delay(100);
}
