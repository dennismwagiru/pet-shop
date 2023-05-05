Pet-Shop Backend Developer Task
==============
![CI Workflow](https://github.com/dennismwagiru/pet-shop/actions/workflows/checks.yml/badge.svg "Workflow Badge")


Writing Pet-Shop Api using Laravel, in PHP8.2.

### Background


### Tech-stack
#### (Development Environment)
* [WSL 2](https://docs.microsoft.com/en-us/windows/wsl/install) - a compatibility layer for running Linux binary executables natively on Windows 10, Windows 11, and Windows Server 2019.
* [Ubuntu](https://ubuntu.com/wsl) - allows access to the Linux terminal on Windows, develop cross-platform applications, and manage IT infrastructure without leaving Windows.
* [PHP 8.2](https://www.php.net/releases/8.2/en.php) - an interpreted high-level general-purpose programming language
* [Docker](https://www.docker.com/) - a set of platform as a service products that use OS-level virtualization to deliver software in packages called containers.
    * [Laravel](https://laravel.com/) - a web framework written in PHP
    * [MySQL](https://www.mysql.com/) - a cross platform relational database program
    * [Nginx](https://www.nginx.com/) - a web server used to serve the application

### Running Locally
* The project has been containerized with the following services included:-

  | Service    | Port |
      |------------|------|
  | APP (HTTP) | 8000 |
  | MySQL      | 3306 |
  | Nginx      |      |

* Follow these steps for the initial setup
    1. Clone the repository
        ````bash
            git clone git@github.com:dennismwagiru/pet-shop.git && cd pet-shop
        ````
    2. Build and start server
        ```bash
           make install
        ```
       Go to <a href="http://127.0.0.1:8000" target="_blank">http://127.0.0.1:8000</a> to launch Swagger Api Documentation

### Other scripts included
* Subsequent Runs
    ````bash
        make up
    ````
* Build docker image
    ````bash
        make build
    ````
* Stop
    ````bash
        make stop
    ````
* Run migrations
    ````bash
       make migrate
    ````
* Seed your database
    ````bash
       make seed
    ````
* Fresh migrations
    ````bash
        make fresh
    ````
* Make model field filter
    ````bash
        make make-filter
    ````
    * Enter the name of the Field.
    * i.e. to add a filter for user last name enter **User/LastName**
* Generate Insights
    ````bash
        make insights
    ````
* Analyse Larastan Level 8 rules for static code coverage
    ````bash
        make analyse
    ````

### Requirements
#### Must Have
- [X] The application must be written in PHP 8.2
- [X] The application must use Laravel Framework v.10
- [X] Every route must be documented using swagger (OpenAPI) so that we can test your backend APIs
- [X] The application must have at least 10 unit or feature tests
- [X] The application must use a JSON Web Token implemented with a middleware
    - [X] Implemented HS256 for Token Signing/Verification
        - [X] The token must be compliant with the RFC 7519 standard

- [X] The application must include a README.md
- [X] The application must run “out-of-the-box.”
    * We will follow the steps provided on the README.md file, such as booting the application from a shell script.
- [X] The code must be pushed into your personal repository, which is available to us.
    * We really want to see your individual commits :slight_smile:
        * Please avoid big commits by breaking them down into smaller and descriptive commits, ideally containing code specific to a feature.

#### The Recommended
- [X] The application should have database Migrations and Seeders files
- [X] Every table should have an Eloquent model and relationships (if applicable)
- [X] Every endpoint should have its own controller and request class
    * The methods of the controllers must be linked to a route
- [X] Every route should be protected by a middleware (if applicable)
- [X] The application should have unit tests, as well as feature tests for each one of the API endpoints
- [ ] The application should follow the PSR-12 standard

#### Nice to have (bonus points)
- [X] It would be nice to have a Dockerfile for the application and to be able to boot it with docker-compose or docker run
    * Laravel Sail or similar packages are not providing extra points; you should write something yourself to gain an advantage.
- [ ] It would be nice to see a Laravel IDE Helper Generator
- [X] It would be nice if Larastan Level 8 rules for static code coverage passed successfully
    * Pro Tip: Avoid ignoring errors as much as possible
- [X] It would be nice to have PHP Insights implemented with a minimum score of 75% for each quality gate

  ✨ Analysis Completed !


|   Code   |    Complexity  |   Architecture    |   Style   |
|----------|----------------|-------------------|-----------|
|   87.8%  |    77.8%       |   87.5%           |   100 %   |  




| Metric         | Score                                                     |
|----------------|-----------------------------------------------------------|
| [CODE]         | 87.8 pts within 958 lines                                 |
| [COMPLEXITY]   | 77.8 pts with average of 1.67 cyclomatic complexity       |
| [ARCHITECTURE] | 87.5 pts within 42 files                                  |
| [MISC]         | 100 pts on coding style and 0 security issues encountered |


