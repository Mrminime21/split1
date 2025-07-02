# Starlink Router Rent - Laravel Application

A comprehensive router rental platform with investment opportunities, 3-level referral system, and crypto payment integration built with Laravel.

## Features

- **Premium Router Rental**: Rent Starlink routers with guaranteed daily profits
- **Investment Plans**: 3, 6, and 12-month investment options with daily returns
- **3-Level Referral System**: Earn up to 15% commission from referrals
- **Crypto Payments**: Plisio.net integration for cryptocurrency payments
- **Real-time Analytics**: Dashboard with earnings tracking
- **Responsive Design**: Works on all devices
- **Admin Panel**: Complete management system

## Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository
   ```bash
   git clone <repository-url>
   cd starlink-router-rent
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install Node.js dependencies
   ```bash
   npm install
   ```

4. Copy environment file
   ```bash
   cp .env.example .env
   ```

5. Generate application key
   ```bash
   php artisan key:generate
   ```

6. Configure your database in `.env` file
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=starlink_router_rent
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. Run database migrations and seeders
   ```bash
   php artisan migrate --seed
   ```

8. Build frontend assets
   ```bash
   npm run build
   ```

9. Start the development server
   ```bash
   php artisan serve
   ```

## Configuration

### Payment Gateways

Add your payment gateway credentials to the `.env` file:

```
PLISIO_API_KEY=your_plisio_api_key
BINANCE_API_KEY=your_binance_api_key
BINANCE_SECRET=your_binance_secret
```

### Telegram Integration

Add your Telegram bot token:

```
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
```

### Referral Commission Rates

Configure referral commission rates:

```
REFERRAL_LEVEL_1_RATE=7.00
REFERRAL_LEVEL_2_RATE=5.00
REFERRAL_LEVEL_3_RATE=3.00
```

## Default Admin Credentials

- **Username**: admin
- **Email**: admin@starlinkrouterrent.com
- **Password**: admin123

**Important**: Change these credentials immediately after installation!

## Key Features

### User Management
- User registration with referral code support
- 3-level referral system with automatic commission tracking
- User dashboard with earnings overview
- Profile management

### Device Management
- Starlink router inventory
- Device status tracking
- Performance monitoring
- Rental assignment

### Payment System
- Multiple payment methods (crypto, bank transfer, etc.)
- Plisio.net cryptocurrency integration
- Automatic payment processing
- Withdrawal request management

### Investment System
- Multiple investment plans
- Daily profit calculation
- Automatic earnings distribution
- Compound interest options

## API Documentation

The application includes RESTful API endpoints for:
- User authentication
- Device management
- Payment processing
- Rental management
- Investment tracking

## Security Features

- Laravel Sanctum for API authentication
- CSRF protection
- SQL injection prevention
- XSS protection
- Rate limiting

## Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Configure proper database credentials
4. Set up SSL certificate
5. Configure web server (Apache/Nginx)
6. Set up cron jobs for scheduled tasks

## Scheduled Tasks

Add to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Support

For support and questions, please contact the development team or refer to the documentation.

## License

This project is proprietary software. All rights reserved.