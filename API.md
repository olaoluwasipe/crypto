# API Documentation

Base URL: `http://localhost:8000/api/v1`

All API responses are in JSON format. The API uses Laravel Sanctum for authentication.

## Authentication

Most endpoints require authentication. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message"
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field": ["Error message"]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "last_page": 10,
    "from": 1,
    "to": 10
  }
}
```

---

## Public Endpoints

### 1. API Root

**GET** `/`

Check API status.

**Response:**
```json
{
  "message": "Hello World"
}
```

---

### 2. Register User

**POST** `/auth/register`

Register a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123!@#",
  "password_confirmation": "Password123!@#"
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `email`: required, valid email, unique
- `password`: required, string, min 8, must contain uppercase, lowercase, number, and special character
- `password_confirmation`: required, must match password

**Response:**
```json
{
  "success": true,
  "message": "Register successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

---

### 3. Login

**POST** `/auth/login`

Authenticate user and get access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "Password123!@#"
}
```

**Validation Rules:**
- `email`: required, valid email, must exist
- `password`: required, string, min 8

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

---

## Authenticated Endpoints

All endpoints below require authentication token in the Authorization header.

### 4. Get Current User

**GET** `/user`

Get authenticated user's profile.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

---

### 5. Logout

**POST** `/logout`

Revoke current access token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": {}
}
```

---

### 6. Refresh Token

**POST** `/refresh`

Refresh the current access token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Refresh successful",
  "data": {
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

---

### 7. Add Money to Wallet

**POST** `/add-money`

Add funds to user's wallet.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "amount": 1000,
  "currency": "ngn"
}
```

**Validation Rules:**
- `amount`: required, numeric, min 10
- `currency`: required, string, must exist in currencies table

**Response:**
```json
{
  "success": true,
  "message": "Money added successfully",
  "data": {
    "id": 1,
    "amount": "1000.00",
    "currency": "NGN",
    "type": "credit",
    "reference": "CR-xxxxxxxxxx",
    "status": "completed",
    "created_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

---

### 8. Get Wallet Transactions

**GET** `/transactions`

Get paginated list of wallet transactions.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `currency` (optional): Filter by currency symbol (e.g., "ngn", "btc")
- `type` (optional): Filter by type ("credit" or "debit")
- `status` (optional): Filter by status ("pending", "completed", "cancelled")
- `per_page` (optional): Items per page (default: 10)

**Example:**
```
GET /transactions?currency=ngn&type=credit&per_page=20
```

**Response:**
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": [
    {
      "id": 1,
      "amount": "1000.00",
      "currency": "NGN",
      "type": "credit",
      "reference": "CR-xxxxxxxxxx",
      "status": "completed",
      "created_at": "2024-01-01T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 10,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 10
  }
}
```

---

### 9. List Currencies

**GET** `/currencies`

Get list of all active currencies.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Currencies retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Nigerian Naira",
      "symbol": "ngn",
      "code": "ngn",
      "precision": 2,
      "type": "fiat",
      "min_trade_amount": 100,
      "max_trade_amount": 1000000,
      "logo": "https://example.com/logo.png",
      "status": 1
    }
  ]
}
```

---

### 10. Get Exchange Rates

**GET** `/exchange-rates`

Get all available exchange rates.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Exchange rates retrieved successfully",
  "data": [
    {
      "base_currency": "Nigerian Naira",
      "quote_currency": "Bitcoin",
      "rate": "NGN 15000000.00"
    }
  ]
}
```

---

### 11. Convert Currency

**POST** `/convert-currency`

Convert amount from one currency to another.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "base_currency": "ngn",
  "quote_currency": "btc",
  "amount": 1500000
}
```

**Validation Rules:**
- `base_currency`: required, string
- `quote_currency`: required, string
- `amount`: required, numeric

**Response:**
```json
{
  "success": true,
  "message": "Currency converted successfully",
  "data": {
    "base_currency": "NGN",
    "quote_currency": "BTC",
    "amount": 1500000,
    "converted_amount": 0.1,
    "rate": 15000000
  }
}
```

---

### 12. Buy Cryptocurrency

**POST** `/trades/buy`

