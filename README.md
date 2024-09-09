# Laravel Warehouse Information System

A warehouse information system application developed using laravel and postgresql. This application consist of several main feature, such as:
1. Authentication using a bearer token, 
2. CRUD for item, mutation, and other supporting data
3. containerize using docker to ease deployment steps
4. ...and many more

start running the application in your local machine using this following steps:
1. clone the repository
```bash
git clone git@github.com:ThiccPan/warehouse-system.git
```
2. step into the project directory
```bash
cd warehouse-system
```
3. install the dependencies
```bash
composer install
```
4. configure your .env file
```bash
# db setup example
DB_CONNECTION=pgsql
DB_HOST=dbhost
DB_PORT=5433
DB_DATABASE=db_wh_idgrow
DB_USERNAME=yourusername
DB_PASSWORD=yourpassword
```
5. run your container script
```bash
docker-compose up -d --build
```
6. configure your app migration
```bash
docker exec warehouse_system php artisan migrate
```
7. configure your application key
```bash
docker exec warehouse_system php artisan key:generate
```
8. done! you can start hitting the application api

Documentation:
- API documentation: [postman link](https://documenter.getpostman.com/view/23637484/2sAXjSxoFm)
