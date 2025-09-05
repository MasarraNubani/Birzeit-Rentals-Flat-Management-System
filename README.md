# Birzeit-Rentals-Flat-Management-System
A **full-stack PHP/MySQL web application** for managing apartment rentals.  
Designed with role-based access for **Managers, Owners, and Customers**, it provides a complete workflow: adding flats, approval, viewings, rentals, and notifications.

---

## 🚀 Features

### 🔑 Role-Based Access
- **Manager**: Approve or remove flats, manage users, advanced inquiry search.
- **Owner**: Add flats, upload images, set viewing times, preview and confirm appointments.
- **Customer**: Browse flats, book viewing appointments, request rentals.

### 🏢 Core Modules
- **Flat Management**: Add, view, edit, delete flats with photos and metadata.
- **Viewing Times**: Owners define available slots, customers book instantly.
- **Rentals**: Rental contracts with overlap prevention logic.
- **Notifications**: Red-badge notification system for important events (pending flats, new requests, confirmations).
- **Inquiry Tool**: Advanced search for managers by date, location, owner, or customer.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8+, MySQL (PDO with prepared statements)
- **Frontend**: HTML5, CSS3 (vanilla + custom styling)
- **Auth & Security**:
  - Session-based authentication
  - Role-based authorization
  - CSRF protection
  - Secure file uploads with MIME & size validation

---

## 📂 Project Structure

/birzeit-rentals
│── /auth # Login, Register (Customer & Owner)
│── /includes # Header, Footer, CSRF, Notifications
│── /manager # Manager dashboards & tools
│── /owner # Owner dashboards (Add Flat, Preview Appointments)
│── /pages # Public/Customer pages (Home, Flats, Search, Detail)
│── /uploads # Flat images (ignored in .gitignore)
│── dbconfig.inc.php (example only)
│── style.css
│── README.md

yaml
Copy code

---

## ⚙️ Setup Instructions

1. **Clone the repo**
   ```bash
   git clone https://github.com/your-username/birzeit-rentals.git
   cd birzeit-rentals
Database Setup

Create a new MySQL database.

Import the provided schema.sql.

Update your DB credentials in dbconfig.inc.php.

Run Locally

Use XAMPP or MAMP.

Place the project in the htdocs folder.

Visit: http://localhost/birzeit-rentals/pages/home.php.

📸 Screenshots
<img width="1875" height="782" alt="image" src="https://github.com/user-attachments/assets/04306642-293d-40b3-bd5f-fb78894eb703" />
<img width="1899" height="469" alt="image" src="https://github.com/user-attachments/assets/7011a6de-d1d2-48d5-bdf0-a4b1490a0215" />
<img width="1919" height="882" alt="image" src="https://github.com/user-attachments/assets/4f595fbb-391a-4097-be1f-d71f1587ae5c" />
<img width="1890" height="848" alt="image" src="https://github.com/user-attachments/assets/d9cde595-c67a-4e7a-8a6f-e8f5a25fee92" />
<img width="1903" height="847" alt="image" src="https://github.com/user-attachments/assets/d21c012a-1a6a-4f73-8b98-cf645084c851" /><img width="1912" height="868" alt="image" src="https://github.com/user-attachments/assets/512af170-f390-4738-8c4b-b4a468702721" />
<img width="1897" height="885" alt="image" src="https://github.com/user-attachments/assets/4a9e2b15-c709-4924-b873-82a92fcd2549" />
<img width="1899" height="883" alt="image" src="https://github.com/user-attachments/assets/1fb77256-ef32-4cbc-9376-09b4b0741ccc" />
<img width="1899" height="872" alt="image" src="https://github.com/user-attachments/assets/7c12f499-7e78-4b25-a0dc-e8c6f4697489" />
![Uploading image.png…]()

🗺️ Roadmap

 Improve UI with TailwindCSS or Bootstrap

 REST API for mobile integration

 Two-Factor Authentication (2FA)

 Multi-language support (Arabic/English)

 Dockerize for easier deployment

👤 Author

Developed by Masarra S. H. Al-Nubani
📍 Palestine
🔗 LinkedIn www.linkedin.com/in/masarra-nubani-485b20248
