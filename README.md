# 🏛️ Vault: Personal Knowledge Management System

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-v8.0-4479A1.svg?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Vault** is a lightweight, self-hosted web application built with PHP and MySQL to help you organize your digital life. Store notes, save important links, and manage your files in one secure, centralized location.

---

## ✨ Key Features

- **📝 Note Management:** Create, edit, and organize notes with a clean interface. Mark important notes as "Favourites" for quick access.
- **🔗 Link Archiving:** Save URLs with titles, descriptions, and custom categories to build your own curated web directory.
- **📁 File Vault:** Upload and manage your documents and assets (Supports XAMPP-friendly storage).
- **🔍 Quick Search:** Instantly find what you're looking for across all your notes and links.
- **🌓 Adaptive UI:** Features a sleek, modern dashboard with a focus on usability and mobile responsiveness.
- **🔒 Secure Auth:** User authentication system with password hashing and session-based security.

---

## 🎥 Demo

![Project Demo](demo.mp4)

> [!TIP]
> To show a video here, simply save your screen recording as `demo.mp4` in this folder. GitHub will render it as a video player!

---

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL / MariaDB
- **Frontend:** Vanilla CSS (Modern UI), HTML5
- **Server:** Apache (optimized for XAMPP/WAMP)

---

## 🚀 Getting Started

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) or any local PHP/MySQL server.

### 2. Installation
1. Clone this repository to your `htdocs` folder:
   ```bash
   cd C:/xampp/htdocs
   git clone https://github.com/your-username/vault.git
   ```
2. Create a database named `vault_db` in **phpMyAdmin**.
3. Import the `database.sql` file provided in the root folder.

### 3. Configuration
1. Rename `.env.example` to `.env`.
2. Update the database credentials in `.env` to match your local setup:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=vault_db
   ```

### 4. Running the App
Start Apache and MySQL from your XAMPP Control Panel, then navigate to:
`http://localhost/vault`

---

## 📂 Project Structure

- `auth/`: Login and Registration logic.
- `config/`: Database connection and security setup.
- `includes/`: Reusable components (Header, Footer, Navigation).
- `notes/`: Core functionality for managing personal notes.
- `uploads/`: Directory for stored user files.
- `assets/`: Styling (CSS) and client-side resources.

---

## 📝 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
**Developed with ❤️ by Nidharshanaa**
