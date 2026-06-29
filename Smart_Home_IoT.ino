#include <WiFi.h>
#include <WiFiManager.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>


// =============================
// SENSOR PINS
// =============================

#define DHTPIN 4
#define DHTTYPE DHT11

#define MQ2_PIN 34
#define LDR_PIN 35

#define RELAY_PIN 27
#define BUZZER_PIN 25
#define BUTTON_PIN 18



DHT dht(
DHTPIN,
DHTTYPE
);



// =============================
// OLED
// =============================


#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64


Adafruit_SSD1306 display(
SCREEN_WIDTH,
SCREEN_HEIGHT,
&Wire,
-1
);



// =============================
// PHP SERVER LINKS
// CHANGE PC IP HERE
// =============================


String insertURL =
"http://10.221.146.224/smart_home/insert_data.php";


String getRelayURL =
"http://10.221.146.224/smart_home/get_relay.php?device_id=ESP32_01";


String updateRelayURL =
"http://10.221.146.224/smart_home/update_relay.php";


String getSettingsURL =
"http://10.221.146.224/smart_home/get_settings.php?device_id=ESP32_01";




// =============================
// SENSOR VARIABLES
// =============================


float temperature;

float humidity;

int gas;

int light;


String status;

String reason;

String lightState;



// =============================
// DATABASE SETTINGS
// =============================


float temperatureThreshold = 30;

int gasThreshold = 400;



// =============================
// RELAY VARIABLES
// =============================


bool relayState=false;

bool lastButtonState=HIGH;




// =============================
// TIMER VARIABLES
// =============================


unsigned long lastUpload=0;

unsigned long lastSettingsCheck=0;

unsigned long lastRelayCheck=0;



const long uploadInterval=5000;

const long settingsInterval=5000;

const long relayInterval=1000;





// =============================
// SETUP
// =============================


void setup()

{


Serial.begin(115200);



// PINS

pinMode(RELAY_PIN,OUTPUT);

pinMode(BUZZER_PIN,OUTPUT);

pinMode(BUTTON_PIN,INPUT_PULLUP);



digitalWrite(RELAY_PIN,HIGH);

digitalWrite(BUZZER_PIN,LOW);



// DHT

dht.begin();



// OLED

Wire.begin(21,22);



display.begin(
SSD1306_SWITCHCAPVCC,
0x3C
);



display.clearDisplay();

display.setTextSize(1);

display.setTextColor(WHITE);

display.setCursor(0,0);



display.println("SMART HOME");

display.println("STARTING");


display.display();



delay(2000);




// =============================
// WIFI MANAGER
// =============================


WiFiManager wm;



wm.setConnectTimeout(20);



// Connect saved WiFi

// If not available open setup portal


if(

!wm.autoConnect(

"SmartHome_Setup",

"12345678"

)

)

{


Serial.println("WiFi Failed");



Serial.println("Starting Backup AP");



WiFi.softAP(

"SmartHome_Backup",

"12345678"

);



Serial.print("Backup IP:");

Serial.println(

WiFi.softAPIP()

);


}


else

{


Serial.println("WiFi Connected");


Serial.print("ESP32 IP:");

Serial.println(

WiFi.localIP()

);


}



}









// =============================
// GET SETTINGS
// =============================


void getSettings()

{


HTTPClient http;


http.begin(

getSettingsURL

);



int response = http.GET();



if(response > 0)

{


String json = http.getString();



StaticJsonDocument<512> doc;



deserializeJson(doc,json);



temperatureThreshold =

doc["temperature_threshold"];



gasThreshold =

doc["gas_threshold"];



Serial.println("SETTINGS UPDATED");



}



http.end();


}









// =============================
// SENSOR LOGIC
// =============================


void updateSensors()

{


humidity = dht.readHumidity();


temperature = dht.readTemperature();




if(

isnan(humidity) ||

isnan(temperature)

)

{


humidity=0;

temperature=0;


}



gas = analogRead(MQ2_PIN);


light = analogRead(LDR_PIN);





// LIGHT

if(light < 2000)

{

lightState="BRIGHT";

}

else

{

lightState="DARK";

}





bool tempBad =

temperature > temperatureThreshold;



bool gasBad =

gas > gasThreshold;





// STATUS

if(

tempBad ||

gasBad

)

{


status="NOT IDEAL";



if(tempBad && gasBad)

{

reason="Temp + Gas high";

}

else if(tempBad)

{

reason="Temp high";

}

else

{

reason="Gas detected";

}



digitalWrite(

BUZZER_PIN,

HIGH

);



}

else

{


status="IDEAL";


reason="Good environment";


digitalWrite(

BUZZER_PIN,

LOW

);



}



}









// =============================
// GET RELAY STATUS
// =============================


void getRelayState()

{


HTTPClient http;



http.begin(

getRelayURL

);



int response = http.GET();



if(response > 0)

{


String state = http.getString();



state.trim();



if(state=="ON")

{

relayState=true;

}

else

{

relayState=false;

}



}



http.end();


}









// =============================
// UPDATE RELAY DATABASE
// =============================


void updateRelayDatabase()

{


HTTPClient http;



http.begin(

updateRelayURL

);



http.addHeader(

"Content-Type",

"application/x-www-form-urlencoded"

);




String data =

String("device_id=ESP32_01")

+

"&actuator_status="

+

String(

relayState ? "ON":"OFF"

);




int response = http.POST(data);



Serial.print("RELAY UPDATE:");

Serial.println(response);



http.end();



}









// =============================
// SEND SENSOR DATA
// =============================


void sendData()

{


HTTPClient http;



http.begin(

insertURL

);



http.addHeader(

"Content-Type",

"application/x-www-form-urlencoded"

);





String data =

String("device_id=ESP32_01");



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



// SETTINGS

if(

millis()-lastSettingsCheck >= settingsInterval

)

{


lastSettingsCheck = millis();



getSettings();



}



// SENSOR

updateSensors();






// RELAY FROM DATABASE

if(

millis()-lastRelayCheck >= relayInterval

)

{


lastRelayCheck = millis();



getRelayState();



}







// BUTTON

bool buttonState =

digitalRead(BUTTON_PIN);



if(

buttonState == LOW &&

lastButtonState == HIGH

)

{


relayState = !relayState;



updateRelayDatabase();



delay(300);


}



lastButtonState = buttonState;







// RELAY OUTPUT


if(relayState)

{

digitalWrite(

RELAY_PIN,

LOW

);

}

else

{

digitalWrite(

RELAY_PIN,

HIGH

);

}







// OLED


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

display.println(

relayState ? "ON":"OFF"

);



display.print("Status:");

display.println(status);



display.display();









// UPLOAD


if(

millis()-lastUpload >= uploadInterval

)

{


lastUpload = millis();



sendData();



}







// SERIAL


Serial.println("================");

Serial.print("Temperature:");

Serial.println(temperature);



Serial.print("Humidity:");

Serial.println(humidity);



Serial.print("Gas:");

Serial.println(gas);



Serial.print("Light:");

Serial.println(lightState);



Serial.print("Status:");

Serial.println(status);



Serial.print("Reason:");

Serial.println(reason);



Serial.print("Relay:");

Serial.println(

relayState ? "ON":"OFF"

);



delay(100);



}