Buy cryptocurrency using fiat currency.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "amount": 1000,
  "wallet": "ngn",
  "currency": "btc"
}
```

**Validation Rules:**
- `amount`: required, numeric, min 0
- `wallet`: required, string, must exist in currencies
- `currency`: required, string, must exist in currencies

**Response:**
```json
{
  "success": true,
  "message": "Trade successful",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "type": "buy",
    "base_currency": "NGN",
    "quote_currency": "BTC",
    "base_amount": "1000.00",
    "quote_amount": "0.00006667",
    "fee": "0.00000007",
    "rate": "15000000.00",
    "status": "pending",
    "executed_at": "2024-01-01T12:00:00.000000Z",
    "created_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

**Notes:**
- Amount is in the wallet currency (e.g., NGN)
- Fee is calculated on the cryptocurrency amount
- Trade status starts as "pending"

---

### 13. Sell Cryptocurrency

**POST** `/trades/sell`

Sell cryptocurrency for fiat currency.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "amount": 0.001,
  "wallet": "btc"
}
```

**Validation Rules:**
- `amount`: required, numeric, min 0
- `wallet`: required, string, must exist in currencies

**Note:** The `currency` field is automatically set to "ngn" for sell operations.

**Response:**
```json
{
  "success": true,
  "message": "Trade successful",
  "data": {
    "id": 2,
    "reference": "550e8400-e29b-41d4-a716-446655440001",
    "type": "sell",
    "base_currency": "BTC",
    "quote_currency": "NGN",
    "base_amount": "0.001",
    "quote_amount": "15000.00",
    "fee": "0.0000001",
    "rate": "15000000.00",
    "status": "pending",
    "executed_at": "2024-01-01T12:00:00.000000Z",
    "created_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

**Notes:**
- Amount is in the wallet currency (e.g., BTC)
- Automatically converts to NGN
- Fee is calculated on the cryptocurrency amount

---

### 14. Get Trade History

**GET** `/trades`

Get paginated list of user's trades.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status ("pending", "completed", "cancelled")
- `type` (optional): Filter by type ("buy" or "sell")
- `start_date` (optional): Filter trades from date (YYYY-MM-DD)
- `end_date` (optional): Filter trades to date (YYYY-MM-DD)
- `per_page` (optional): Items per page (default: 10)

**Example:**
```
GET /trades?status=completed&type=buy&per_page=20
```

**Response:**
```json
{
  "success": true,
  "message": "Transactions retrieved successfully",
  "data": [
    {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "type": "buy",
      "base_currency": "NGN",
      "quote_currency": "BTC",
      "base_amount": "1000.00",
      "quote_amount": "0.00006667",
      "fee": "0.00000007",
      "rate": "15000000.00",
      "status": "completed",
      "executed_at": "2024-01-01T12:00:00.000000Z",
      "created_at": "2024-01-01T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 10,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 10
  }
}
```

---

### 15. Get Trade Details

**GET** `/trades/{reference}`

Get details of a specific trade by reference.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction retrieved successfully",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "type": "buy",
    "base_currency": "NGN",
    "quote_currency": "BTC",
    "base_amount": "1000.00",
    "quote_amount": "0.00006667",
    "fee": "0.00000007",
    "rate": "15000000.00",
    "status": "completed",
    "executed_at": "2024-01-01T12:00:00.000000Z",
    "created_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

---

## Error Codes

- `200`: Success
- `400`: Bad Request (validation errors, business logic errors)
- `401`: Unauthorized (missing or invalid token)
- `404`: Not Found (resource doesn't exist)
- `422`: Validation Error (invalid input data)
- `500`: Internal Server Error

---

## Rate Limiting

Currently, rate limiting is not implemented. Consider implementing it for production use.

---

## Example cURL Requests

### Register User
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!@#",
    "password_confirmation": "Password123!@#"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123!@#"
  }'
```

### Buy Cryptocurrency
```bash
curl -X POST http://localhost:8000/api/v1/trades/buy \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "amount": 1000,
    "wallet": "ngn",
    "currency": "btc"
  }'
```

### Get Trade History
```bash
curl -X GET "http://localhost:8000/api/v1/trades?status=completed&per_page=20" \
  -H "Authorization: Bearer {token}"
```
