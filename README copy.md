# Simple Symfony API Example with Validation

### Description
A RESTful API in PHP that provides basic CRUD operations (Create, Read, Update, Delete) for a task management system. The API is based on an SQLite database and Symfony. The API can be accessed at `/tasks`.

* GET `/tasks`
* GET `/tasks/{id}`
* POST `/tasks`
* PUT `/tasks/{id}`
* DELETE `/tasks/{id}`

**Parameters for Pagination, Limit, and Filtering:**

* GET `/tasks`
    * page
    * limit
    * status

Example: `/tasks?page=2&limit=50&status=completed`

### Requirements
* PHP >= 8.2
* Composer
* Symfony CLI (`https://symfony.com/download`)

### Installation 
* If necessary, grant execution permissions to the Bash script:  
  `chmod +x bin/dev/install`
* Run the "install" Bash script:  
  `./bin/dev/install` (If it doesn't work properly, execute the commands manually)
* Start the Symfony server:  
  `symfony server:start`
* Test using the generated URL and port (use Postman or a similar tool)

### Tests
* Run: `php bin/phpunit`
