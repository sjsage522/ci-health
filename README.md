# Mailplug Health Check Client

Mailplug Health Check Client is a PHP library that provides a simple way to check the health status of various services in your infrastructure. It allows you to monitor the health of multiple components, ensuring that your application is running smoothly.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Basic Example](#basic-example)
  - [Health Check Result Example](#health-check-result-example)
  - [Configuration](#configuration)

## Features
- Check the health status of multiple components.
- Easily extendable to support custom health checks.
- Configurable health checks based on service requirements.

## Installation

You can install this package via Composer:

```bash
composer require mailplug/ci-health
```

## Usage
### Basic Example
After installation, you can use the HealthCheckClient to perform health checks on all your configured services.
```php
<?php

require 'vendor/autoload.php';

use Mailplug\Health\Client\HealthCheckClient;
use Mailplug\Health\Config\Config;

// Initialize the configuration
$config = new Config();
$config->alives = [
  HealthCheckTargetDb::REDIS(),
];

// Instantiate the HealthCheckClient
$client = new HealthCheckClient($config);

// Perform health check for all services
$result = $client->healthCheckAll();

// json format
echo json_encode($result, JSON_PRETTY_PRINT);
```
### Health Check Result Example
The output of the health check will be in JSON format, providing detailed information about the status of various components. Below is an example of the output:
```json
{
    "status": "UP",
    "components": {
        "db": {
            "mysqldb": {
                "status": "success",
                "message": "Connection to database server was successful.",
                "data": null
            },
            "postgresdb": {
                "status": "success",
                "message": "Connection to database server was successful.",
                "data": null
            },
            "redis": {
                "status": "success",
                "message": "",
                "data": {
                    "responseTime": 0.0012140274047851562,
                    "connections": 19,
                    "uptime": 5783839
                }
            }
        },
        "rabbitmq": {
            "status": "success",
            "message": "",
            "data": null
        },
        "disk_usage": {
            "status": "success",
            "message": "Disk usage is 21 percent.",
            "data": 21.938328660427558
        },
        "php": {
            "php_version": {
                "status": "success",
                "message": "Current PHP version is 7.4.33",
                "data": "7.4.33"
            },
            "phpfpm_count": {
                "status": "success",
                "message": "php-fpm processes are running. [4]",
                "data": null
            },
            "opcache_memory": {
                "status": "success",
                "message": "61% of available 128 MB memory used.",
                "data": 60.608083009719849
            }
        }
    }
}
```
### Configuration
The Config object allows you to define which services should be considered in the health check process. You can pass an array of services (alives) that the library will monitor:
```php
$config = new Config();
$config->alives = [
  HealthCheckTargetDb::REDIS(),
];

$config->redisHost = 'localhost';
```
