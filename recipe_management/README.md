# Recipe Management System

A PHP-based web application to manage and share cooking recipes.

## Features

- User registration and authentication
- Create, view, edit, and delete recipes
- Search functionality
- Image upload for recipes
- Responsive design

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone or download this repository to your web server directory.
2. Create a MySQL database named `recipe_management`.
3. Import the database structure from `database.sql`.
4. Update the database configuration in `config/database.php` if needed.
5. Make sure the `uploads` directory has write permissions.

```bash
# Example commands
git clone https://github.com/yourusername/recipe-management.git
cd recipe-management
mysql -u root -p < database.sql
chmod 777 uploads
```

## Usage

1. Open the website in your browser.
2. Register a new account.
3. Login with your credentials.
4. Start adding and exploring recipes!

## Directory Structure

```
recipe_management/
├── admin/              # Admin panel files (if applicable)
├── assets/             # CSS, JavaScript, and images
│   ├── css/
│   ├── js/
│   └── img/
├── auth/               # Authentication files
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/             # Configuration files
│   └── database.php
├── includes/           # Common files
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── recipes/            # Recipe management files
│   ├── index.php
│   ├── add.php
│   ├── view.php
│   ├── edit.php
│   └── delete.php
├── uploads/            # User-uploaded images
├── index.php           # Home page
├── database.sql        # Database structure
└── README.md           # This file
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details. 