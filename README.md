<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# CardioSense API - Laravel Project

## Intruduction

This is a Laravel project that serves as the backend for the CardioSense project. It is a RESTful API that provides endpoints for the frontend and a prototype, a heart sensor, to interact with the database. The project is still in development and is not yet ready for production.

## Technologies

This project uses the tool called by Laravel Sail to manage the development environment in a containers instances (Docker).
Each container contains a different service runnning in enviroment. The containers running Laravel, MySQL, Redis, and Mailpit.

The following technologies are used in this project:
- Laravel 11
- PHP 8.3.13 (Composer 2.8.2)
- MySQL 8.0.32
- Redis 7.4.1
- Mailpit (Local Mail Server) 1.21.0

Another techlogies:
- Docker version 24.0.7, build 24.0.7-0ubuntu4.1
- Docker Compose v2.21.0 (Using by Laravel Sail)

The Redis database is used (for now) for purpose of cache and I/Os of the heart beat sensor.
The MySQL database is used to store the general data of the application.

## Installation

To install the project, you need to have Docker and Docker Compose installed on your machine. You can install Docker and Docker Compose by following the instructions on the official Docker website.

After installing Docker and Docker Compose, you can clone the project and run the following commands in the project directory:

```bash

git clone https://github.com/Dablio-0/api-cardiosense.git

```
Access the folder of the project:

```bash

cd api-cardiosense

```

Remember, you must copy the .env.example file to .env and configure the environment variables as needed.

Optionally, you can create an alias command to runnning sail commands, like this:

ON LINUX:

```bash

alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)' 

```

ON WINDOWS:

```bash

doskey sail=vendor\bin\sail.bat $*

```

Or use the command using the vendor directory:

```bash

./vendor/bin/sail up # Use -d to run in background or omit to run in foreground

```

If you don't have the images of the services used in the project, the sail command will download them automatically.

WARNING: Checks all the IP, Ports and Services that are running in your machine. The sail command will use the ports 80, 3306, 6379 and 8025.
Checks if the ports are free to use and if the another services are using the same ports.

After the containers are up, you can run the following command to install the dependencies of the project:

```bash

sail composer install

```

After installing the dependencies, you can run the following command to generate the application key:

```bash

sail artisan key:generate

```

After generating the application key, you can run the following command to run the migrations and seed the database:

```bash

sail artisan migrate --seed

```

ATENTION: Is not necessary to run the command to serve the application (sail artisan serve). The Laravel Sail will run the application in the port 80 
after the containers are up.

## Usage

To see the routes available in the application, you can run the following command:

```bash

sail artisan route:list

```

or access the file api.php in the routes directory.

You can use the Postman or Insomnia to test the routes of the application.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Team Members

