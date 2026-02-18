# Testing Guide

## Database Configuration for Tests

The tests are configured to use MySQL by default. You have two options:

### Option 1: MySQL (Current Configuration)

1. **Create Test Database**
   ```sql
   CREATE DATABASE crypto_testing;
   ```

2. **Update phpunit.xml** (already configured)
   ```xml
   <env name="DB_CONNECTION" value="mysql"/>
   <env name="DB_DATABASE" value="crypto_testing"/>
   <env name="DB_HOST" value="127.0.0.1"/>
   <env name="DB_PORT" value="3306"/>
   <env name="DB_USERNAME" value="root"/>
   <env name="DB_PASSWORD" value=""/>
   ```

3. **Run Tests**
   ```bash
   php artisan test --compact
   ```

### Option 2: SQLite (Alternative)

If you prefer to use SQLite for faster tests:

1. **Enable SQLite Extension in PHP**
   
   For Windows (XAMPP/WAMP):
   - Open `php.ini`
   - Find and uncomment: `extension=pdo_sqlite`
   - Find and uncomment: `extension=sqlite3`
   - Restart your web server

   For Linux:
   ```bash
   sudo apt-get install php-sqlite3
   sudo systemctl restart php-fpm
   ```

   For macOS (Homebrew):
   ```bash
   brew install php-sqlite
   ```

2. **Update phpunit.xml**
   ```xml
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

3. **Run Tests**
   ```bash
   php artisan test --compact
   ```

## Running Tests

### Run All Tests
```bash
php artisan test --compact
```

### Run Specific Test Suite
```bash
php artisan test --compact --filter=AuthTest
php artisan test --compact --filter=WalletTest
php artisan test --compact --filter=TradeTest
php artisan test --compact --filter=CurrencyTest
php artisan test --compact --filter=ApiTest
```

### Run with Coverage
```bash
php artisan test --coverage
```

## Test Database

The `RefreshDatabase` trait is enabled, which means:
- Database is migrated before each test
- Database is rolled back after each test
- Each test starts with a clean database

## Troubleshooting

### Error: "could not find driver"

**For SQLite:**
- Ensure SQLite extension is enabled in PHP
- Check with: `php -m | grep -i sqlite`

**For MySQL:**
- Ensure MySQL is running
- Verify database credentials in `phpunit.xml`
- Create the test database if it doesn't exist

### Error: "Database connection failed"

1. Check MySQL is running:
   ```bash
   # Windows
   net start mysql
   
   # Linux/Mac
   sudo systemctl start mysql
   ```

2. Verify database exists:
   ```sql
   CREATE DATABASE IF NOT EXISTS crypto_testing;
   ```

3. Check credentials in `phpunit.xml` match your MySQL setup
