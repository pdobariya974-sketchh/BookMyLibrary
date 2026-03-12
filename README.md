# 📚 BookMyLibrary

A **Library Management System** built using **PHP, MySQL, HTML, CSS, and JavaScript** to efficiently manage library operations such as managing books, issuing books, and tracking records.

This project provides a **simple and user-friendly dashboard** for administrators to manage library data.

---

## 🚀 Features

* 📊 Admin Dashboard with statistics
* 📖 Add, update, and delete books
* 🔄 Issue and return book management
* 👨‍🎓 Student / Member management
* 🔍 Search and track book availability
* 📈 Visual statistics using Chart.js (bar chart & doughnut chart)
* 📱 Responsive and modern UI design (Bootstrap 5 + custom CSS)

---

## 🛠️ Technologies Used

* **Frontend:** HTML, CSS (Bootstrap 5), JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Charts:** Chart.js
* **Icons:** Font Awesome 6

---

## 📂 Project Structure

```
BookMyLibrary/
│
├── admin/
│   ├── dashboard.php      # Admin dashboard with stats & charts
│   ├── sidebar.php        # Shared sidebar navigation
│   ├── books.php          # Book list with search
│   ├── add_book.php       # Add a new book
│   ├── edit_book.php      # Edit an existing book
│   ├── delete_book.php    # Delete a book
│   ├── members.php        # Member list with search
│   ├── add_member.php     # Add a new member
│   ├── edit_member.php    # Edit a member
│   ├── delete_member.php  # Delete a member
│   ├── issue_book.php     # Issue a book to a member
│   ├── return_book.php    # Return a book & fine calculation
│   └── logout.php         # Logout
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── main.js            # JavaScript (charts, sidebar, validation)
├── database/
│   └── library.sql        # Database schema & sample data
├── index.php              # Login page
└── config.php             # Database configuration
```

---

## ⚙️ Installation & Setup

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (or any PHP + MySQL stack)
* PHP 8.0+
* MySQL 5.7+

### Steps

**1️⃣ Clone the repository**

```bash
git clone https://github.com/pdobariya974-sketchh/BookMyLibrary.git
```

**2️⃣ Move the project to the XAMPP htdocs folder**

```
C:\xampp\htdocs\BookMyLibrary\
```

**3️⃣ Import the database**

* Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
* Click **Import** tab
* Select `database/library.sql` from the project folder
* Click **Go** — the `library_db` database will be created automatically

**4️⃣ Configure the database connection** *(if needed)*

Edit `config.php` and update the credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // your MySQL password
define('DB_NAME', 'library_db');
```

**5️⃣ Run the project in your browser**

```
http://localhost/BookMyLibrary/
```

**6️⃣ Login with the default admin credentials**

| Field    | Value      |
|----------|-----------|
| Username | `admin`   |
| Password | `admin123`|

> ⚠️ **Change the default password after first login** in a production environment.

---

## 📊 Dashboard Overview

The admin dashboard displays:
- **Total Books** — total number of book titles in the library
- **Total Members** — registered library members
- **Books Issued** — currently issued (pending return)
- **Books Available** — sum of available copies across all titles
- **Bar Chart** — books distributed by category
- **Doughnut Chart** — issue status breakdown (Issued / Returned / Overdue)
- **Recent Issued Books** — latest 10 issue records

---

## 📚 Book Management

- Add new books with title, author, ISBN, category, quantity, and description
- Edit existing book details (quantity cannot be reduced below currently issued copies)
- Delete books (blocked if there are active loans)
- Server-side search by title, author, or ISBN

---

## 👥 Member Management

- Add, edit, and delete library members
- Track member status (Active / Inactive)
- Delete blocked if member has active loans

---

## 🔄 Issue & Return

- Issue a book to an active member with a due date (default: 14 days)
- View all currently issued books with overdue status
- Return a book and automatically calculate the fine ($1/day overdue)

---

## 🎯 Purpose of Project

This project was developed to **learn web development concepts**, including:

* Backend development with PHP
* Database management with MySQL
* Building dashboards and CRUD operations
* Responsive UI with Bootstrap 5
* Data visualization with Chart.js

---

## 👨‍💻 Author

**Parth Dobariya**  
Computer Engineering Student  
RK University, Rajkot