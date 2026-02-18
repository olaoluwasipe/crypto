# Crypto Trading API

A Laravel-based cryptocurrency trading API that enables users to buy, sell, and manage cryptocurrency trades with real-time exchange rates from CoinGecko.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture & Design Decisions](#architecture--design-decisions)
- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Fee System](#fee-system)
- [CoinGecko Integration](#coingecko-integration)
- [Database Structure](#database-structure)
- [Testing](#testing)
- [Trade-offs & Time Constraints](#trade-offs--time-constraints)
- [Time Spent](#time-spent)

## Features

- **User Authentication**: Secure registration and login using Laravel Sanctum
- **Wallet Management**: Multi-currency wallet system with transaction history
- **Cryptocurrency Trading**: Buy and sell cryptocurrencies with real-time rates
- **Exchange Rates**: Integration with CoinGecko API for live exchange rates
- **Fee Management**: Flexible fee system supporting percentage and fixed fees
- **Transaction History**: Complete audit trail of all wallet and trade transactions
- **Currency Conversion**: Real-time currency conversion between supported currencies

## Technology Stack

- **Framework**: Laravel 12
- **PHP**: 8.5.0
- **Authentication**: Laravel Sanctum v4
- **Testing**: Pest PHP v4
- **Database**: MySQL/PostgreSQL/SQLite (configurable)
- **API Integration**: CoinGecko API for exchange rates

## Architecture & Design Decisions

### Service Layer Pattern

The application follows a **Service Layer Pattern** with clear separation of concerns:

- **Controllers**: Handle HTTP requests/responses and validation
- **Services**: Contain business logic (TradeService, WalletService, CurrencyService, FeeService)
- **Contracts/Interfaces**: Define service contracts for dependency injection
- **Models**: Represent database entities with relationships
- **Resources**: Transform models for API responses

This architecture provides:
- **Testability**: Services can be easily mocked and tested
- **Maintainability**: Business logic is centralized and reusable
- **Flexibility**: Easy to swap implementations via contracts

### Database Design

**Multi-Wallet System**: Each user has separate wallets for each currency, enabling:
- Clear balance tracking per currency
- Simplified transaction management
- Easy currency-specific operations

**Transaction Audit Trail**: All operations create immutable transaction records:
- Wallet transactions for deposits/withdrawals
- Trade records linking debit and credit transactions
- Complete history with references and metadata

**Exchange Rate Storage**: Exchange rates are cached in the database:
- Reduces API calls to CoinGecko
- Enables offline rate lookups
- Bidirectional rates stored (base↔quote)

### Security Considerations

- **Password Requirements**: Strong password validation (uppercase, lowercase, number, special character, min 8 chars)
- **Token-based Authentication**: Laravel Sanctum for stateless API authentication
- **Database Transactions**: All trades use database transactions to ensure atomicity
- **Wallet Locking**: Uses `lockForUpdate()` to prevent race conditions during trades
- **Balance Validation**: Ensures sufficient balance before executing trades

### API Design

- **RESTful Endpoints**: Following REST conventions
- **Versioned API**: All routes under `/api/v1/` prefix
- **JSON Responses**: Force JSON middleware ensures consistent response format
- **Standardized Responses**: Using `ApiResponses` trait for consistent response structure
- **Error Handling**: Try-catch blocks with meaningful error messages

## Setup Instructions

### Prerequisites

- PHP 8.5.0 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js and NPM (for frontend assets if needed)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/olaoluwasipe/crypto.git
   cd crypto
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies** (if needed)
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Environment Variables**
   
   Edit `.env` file with your configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=crypto_trading
   DB_USERNAME=root
   DB_PASSWORD=
   
   COINGECKO_BASE_URL=https://api.coingecko.com/api/v3/simple
   COINGECKO_API_KEY=your_coingecko_api_key
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed Database**
   ```bash
   php artisan db:seed
   ```

8. **Fetch Exchange Rates**
   ```bash
   php artisan app:get-exchange-rate
   ```
   or, schedule it so it always has updated rates
   ```bash
   php artisan schedule:work
   ```


9. **Start Development Server**
   ```bash
   php artisan serve
   ```

   The API will be available at `http://localhost:8000`

### Default Test User

After seeding, you can use:
- **Email**: `test@example.com`
- **Password**: `password` (default factory password)

## API Documentation

See [Crypto.postman_collection.json](Crypto.postman_collection.json) for detailed API documentation with request/response examples.

## Fee System

### Fee Calculation Approach

The fee system supports two calculation methods:

1. **Percentage-based Fees**: Calculated as a percentage of the trade amount
   ```php
   fee = amount × (percentage / 100)
   ```

2. **Fixed Amount Fees**: A fixed fee regardless of trade amount
   ```php
   fee = fixed_amount
   ```

### Fee Constraints

Fees can have minimum and maximum caps:
- **Minimum Amount**: If calculated fee is below minimum, use minimum
- **Maximum Amount**: If calculated fee exceeds maximum, cap at maximum

### Fee Application

- **Buy Trades**: Fee is calculated on the cryptocurrency amount being purchased
- **Sell Trades**: Fee is calculated on the cryptocurrency amount being sold
- **Fee Currency**: Fees are deducted in the currency specified in the fee configuration

### Example Fee Calculation

For a buy trade of 0.1 BTC with:
- Percentage: 0.1%
- Min amount: 1 NGN
- Max amount: 1000000 NGN

```
Calculated fee = 0.1 BTC × 0.1% = 0.0001 BTC
Converted to NGN = 0.0001 BTC × exchange_rate
If result < 1 NGN → fee = 1 NGN
If result > 1000000 NGN → fee = 1000000 NGN
```

### Fee Configuration

Fees are stored in the `fees` table and can be configured per currency and trade type (buy/sell). See `database/seeders/FeeSeeder.php` for examples.

## CoinGecko Integration

### How It Works

1. **Command-based Updates**: Exchange rates are fetched via Artisan command
   ```bash
   php artisan app:get-exchange-rate
   ```

2. **API Endpoint**: Uses CoinGecko's Simple Price API
   ```
   GET /api/v3/simple/price?vs_currencies={base}&ids={quote_currencies}&x_cg_demo_api_key={key}
   ```

3. **Rate Storage**: 
   - Rates are stored in `exchange_rates` table
   - Bidirectional rates are saved (base→quote and quote→base)
   - Rates are marked with source: 'coingecko'

4. **Rate Usage**: 
   - Rates are fetched from database during trades
   - No real-time API calls during trade execution
   - Ensures consistent rates and reduces API costs

### Rate Update Strategy

- **Manual Updates**: Run the command manually or via cron job
- **Recommended Frequency**: Every 5-15 minutes for active trading
- **Cron Setup** (optional):
  ```bash
  */10 * * * * cd /path-to-project && php artisan app:get-exchange-rate
  ```

### API Key Setup

1. Get API key from [CoinGecko](https://www.coingecko.com/en/api)
2. Add to `.env`:
   ```env
   COINGECKO_API_KEY=your_api_key_here
   COINGECKO_BASE_URL=https://api.coingecko.com/api/v3/simple
   ```

## Database Structure

### Key Tables

- **users**: User accounts
- **currencies**: Supported cryptocurrencies and fiat currencies
- **wallets**: User wallets per currency
- **wallet_transactions**: All wallet deposits/withdrawals
- **trades**: Trade records linking transactions
- **exchange_rates**: Cached exchange rates from CoinGecko
- **fees**: Fee configuration per currency and trade type

### Relationships

- User → Wallets (1:many)
- User → Trades (1:many)
- User → WalletTransactions (1:many)
- Wallet → WalletTransactions (1:many)
- Trade → WalletTransactions (2:1, credit & debit)
- Currency → ExchangeRates (1:many, as base and quote)

## Testing

### Running Tests

Run all tests:
```bash
php artisan test --compact
```

Run specific test suite:
```bash
php artisan test --compact --filter=AuthTest
php artisan test --compact --filter=WalletTest
php artisan test --compact --filter=TradeTest
php artisan test --compact --filter=CurrencyTest
```

### Test Coverage

The test suite includes:
- **Authentication Tests**: Registration, login, logout, token refresh
- **Wallet Tests**: Add money, transaction history, filtering
- **Currency Tests**: List currencies, exchange rates, conversion
- **Trade Tests**: Buy/sell operations, trade history, validation
- **API Tests**: JSON response middleware, fallback routes

### Test Data

The database seeder includes:
- Test user account
- Supported currencies (NGN, USD, BTC, ETH, USDT)
- Fee configurations for buy/sell operations
- Sample exchange rates (run `app:get-exchange-rate` for real rates)

## Trade-offs & Time Constraints

### What Was Prioritized

1. **Core Functionality**: Focus on essential trading features (buy/sell)
2. **Transaction Safety**: Database transactions and wallet locking for data integrity
3. **API Structure**: Clean, versioned API with consistent responses
4. **Testing**: Comprehensive test coverage for core functionality

### What Was Deferred

1. **Real-time Rate Updates**: Rates are fetched via command rather than real-time during trades
   - **Reason**: Reduces API costs and ensures rate consistency
   - **Trade-off**: Slight delay in rate updates
   - **Multiple Rate Sources**: A system should utilize multiple rate providers in case of issues from one, this system does not have capability for that right now

2. **Advanced Features**:
   - Order book system
   - Limit orders
   - Stop-loss orders
   - Trading pairs management UI
   - WebSocket for real-time updates

3. **Enhanced Security**:
   - 2FA authentication
   - Rate limiting per user
   - IP whitelisting
   - Advanced fraud detection
   - Encryption

4. **Performance Optimizations**:
   - Redis caching for rates
   - Queue system for rate updates
   - Database query optimization
   - API response caching

5. **Admin Features**:
   - Admin dashboard
   - Fee management UI
   - User management
   - Transaction monitoring

### Architecture Decisions

1. **Service Layer**: Chose service layer over repository pattern for simplicity
2. **Database Transactions**: Used for all trades to ensure atomicity
3. **Wallet Locking**: `lockForUpdate()` prevents race conditions
4. **Fee System**: Flexible system supporting both percentage and fixed fees
5. **API Versioning**: Future-proof API with version prefix

## Time Spent

Approximate time breakdown:

- **Project Setup & Architecture**: 2-3 hours
  - Laravel setup, database design, service layer structure

- **Core Features Development**: 8-10 hours
  - Authentication system
  - Wallet management
  - Trading logic (buy/sell)
  - Fee calculation system
  - Currency conversion

- **CoinGecko Integration**: 2-3 hours
  - API integration
  - Exchange rate command
  - Rate storage and retrieval

- **Testing**: 3-4 hours
  - Test suite creation
  - Factory setup
  - Edge case testing

- **Documentation**: 2-3 hours
  - README, API docs
  - Code comments
  - Setup instructions

**Total Estimated Time**: 17-23 hours
