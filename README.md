
# Laravel Service Files Generator

It allows you to generate automatically service file for each database tables.

## Installation

Install warfee/service-files-generator with composer

```bash
  composer require warfee/service-files-generator
```

Put library path to application service provider file

```bash
  Warfee\ServiceFilesGenerator\ServiceFilesGeneratorServiceProvider::class,

```

    
## Usage

Running command. By default it will run from mysql connection.

Column : created_at, updated_at, deleted_at timestamp will be ignore. Timestamp will be use based on method action

Supported Driver : mysql,sqlite
Implement Soft Delete : true, false

```bash
  php artisan service-generator:create
```

