# CSV Upload Project

## Overview

This project is a Laravel-based web application designed to handle CSV file uploads and processing. It provides a robust framework for managing product data through CSV imports, leveraging Laravel's powerful features and a modern frontend stack.

## Features

- CSV file upload and processing
- Product data management
- User authentication and authorization
- RESTful API endpoints
- Frontend built with Tailwind CSS and Vite

## Requirements

- PHP 8.1+
- Composer
- Node.js and npm
- MySQL or compatible database

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/csvupload.git
   cd csvupload
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Install JavaScript dependencies:
   ```
   npm install
   ```

4. Copy the `.env.example` file to `.env` and configure your environment variables:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Run database migrations:
   ```
   php artisan migrate
   ```

7. Seed the database (optional):
   ```
   php artisan db:seed
   ```

## Usage

1. Start the development server:
   ```
   php artisan serve
   ```

2. Compile assets:
   ```
   npm run dev
   ```

3. Access the application in your web browser at `http://localhost:8000`

## Testing

Run the test suite using PHPUnit:
```
php artisan test
```

## Database

The project uses migrations to define the database schema. Key tables include:

- `products`: Stores product information imported from CSV files
- `users`: Manages user accounts
- `cache`: Handles application caching

## Redis and Laravel Queue

This project uses Redis for caching and Laravel Queue for background job processing. Here's how to set it up:

### Redis Setup

1. Ensure Redis is installed on your system. If not, you can install it using:
   ```
   sudo apt-get install redis-server
   ```

2. Start the Redis server:
   ```
   sudo service redis-server start
   ```

3. In your `.env` file, set the Redis connection:
   ```
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

### Laravel Queue

1. Set up the queue connection in your `.env` file:
   ```
   QUEUE_CONNECTION=redis
   ```

2. Run the queue worker:
   ```
   php artisan queue:work
   ```

3. For production, it's recommended to use a process monitor like Supervisor to keep the queue worker running. Here's a basic Supervisor configuration:

   ```
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   user=www-data
   numprocs=8
   redirect_stderr=true
   stdout_logfile=/path/to/your/project/worker.log
   ```

### Using Queues

To dispatch a job to the queue, you can use the `dispatch` method:

```php
use App\Jobs\ProcessCsvUpload;

ProcessCsvUpload::dispatch($fileUpload);
```

This will add the job to the queue, which will then be processed by the queue worker.

### Monitoring Queues

You can monitor your queues using Laravel's built-in commands:

- View failed jobs:
  ```
  php artisan queue:failed
  ```

- Retry failed jobs:
  ```
  php artisan queue:retry all
  ```

- Clear failed jobs:
  ```
  php artisan queue:flush
  ```

For more advanced monitoring, consider using Laravel Horizon if you're using Redis as your queue driver.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgements

- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Vite](https://vitejs.dev)

This README provides a comprehensive overview of your CSV upload project, including installation instructions, usage guidelines, and key features. It also includes sections on testing, file uploads, database structure, and contribution guidelines. Feel free to adjust any details to better match your specific project requirements or add any additional sections you think would be helpful for users and contributors.