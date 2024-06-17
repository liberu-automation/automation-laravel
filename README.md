# Liberu Automation

![](https://img.shields.io/badge/PHP-8.3-informational?style=flat&logo=php&color=4f5b93)
![](https://img.shields.io/badge/Laravel-11-informational?style=flat&logo=laravel&color=ef3b2d)
![](https://img.shields.io/badge/JavaScript-ECMA2020-informational?style=flat&logo=JavaScript&color=F7DF1E)
![](https://img.shields.io/badge/Livewire-3.5-informational?style=flat&logo=Livewire&color=fb70a9)
![](https://img.shields.io/badge/Filament-3.2-informational?style=flat&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48cGF0aCBkPSJNMCAwaDQ4djQ4SDBWMHoiIGZpbGw9IiNmNGIyNWUiLz48cGF0aCBkPSJNMjggN2wtMSA2LTMuNDM3LjgxM0wyMCAxNWwtMSAzaDZ2NWgtN2wtMyAxOEg4Yy41MTUtNS44NTMgMS40NTQtMTEuMzMgMy0xN0g4di01bDUtMSAuMjUtMy4yNUMxNCAxMSAxNCAxMSAxNS40MzggOC41NjMgMTkuNDI5IDYuMTI4IDIzLjQ0MiA2LjY4NyAyOCA3eiIgZmlsbD0iIzI4MjQxZSIvPjxwYXRoIGQ9Ik0zMCAxOGg0YzIuMjMzIDUuMzM0IDIuMjMzIDUuMzM0IDEuMTI1IDguNUwzNCAyOWMtLjE2OCAzLjIwOS0uMTY4IDMuMjA5IDAgNmwtMiAxIDEgM2gtNXYyaC0yYy44NzUtNy42MjUuODc1LTcuNjI1IDItMTFoMnYtMmgtMnYtMmwyLTF2LTQtM3oiIGZpbGw9IiMyYTIwMTIiLz48cGF0aCBkPSJNMzUuNTYzIDYuODEzQzM4IDcgMzggNyAzOSA4Yy4xODggMi40MzguMTg4IDIuNDM4IDAgNWwtMiAyYy0yLjYyNS0uMzc1LTIuNjI1LS4zNzUtNS0xLS42MjUtMi4zNzUtLjYyNS0yLjM3NS0xLTUgMi0yIDItMiA0LjU2My0yLjE4N3oiIGZpbGw9IiM0MDM5MzEiLz48cGF0aCBkPSJNMzAgMThoNGMyLjA1NSA1LjMxOSAyLjA1NSA1LjMxOSAxLjgxMyA4LjMxM0wzNSAyOGwtMyAxdi0ybC00IDF2LTJsMi0xdi00LTN6IiBmaWxsPSIjMzEyODFlIi8+PHBhdGggZD0iTTI5IDI3aDN2MmgydjJoLTJ2MmwtNC0xdi0yaDJsLTEtM3oiIGZpbGw9IiMxNTEzMTAiLz48cGF0aCBkPSJNMzAgMThoNHYzaC0ydjJsLTMgMSAxLTZ6IiBmaWxsPSIjNjA0YjMyIi8+PC9zdmc+&&color=fdae4b&link=https://filamentphp.com)

[![Install](https://github.com/liberu-automation/automation-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-automation/automation-laravel/actions/workflows/install.yml)
[![Tests](https://github.com/liberu-automation/automation-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-automation/automation-laravel/actions/workflows/tests.yml)
[![Docker](https://github.com/liberu-automation/automation-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-automation/automation-laravel/actions/workflows/main.yml)


## Our Projects

* https://github.com/liberu-accounting/accounting-laravel
* https://github.com/liberu-automation/automation-laravel
* https://github.com/liberu-billing/billing-laravel
* https://github.com/liberusoftware/boilerplate
* https://github.com/liberu-browser-game/browser-game-laravel
* https://github.com/liberu-cms/cms-laravel
* https://github.com/liberu-control-panel/control-panel-laravel
* https://github.com/liberu-crm/crm-laravel
* https://github.com/liberu-ecommerce/ecommerce-laravel
* https://github.com/liberu-genealogy/genealogy-laravel
* https://github.com/liberu-maintenance/maintenance-laravel
* https://github.com/liberu-real-estate/real-estate-laravel
* https://github.com/liberu-social-network/social-network-laravel

## Setup

1. Ensure your environment is set up with PHP 8.3 and Composer installed.
2. Download the project files from this GitHub repository.
3. Open a terminal in the project folder. If you are on Windows and have Git Bash installed, you can use it for the following steps.
4. Run the following command:

```bash
./setup.sh
```

and everything should be installed automatically if you are using Linux you just run the script as you normally run scripts in the terminal.

NOTE 1: The script will ask you if you want to have your .env be overwritten by .env.example, in case you have already an .env configuration available please answer with "n" (No).

NOTE 2: This script will run seeders, please make sure you are aware of this and don't run this script if you don't want this to happen.
```bash
composer install
php artisan key:generate
php artisan migrate --seed
```
This will install the necessary dependencies, generate an application key, and set up your database with initial data.

NOTE 3: Ensure your `.env` file is correctly configured with your database connection details before running migrations.

## Building with Docker

Alternatively, you can build and run the project using Docker. To build the Dockerfile, follow these steps:

1. Ensure you have Docker installed on your system.
2. Open a terminal in the project folder.
3. Run the following command to build the Docker image:
   ```
   docker build -t automation-laravel .
   ```
4. Once the image is built, you can run the container with:
   ```
   docker run -p 8000:8000 automation-laravel
   ```

NOTE 3: Ensure your `.env` file is correctly configured with your database connection details before running migrations.

### Using Laravel Sail

This project also includes support for Laravel Sail, which provides a Docker-based development environment. To use Laravel Sail, follow these steps:

1. Ensure you have Docker installed on your system.
2. Open a terminal in the project folder.
3. Run the following command to start the Laravel Sail environment:
   ```
   ./vendor/bin/sail up
   ```
4. Once the containers are running, you can access the application at `http://localhost`.
5. To stop the Sail environment, press `Ctrl+C` in the terminal.

For more information on using Laravel Sail, refer to the [official documentation](https://laravel.com/docs/sail).

### Description
Welcome to Liberu Automation, our revolutionary open-source project that redefines the world of web hosting control and billing. With the powerful combination of Laravel 11, PHP 8.3, Livewire 3, and Filament 3, Liberu Automation is not just a control panel – it's a dynamic solution designed to streamline web hosting management and billing processes with efficiency and innovation.

**Key Features:**

1. **Intuitive Control Panel:** Liberu Automation boasts an intuitive and user-friendly control panel, providing seamless management of web hosting resources. From domain administration to server configuration, our project simplifies the complexities of web hosting, ensuring a smooth and accessible experience for users.

2. **Billing Automation:** Automate your billing processes with Liberu Automation. From subscription management to invoice generation, our project facilitates effortless financial transactions, saving time and resources for both administrators and users.

3. **Real-time Monitoring:** Keep a vigilant eye on your web hosting infrastructure with real-time monitoring features. Leveraging Laravel 11 and PHP 8.3, Liberu Automation ensures that administrators can track server performance, resource usage, and other critical metrics to maintain optimal functionality.

4. **Client Management:** Enhance client relationships through Liberu Automation's comprehensive client management system. From user permissions to support ticket systems, our project empowers administrators to provide top-notch service to their clients.

5. **Efficient Administration:** Filament 3, our admin panel built on Laravel, adds an extra layer of efficiency to Liberu Automation. Administrators can effortlessly manage user permissions, customize settings, and oversee the entire hosting infrastructure with a powerful and intuitive interface.

Liberu Automation is open source, released under the permissive MIT license. We invite web hosting providers, developers, and tech enthusiasts to join us in shaping the future of hosting management tools. Together, let's harness the power of technology to simplify web hosting, automate billing processes, and create a seamless experience for administrators and users alike.

Welcome to Liberu Automation – where innovation meets control and efficiency in the dynamic world of web hosting. Join us on this journey to redefine the standards of web hosting control panels and billing systems.

### Licensed under MIT, use for any personal or commercial project.

### Contributions

We warmly welcome new contributions from the community! We believe in the power of collaboration and appreciate any involvement you'd like to have in improving our project. Whether you prefer submitting pull requests with code enhancements or raising issues to help us identify areas of improvement, we value your participation.

If you have code changes or feature enhancements to propose, pull requests are a fantastic way to share your ideas with us. We encourage you to fork the project, make the necessary modifications, and submit a pull request for our review. Our team will diligently review your changes and work together with you to ensure the highest quality outcome.

However, we understand that not everyone is comfortable with submitting code directly. If you come across any issues or have suggestions for improvement, we greatly appreciate your input. By raising an issue, you provide valuable insights that help us identify and address potential problems or opportunities for growth.

Whether through pull requests or issues, your contributions play a vital role in making our project even better. We believe in fostering an inclusive and collaborative environment where everyone's ideas are valued and respected.

We look forward to your involvement, and together, we can create a vibrant and thriving project. Thank you for considering contributing to our community!
<!--/h-->

### License

This project is licensed under the MIT license, granting you the freedom to utilize it for both personal and commercial projects. The MIT license ensures that you have the flexibility to adapt, modify, and distribute the project as per your needs. Feel free to incorporate it into your own ventures, whether they are personal endeavors or part of a larger commercial undertaking. The permissive nature of the MIT license empowers you to leverage this project without any unnecessary restrictions. Enjoy the benefits of this open and accessible license as you embark on your creative and entrepreneurial pursuits.
<!--/h-->

## Contributors


<a href = "https://github.com/liberu-automation/automation-laravel/graphs/contributors">
  <img src = "https://contrib.rocks/image?repo=liberu-automation/automation-laravel"/>
