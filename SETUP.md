# Setup Guide

## Environment Variables

Copy `.env.example` into `.env` file in the root directory

```bash
    cp .env.example .env
```

## Quick Setup Steps

1. Copy the environment variables above to a `.env` file
2. Generate application key: `php artisan key:generate`
3. Run migrations: `php artisan migrate`
4. Seed database: `php artisan db:seed`
5. Fetch exchange rates: `php artisan app:get-exchange-rate`
6. Start server: `php artisan serve`

## Test Users

After seeding, you can use these test accounts:
- Email: `test@example.com`
- Email: `john@example.com`
- Email: `jane@example.com`
- Password: `password` (default factory password)

## CoinGecko API Key

1. Visit [CoinGecko API](https://www.coingecko.com/en/api)
2. Sign up for a free account
3. Get your API key
4. Add it to `.env` as `COINGECKO_API_KEY`
