# Quick Setup Guide

## Background

We offer parking space location and ‘park and ride’ location data.
There is an api with two endpoints:

1. Search - `/v1/api/search`

2. Details - `/v1/api/details`


## Changelog

### v1.0.1 (YYYY-MM-DD)
- Upgraded Laravel framework to v10.48.29
- Updated PHP compatibility to 8.2
- Removed outdated CORS configurations
- Added response wrapper for consistent API format
- Enhanced Location resource with owner information
- Implemented caching for search results
- Added validation for latitude/longitude coordinates

## Features
- JWT Authentication
- Email verification
- Password reset functionality
- Location search with coordinate-based filtering
- Detailed location information
- Response caching
- Input validation
- Park and Ride location data
- Parking space location data

## Installation

1. Clone repository and install dependencies

bash `git clone [repository-url] cd [project-directory] composer install`

2. Setup environment file 

bash `cp .env.example .env`

3. Generate application key

bash `./vendor/bin/sail artisan key:generate`

4. Generate JWT token

bash `./vendor/bin/sail artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"`
bash `./vendor/bin/sail artisan jwt:secret`

This will update our .env file with something like this:
JWT_SECRET=xxxxxxxx

5. Start Docker containers

bash `./vendor/bin/sail up -d` OR `docker compose down && docker compose up -d`

## Database Setup

1. Run Migration

bash `./vendor/bin/sail artisan migrate`

2. Run Seeder

bash `./vendor/bin/sail artisan db:seed`

3. Clear cache files

bash `./vendor/bin/sail artisan optimize:clear`

## API Endpoints

All endpoints are prefixed with `/api/v1`

### Postman Collection

You can find the published postman collection here - https://documenter.getpostman.com/view/24345482/2sB2j1hCgQ

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/login` | Authenticate user and get token | No |
| POST | `/register` | Register new user | No |
| POST | `/logout` | Logout user | Yes |
| POST | `/refresh` | Refresh JWT token | Yes |

### Password Reset

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/password/email` | Send password reset email | No |
| POST | `/password/reset` | Reset password | Yes |

### Search Endpoints
Requires authentication and email verification

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/search` | Search for parking locations | Yes |
| GET | `/details` | Get detailed information about parking locations | Yes |


## Running Tests

1. Run All Tests

bash `./vendor/bin/phpunit tests/Feature/`

2. Run Specific Test

bash `/vendor/bin/phpunit tests/Feature/SearchControllerTest.php --filter testDetailsEndpoint`

3. Run Test with Coverage

bash `./vendor/bin/phpunit --coverage-text`

4. Refresh Database and Re-seed

bash `./vendor/bin/sail artisan migrate:fresh --seed`


