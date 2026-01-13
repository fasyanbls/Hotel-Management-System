# 🏨 Hotel Management System (MyHotel)

A **web-based Hotel Management System** designed to automate hotel operations such as room reservations, customer management, payments, and reporting. This system provides separate interfaces for **Admin** and **Customer**, ensuring secure, efficient, and user-friendly hotel management.

This project was developed as a **Final Project for the Web Application Development course**.

---

## 📖 Project Overview

In today’s digital era, hotels are required to manage reservations, room availability, customer data, and payments efficiently. Many small to mid-sized hotels still rely on manual processes, which often cause data inconsistency, human errors, and poor customer experience.

**MyHotel** solves these problems by offering a centralized, web-based system that:
- Automates hotel operations
- Provides real-time data
- Improves service efficiency
- Supports data-driven decision making

---

## 🎯 Objectives

- Automate hotel booking and payment processes  
- Improve operational efficiency and accuracy  
- Enhance customer experience with online access  
- Reduce human error in reservations and billing  
- Provide clear reporting for hotel management  

---

## 🛠️ Tech Stack

**Front-End**
- HTML5  
- CSS3  
- JavaScript  
- Bootstrap  

**Back-End**
- PHP (Native PHP)

**Database**
- MySQL  

**Tools & Environment**
- Apache Server (via XAMPP)
- phpMyAdmin
- NetBeans IDE
- Chart.js (for data visualization)

---

## 🏗️ System Architecture

This system uses a **Client–Server Architecture**:
- **Client Side**: Web browser (HTML, CSS, JavaScript)
- **Server Side**: PHP handles logic, authentication, and CRUD operations
- **Database**: MySQL stores users, rooms, reservations, payments, and reports

---

## 👥 User Roles

### 🔐 Admin
- Manage rooms (Add, Edit, Delete)
- Manage customers
- Manage reservations
- Manage payments
- Generate reports (PDF & Excel)
- View dashboard analytics
- Print receipts

### 👤 Customer
- Register & login
- View available rooms
- Book rooms
- View reservations
- View payment history
- Download receipts
- Edit profile & change password

---

## ✨ Key Features

### 🔑 Authentication
- Role-based login (Admin & Customer)
- Secure session handling
- Password hashing (SHA-256)

### 🏨 Room Management
- Real-time room availability
- Room images & facilities
- Price-based room sorting

### 📅 Reservation System
- Date validation & double booking prevention
- Reservation history tracking
- Admin & customer booking access

### 💳 Payment Management
- Payment status tracking (Paid / Pending)
- Multiple payment methods
- Invoice & receipt generation

### 📊 Dashboard & Reports
- Admin dashboard with statistics
- Charts for payment & room status (Chart.js)
- Monthly income visualization
- Export reports to PDF & Excel

---

## 🗂️ Database Design

Main entities:
- Users
- Customers
- Rooms
- Reservations
- Payments

The database is designed using an **ERD-based relational structure** to ensure data integrity and scalability.

---

## ⚙️ Installation & Setup

### 1️⃣ Requirements
- XAMPP (Apache & MySQL)
- Web browser
- NetBeans IDE 

---

### 2️⃣ Clone Repository
```bash
git clone https://github.com/yourusername/hotel-management-system.git
````

---

### 3️⃣ Setup Database

1. Open **phpMyAdmin**
2. Create a database:

```sql
CREATE DATABASE hotel_management;
```

3. Import the provided `.sql` file into the database

---

### 4️⃣ Configure Database Connection

Edit `config/database.php`:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "hotel_management";
```

---

### 5️⃣ Run the Application

1. Move the project folder to:

```
xampp/htdocs/
```

2. Start **Apache** and **MySQL** from XAMPP
3. Open browser and go to:

```
http://localhost/hotel-management-system/
```

---

## 🔐 Default Admin Account

```txt
Email    : admin@hotel.com
Password : admin
```

---

## 📄 Project Documentation

📌 **Final Project Report (PDF):**
👉 https://drive.google.com/file/d/1i2ao-B2opMSWTIdFSFHdnFGX_W9EhU0W/view?usp=sharing

---

## 👨‍💻 Contributors

* **Fasya Nabila Salim** – Information System
* **Eileen Daneaya** – Information System
* **Kaila Annisa Syafitri** – Information System

---
