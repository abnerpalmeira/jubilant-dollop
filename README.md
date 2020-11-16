# Airplane booking system

### Stack
- PHP 7.4.3
- Laravel Framework 7.29.3
- MySQL 5.7

### Setup

1. Enter on the project directory and install the composer dependencies with the following command:
```sh
docker-compose run composer composer install
```
2. Clone the .env.example file in the backend folder into a .env file.

3. Make sure your 800 port is not being used.

4. Go to the base project directory (where docker-compose.yml is located) and run docker-compose up:
```sh
docker-compose up -d
```

### Migrations

1. Go to the base project directory (where docker-compose.yml is located) and run the following command:

```sh
docker exec -it laravel_docker php artisan migrate
```

### Usage

#### Book endpoint

**POST** http://localhost:800/api/book

Use this endpoint to book multiple-seat in the system.

The system expects a json in the following manner:

```json
{
	"name" :"Abner",
	"seats": 5
}
```

It will return a json with the reserved seats.

#### Flush endpoint

**POST** http://localhost:800/api/flush

Use this endpoint  if you want to reset the entire database.

### Design choices

To ensure that the constraints are being followed, I designed three algorithms that look for the best way to fit the passengers based on the constraints. They are ad hoc algorithms optimized to guarantee a good execution time.

The occupied seats are stored in the database as integer values, I decided to store them in this way, to make the coding easier, since I could convert them to an airplane seat notation just using arithmetic operations. I also use these integers to create a matrix that represents the plane map.

I created a json file called config.json, in this file you can adjust the airplane dimensions.

### Possible enhancements

Right now the system only work with airplanes that has only one aisle. A good improvement would be to prepare the code to allow multiple aisle.
