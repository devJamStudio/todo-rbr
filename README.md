# Todo App

A Laravel-based Todo application with email notifications and Google Calendar integration.

## Features

- Full CRUD operations for tasks
- Task filtering by priority, status, and due date
- Email notifications for upcoming tasks
- Google Calendar integration
- User authentication
- Public task sharing with access tokens

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Node.js and NPM (for frontend assets)
- Google Calendar API credentials (for calendar integration)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd todo
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. Configure mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=your_smtp_port
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_from_address
MAIL_FROM_NAME="${APP_NAME}"
```

9. Configure Google Calendar (optional):
```env
GOOGLE_CALENDAR_CLIENT_ID=your_client_id
GOOGLE_CALENDAR_CLIENT_SECRET=your_client_secret
GOOGLE_CALENDAR_REDIRECT_URI="${APP_URL}/google-calendar/callback"
```

10. Start the development server:
```bash
php artisan serve
```

11. In a separate terminal, start the queue worker:
```bash
php artisan queue:work
```

12. For email notifications, start the scheduler:
```bash
php artisan schedule:work
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Development

- Frontend assets are compiled using Vite
- Run `npm run dev` for development
- Run `npm run build` for production

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
