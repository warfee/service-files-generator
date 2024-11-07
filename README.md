
# Laravel Service Files Generator

If you not work with model, it allows you to generate automatically service file for each database tables.
## Installation

Install warfee/service-files-generator with composer

```bash
  composer require warfee/service-files-generator
```

Put library path to application service provider file

```bash
  Warfee\ServiceFilesGenerator\ServiceFilesGeneratorServiceProvider::class,

```

Publish Helpers File

```bash
  php artisan vendor:publish --tag=service-generator-helpers

```
    


    
## Usage

Running command. By default it will run from mysql connection.

Column : created_at, updated_at, deleted_at timestamp will be ignore. Timestamp will be use based on method action

```bash
  php artisan service-generator:create
```

Command Options

- Driver (driver : mysql or sqlite)
- Soft Delete, each record will consider deleted_at field. (softDelete : true or false)
 

```bash
  php artisan service-generator:create {driver=mysql} {softDelete=false}
```