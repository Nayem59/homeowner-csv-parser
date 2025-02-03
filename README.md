# CSV Homeowner-Names CSV Parser Application

A Laravel-based application for parsing homeowner information from CSV files.

## Features

-   CSV file form upload via web interface
-   JSON endpoint for processing
-   Artisan command for CLI processing
-   Error reporting
-   Support for different name formats
-   Unit and feature test coverage
-   Custom parsing service
-   PHPStan

## Requirements

-   PHP 8.1+
-   Composer
-   Git

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/Nayem59/homeowner-csv-parser.git
cd homeowner-csv-parser
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

set App_URL in .env file to localhost 8000:

```
APP_URL=http://127.0.0.1:8000
```

## Usage

### Web Interface

1. Start development server:

```bash
php artisan serve
```

2. Visit http://127.0.0.1:8000 in your browser
3. Upload CSV files using the web form

### Command Line Interface

```bash
php artisan homeowners:import /path/to/file.csv --has-header
```

## Testing

Run all tests:

```bash
php artisan test
```

Run specific test groups:

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

##

Please Hire Me 😊
