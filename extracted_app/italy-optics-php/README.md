# Italy Optics (ইতালি অপটিক্স) - PHP & MySQL Luxury Monochrome Edition

A luxury **White & Black** monochrome e-commerce web application for **Italy Optics**, located at **12 Jamal Khan Road, Chattogram 4000**.

---

## 🌟 Key Updates

### 1. 🔍 High-Ranking SEO Meta Keywords & Local Schema
The HTML `<head>` has been loaded with targeted search keywords and Schema.org JSON-LD LocalBusiness data:
* **Target Keywords**: `italy optics, optics chittagong, optician in chittagong, jamal khan optical shop, eyewear chattogram, eyeglasses chittagong, prescription glasses bangladesh, polarized sunglasses chittagong, blue light blocking glasses, computer glasses, contact lenses chittagong, eye testing jamal khan, luxury italian eyewear, titanium frames bd, designer sunglasses chattogram, reading glasses bangladesh`
* **JSON-LD Schema**: Google LocalBusiness structured data registered for Optician at `12 Jamal Khan Road, Chattogram 4000`, phone `01708-541674`, and `5.0` aggregate star rating.

### 2. 🌐 Complete English Localization
All instances of Bengali text (such as `12 জামাল খান রোড, Chattogram 4000`) have been cleanly localized to **English**:
* **Address**: `12 Jamal Khan Road, Chattogram 4000`
* **Phone**: `01708-541674` (Call & WhatsApp)
* **Hours**: `Open · Closes 10 PM`
* **Store Name**: `Italy Optics`

### 3. 🔐 Secure Customer & Admin Password Authentication
* **Strict Authentication**: Customers and store administrators must log in with their correct password. If a user types the wrong password (`bhul password`), login is **strictly denied** with a security alert.
* **Customer Signup**: New customers can sign up with Name, Email, Phone, and Password. Passwords are encrypted using PHP `password_hash(..., PASSWORD_DEFAULT)`.
* **Admin Verification**: Store administrators must authenticate with the verified admin password to access the inventory manager.

---

## 👤 Default User Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Store Admin** | `admin@italyoptics.com` | `admin123` |
| **Customer** | `customer@italyoptics.com` | `customer123` |

*Note: Entering an incorrect password will be blocked with an error message.*

---

## 🚀 How to Run the Website

### Option 1: Using XAMPP / WampServer / Laragon
1. Copy or symlink this folder `italy-optics-php` to your web server root (e.g. `C:\xampp\htdocs\italy-optics-php`).
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open `http://localhost/italy-optics-php/setup.php` and click **Execute Auto-Migration & Setup**. The app creates the `italy_optics` database (when the MySQL account has permission), all required tables, and demo records.
4. Open `http://localhost/italy-optics-php/` in your browser.

**Important:** This fixed version uses MySQL only. It no longer silently switches to SQLite when MySQL is unavailable. If you see a database connection error, make sure MySQL is running and the values in `config/config.php` match your MySQL credentials. On default XAMPP, `root` with a blank password is typical.

### Option 2: Using Built-in PHP Server
```bash
cd C:\Users\hotspot\.gemini\antigravity\scratch\italy-optics-php
C:\xampp\php\php.exe -S localhost:8000
```
Open `http://localhost:8000` in your browser.

---

## 🧪 Running the Diagnostic Test Suite
To verify the entire website, database connection, password hashing, admin management, and SEO metadata at any time:
```bash
C:\xampp\php\php.exe tests/test_suite.php
```


## Admin login repair
The old archive contained an invalid hard-coded demo password hash. This version repairs that exact legacy hash without overwriting custom passwords. If the database already exists, visit `setup.php` and run the migration once to repair the demo account credentials.

Admin: `admin@italyoptics.com` / `admin123`
