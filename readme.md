# Quick Setup Guide

## Background
We offer parking space location and ‘park and ride’ location data.
There is an api with two endpoints:

Search - /api/search
Details - /api/details


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

## API Endpoints

All endpoints are prefixed with `/api/v1`

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


