#include <WiFi.h>
#include <HTTPClient.h>
#include <Keypad.h>
#include <LiquidCrystal_I2C.h>
#include <Wire.h>
#include <ArduinoJson.h>
#include <vector>

// WiFi credentials
const char* ssid = "jesus";
const char* password = "12345678j";

// API URL (same for GET & POST)
const char* apiServer = "http://192.168.142.246/Cabinet11/cabinet_api.php";

// LCD setup (20x2)
LiquidCrystal_I2C lcd(0x27, 20, 2);

// Keypad setup
const byte ROWS = 4;
const byte COLS = 4;
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};

byte rowPins[ROWS] = {14, 27, 26, 25};  // ESP32 pins for rows
byte colPins[COLS] = {33, 32, 15, 4};   // ESP32 pins for cols

Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

String inputPIN = "";
std::vector<String> validPins;
unsigned long lastPinRefresh = 0;
const unsigned long PIN_REFRESH_INTERVAL = 30000; // Refresh PINs every 30 seconds

// Stepper motor pins (4-wire)
#define IN1 23
#define IN2 22
#define IN3 19
#define IN4 18
int step_number = 0;

void setup() {
  Serial.begin(115200);

  Wire.begin(5, 21); // SDA=5, SCL=21
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("Connecting WiFi");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    lcd.print(".");
  }

  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("IP:");
  lcd.setCursor(0,1);
  lcd.print(WiFi.localIP());
  delay(3000);

  lcd.clear();
  lcd.print("Ready...");

  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);

  getValidPins(); // Fetch valid PINs from server at startup
  lcd.clear();
  lcd.print("Enter PIN:");
  lcd.setCursor(0,1);
}

void loop() {
  char key = keypad.getKey();
  if (key) {
    if (key == '#') { // Submit PIN
      if (inputPIN.length() == 0) {
        lcd.clear();
        lcd.print("No PIN entered");
        delay(1500);
        lcd.clear();
        lcd.print("Enter PIN:");
        lcd.setCursor(0,1);
        return;
      }

      bool found = false;
      for (String validPin : validPins) {
        if (inputPIN == validPin) {
          found = true;
          break;
        }
      }

      if (found) {
        lcd.clear();
        lcd.print("Welcome");
        unlockCabinet();
        logAccess(inputPIN, "Granted");
      } else {
        lcd.clear();
        lcd.print("Denied Access");
        logAccess(inputPIN, "Denied");
        delay(3000);
      }
      inputPIN = "";
      lcd.clear();
      lcd.print("Enter PIN:");
      lcd.setCursor(0,1);
    } else if (key == '*') { // Clear input
      inputPIN = "";
      lcd.clear();
      lcd.print("Enter PIN:");
      lcd.setCursor(0,1);
    } else { // Add digit
      if (inputPIN.length() < 10) {  // limit PIN length
        inputPIN += key;
        lcd.setCursor(0,1);
        lcd.print(inputPIN);
      }
    }
  }
}

// GET valid PINs from server (expects JSON array of strings)
void getValidPins() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(apiServer);
    int httpCode = http.GET();

    if (httpCode > 0) {
      String payload = http.getString();
      Serial.println("Received PINs: " + payload);

      DynamicJsonDocument doc(1024);
      DeserializationError error = deserializeJson(doc, payload);
      if (!error && doc.is<JsonArray>()) {
        validPins.clear();
        for (JsonVariant v : doc.as<JsonArray>()) {
          validPins.push_back(String(v.as<const char*>()));
        }
        Serial.println("Valid PINs updated");
      } else {
        Serial.println("JSON parse error");
      }
    } else {
      Serial.printf("Error fetching PINs: %d\n", httpCode);
    }
    http.end();
  }
}

// POST access attempt log to server
void logAccess(String pin, String status) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(apiServer);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "pin_code=" + pin + "&status=" + status;
    int httpCode = http.POST(postData);
    String response = http.getString();

    Serial.printf("Log Access: %s %s, HTTP code: %d\nResponse: %s\n",
                  pin.c_str(), status.c_str(), httpCode, response.c_str());

    http.end();
  }
}

// Stepper motor unlock routine
void unlockCabinet() {
  lcd.clear();
  lcd.print("Unlocking...");
  Serial.println("Starting unlock sequence...");
  
  // Rotate forward (unlock)
  for (int i = 0; i < 512; i++) { // More steps for full rotation
    stepMotor(step_number);
    step_number++;
    if (step_number > 7) step_number = 0;
    delay(2); // Faster rotation
    
    // Debug output every 100 steps
    if (i % 100 == 0) {
      Serial.printf("Unlock step: %d, motor step: %d\n", i, step_number);
    }
  }
  
  Serial.println("Unlock complete, waiting 3 seconds...");
  delay(3000); // keep cabinet open for 3 sec
  
  lcd.clear();
  lcd.print("Locking...");
  Serial.println("Starting lock sequence...");
  
  // Rotate backward (lock)
  for (int i = 0; i < 512; i++) { // More steps for full rotation
    stepMotor(step_number);
    step_number--;
    if (step_number < 0) step_number = 7;
    delay(2); // Faster rotation
    
    // Debug output every 100 steps
    if (i % 100 == 0) {
      Serial.printf("Lock step: %d, motor step: %d\n", i, step_number);
    }
  }
  
  Serial.println("Lock complete");
  lcd.clear();
  lcd.print("Locked");
  delay(1000);
  lcd.clear();
  lcd.print("Enter PIN:");
  lcd.setCursor(0,1);
}

// Step motor one step (improved)
void stepMotor(int step) {
  switch (step) {
    case 0: 
      digitalWrite(IN1, HIGH); 
      digitalWrite(IN2, LOW);  
      digitalWrite(IN3, LOW);  
      digitalWrite(IN4, LOW);  
      break;
    case 1: 
      digitalWrite(IN1, HIGH); 
      digitalWrite(IN2, HIGH); 
      digitalWrite(IN3, LOW);  
      digitalWrite(IN4, LOW);  
      break;
    case 2: 
      digitalWrite(IN1, LOW);  
      digitalWrite(IN2, HIGH); 
      digitalWrite(IN3, LOW);  
      digitalWrite(IN4, LOW);  
      break;
    case 3: 
      digitalWrite(IN1, LOW);  
      digitalWrite(IN2, HIGH); 
      digitalWrite(IN3, HIGH); 
      digitalWrite(IN4, LOW);  
      break;
    case 4: 
      digitalWrite(IN1, LOW);  
      digitalWrite(IN2, LOW);  
      digitalWrite(IN3, HIGH); 
      digitalWrite(IN4, LOW);  
      break;
    case 5: 
      digitalWrite(IN1, LOW);  
      digitalWrite(IN2, LOW);  
      digitalWrite(IN3, HIGH); 
      digitalWrite(IN4, HIGH); 
      break;
    case 6: 
      digitalWrite(IN1, LOW);  
      digitalWrite(IN2, LOW);  
      digitalWrite(IN3, LOW);  
      digitalWrite(IN4, HIGH); 
      break;
    case 7: 
      digitalWrite(IN1, HIGH); 
      digitalWrite(IN2, LOW);  
      digitalWrite(IN3, LOW);  
      digitalWrite(IN4, HIGH); 
      break;
  }
} 