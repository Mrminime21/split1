# Starlink Router Rent - Full Stack Application

A comprehensive router rental platform with investment opportunities, 3-level referral system, and crypto payment integration built with React, Node.js, Express, and MySQL.

## Project Structure

The project is split into two main parts:

### Frontend
- React with TypeScript
- Tailwind CSS for styling
- React Router for navigation
- Axios for API requests
- React Hot Toast for notifications
- Lucide React for icons

### Backend
- Node.js with Express
- MySQL database
- JWT authentication
- Email notifications with Nodemailer
- Crypto payment integration with Plisio.net

## Features

- **Premium Router Rental**: Rent Starlink routers with guaranteed daily profits
- **Investment Plans**: 3, 6, and 12-month investment options with daily returns
- **3-Level Referral System**: Earn up to 15% commission from referrals
- **Crypto Payments**: Plisio.net integration for cryptocurrency payments
- **Real-time Analytics**: Dashboard with earnings tracking
- **Responsive Design**: Works on all devices
- **Admin Panel**: Complete management system

## Getting Started

### Prerequisites

- Node.js 18.0 or higher
- MySQL 8.0 or higher
- npm or yarn

### Installation

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/starlink-router-rent.git
   cd starlink-router-rent
   ```

2. Install dependencies
   ```bash
   npm run install:all
   ```

3. Configure environment variables
   ```bash
   # Backend
   cp backend/.env.example backend/.env
   # Frontend
   cp frontend/.env.example frontend/.env
   ```

4. Set up the database
   - Create a MySQL database
   - Update the database configuration in `backend/.env`
   - The tables will be created automatically on first run

5. Start the development servers
   ```bash
   npm run dev
   ```

## Development

### Frontend Development

```bash
cd frontend
npm run dev
```

### Backend Development

```bash
cd backend
npm run dev
```

### Building for Production

```bash
# Build frontend
npm run build

# Start production server
npm run start
```

## API Documentation

The API is organized around REST principles. It accepts JSON request bodies, returns JSON responses, and uses standard HTTP response codes.

### Base URL

```
http://localhost:3000/api
```

### Authentication

Most endpoints require authentication. Include the JWT token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN
```

### Endpoints

- `/api/auth` - Authentication routes
- `/api/users` - User management
- `/api/devices` - Device management
- `/api/rentals` - Rental management
- `/api/investments` - Investment management
- `/api/payments` - Payment processing
- `/api/referrals` - Referral system
- `/api/webhooks` - Payment webhooks
- `/api/admin` - Admin panel routes

## Deployment

### Frontend

The frontend is a static site that can be deployed to any static hosting service:

```bash
cd frontend
npm run build
```

This will create a `dist` directory with the built assets.

### Backend

The backend requires a Node.js environment:

```bash
cd backend
npm run start
```

For production, consider using a process manager like PM2:

```bash
npm install -g pm2
pm2 start backend/src/server.js
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.