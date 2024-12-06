<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

<p align="center">
<a href="https://github.com/tundeseun/accessibilityanalyzer/actions"><img src="https://github.com/tundeseun/accessibilityanalyzer/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/tundeseun/accessibilityanalyzer"><img src="https://img.shields.io/packagist/dt/tundeseun/accessibilityanalyzer" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tundeseun/accessibilityanalyzer"><img src="https://img.shields.io/packagist/v/tundeseun/accessibilityanalyzer" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/tundeseun/accessibilityanalyzer"><img src="https://img.shields.io/packagist/l/tundeseun/accessibilityanalyzer" alt="License"></a>
</p>

## About Accessibility Analyzer

Accessibility Analyzer is a Laravel/Vue application designed to streamline accessibility testing and reporting for web applications. This guide provides detailed deployment instructions for both **local** and **online** environments.

---

## Localhost Deployment

### 1. Install Prerequisites

Ensure the following tools and dependencies are installed on your local machine:
- **PHP** (>=8.0)
- **Composer**
- **Node.js** & **npm**
- **MySQL**

Install Laravel globally (if not already installed):
```bash
composer global require laravel/installer

2. Set Up the Project

    Clone the repository:
git clone https://github.com/tundeseun/accessibilityanalyzer.git
cd accessibilityanalyzer

Install Laravel dependencies:
composer install
Install Vue dependencies:
npm install
3. Configure Environment

    Copy the example .env file:
cp .env.example .env
cp .env.example .env
4. Build Frontend Assets

    Compile Vue files:
npm run dev
5. Run the Application

    Start the Laravel development server:
php artisan serve
Open the application in your browser:
http://127.0.0.1:8000


Online Deployment
1. Prepare the Hosting Environment

Ensure the server supports:

    PHP (>=8.0)
    MySQL
    Node.js (optional, for building Vue files)

Set up a domain or subdomain for the application.
2. Upload the Project

    Clone the repository or upload the project files to the server:
git clone https://github.com/tundeseun/accessibilityanalyzer.git
Navigate to the project directory:
cd /path/to/project
3. Install Dependencies

    Install Laravel dependencies:
composer install --optimize-autoloader --no-dev

Install Vue dependencies and build production assets:
npm install
npm run build
4. Configure Environment

    Copy the .env file:
cp .env.example .env
6. Set Permissions

    Ensure writable permissions for the following directories:
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
7. Configure the Web Server

    Configure Apache or Nginx to point to the public directory.

Example Nginx configuration:

server {
    listen 80;
    server_name your-domain.com;
    root /path/to/project/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
8. Optimize Application

Run optimization commands:
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

9. Access the Application

Open the domain in a web browser.

