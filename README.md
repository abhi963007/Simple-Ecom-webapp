# 🛍️ Simple E-commerce Web Application

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=Apache&logoColor=white)

A modern, secure, and user-friendly e-commerce web application built with PHP, MySQL, and Tailwind CSS.

[Features](#features) • [Prerequisites](#prerequisites) • [Installation](#installation) • [Usage](#usage) • [Security](#security) • [License](#license)

</div>

## ✨ Features

### 🔐 User Management
- Secure Authentication & Authorization
- User Registration and Login
- Password Recovery
- Profile Management

### 👨‍💼 Admin Dashboard
- Product Management (CRUD)
- Category Management
- Order Tracking
- User Management
- Sales Analytics

### 🛒 Shopping Experience
- Product Browsing
- Category Navigation
- Shopping Cart
- Secure Checkout
- Order History

### 🎨 UI/UX
- Responsive Design
- Modern Interface
- Clean URL Structure
- Interactive Elements
- Mobile-Friendly

### 🛡️ Security
- Rate Limiting
- SQL Injection Prevention
- XSS Protection
- CSRF Protection
- Session Management

## 🔧 Prerequisites

Before you begin, ensure you have the following installed:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Composer (for dependency management)
- Git

## 📥 Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/abhi963007/Simple-Ecom-webapp.git
   cd Simple-Ecom-webapp
   ```

2. **Configure Apache**
   - Enable mod_rewrite:
     ```bash
     sudo a2enmod rewrite
     sudo service apache2 restart
     ```
   - Update your Apache configuration to allow .htaccess overrides

3. **Set Up the Database**
   ```bash
   # Log into MySQL
   mysql -u root -p

   # Create the database
   CREATE DATABASE ecommerce;
   
   # Import the schema
   mysql -u root -p ecommerce < database.sql
   ```

4. **Configure the Application**
   - Copy the sample config file:
     ```bash
     cp includes/config.sample.php includes/config.php
     ```
   - Update the database credentials in `includes/config.php`

5. **Set Up File Permissions**
   ```bash
   # Create uploads directory
   mkdir -p uploads/products
   chmod 755 uploads/products
   ```

6. **Install Dependencies**
   ```bash
   composer install
   ```

## 🚀 Usage

1. **Start the Application**
   - Access the application through your web browser:
     ```
     http://localhost/Simple-Ecom-webapp
     ```

2. **Admin Access**
   - Default admin credentials:
     ```
     Email: admin@admin.com
     Password: admin123
     ```
   - Change these credentials after first login!

3. **User Registration**
   - Click "Register" in the navigation
   - Fill in your details
   - Verify your email (if enabled)

4. **Managing Products (Admin)**
   - Log in as admin
   - Navigate to Dashboard
   - Use the Products section to add/edit items

5. **Shopping (Users)**
   - Browse products
   - Add items to cart
   - Proceed to checkout
   - View order history

## 🔒 Security Features

- **Password Security**
  - Bcrypt hashing
  - Password strength requirements
  - Failed login attempt limiting

- **Data Protection**
  - SQL injection prevention
  - XSS protection
  - CSRF tokens
  - Secure session management

- **Access Control**
  - Role-based authorization
  - Protected admin routes
  - Rate limiting
  - Session timeout

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

<div align="center">

Made with ❤️ by [Abhiram](https://github.com/abhi963007)

</div> 