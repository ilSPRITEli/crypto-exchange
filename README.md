## Crypto Exchange (Laravel)

Minimal crypto-exchange API project (Laravel 13 + PHP 8.3) with:
- User registration / login / profile
- Orders (buy/sell)
- Trades (create/complete/cancel)
- Crypto transfers (internal/external + history)
- Wallets

## Requirements
- **PHP**: 8.3+
- **Composer**
- **Node.js** + npm (optional; API works without it)
- **Database**:
  - Recommended: Docker Desktop + `docker compose`
  - Or: local Postgres

---

## Setup (Postgres via Docker)

### 1) Start Postgres

```bash
docker compose up -d
```

Docker Postgres (from `docker-compose.yml`):
- **Host**: `127.0.0.1`
- **Port**: `5433`
- **DB**: `crypto_exchange_db`
- **User**: `postgres`
- **Password**: `postgres`

### 2) Install dependencies

```bash
composer install
```

### 3) Create `.env`
Copy `.env.example` to `.env`, then update these values:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=crypto_exchange
DB_USERNAME=postgres
DB_PASSWORD=postgres
APP_KEY= <<<<for this variable - run php artisan key:generate afer created .env file >>>

APP_DEBUG=true
APP_ENV=local
SESSION_DRIVER=cookie

```

Generate app key:

```bash
php artisan key:generate
```

### 4) Migrate + Seed

```bash
php artisan migrate:fresh --seed
```

### 5) Run

```bash
php artisan serve
```

App URL: `http://127.0.0.1:8000`

---

## API Endpoints

Base path: `/api`

## Endpoint responsibilities (business logic)

This section describes what each endpoint does, from a business perspective (not implementation details).

### Auth
- **`POST /api/register`**: Create a new user account.
  - **Rules**: unique email, password min length, password confirmation must match.
- **`POST /api/login`**: Attempt to log a user in with email/password.
  - **Rules**: email must exist, password must match.
- **`GET /api/profile`**: Get the current user profile (who is calling the API).
  - **Note**: depending on your session/auth setup, this may return `null` without proper authentication.

### Lookups (for UI dropdowns / testing)
- **`GET /api/lookups`**: Convenience endpoint for looking up user and cryptos.
  - Returns **users** (id, name, email) and **cryptocurrencies** (id, code, name, is_active).

### Orders
- **`GET /api/orders`**: List orders in the market (buy/sell).
- **`GET /api/orders/{id}`**: View a single order and its details.
- **`POST /api/orders`**: Create a buy/sell order.
  - **Rules**: order_type ∈ {buy,sell}, fiat ∈ {THB,USD}, amount > 0, price_per_unit > 0, crypto must exist and be active.
  - **Sell rule**: seller must have a wallet and sufficient crypto balance.
  - **On create**: `remaining_amount = amount`, `status = open`.
- **`PUT /api/orders/{id}`**: Update an existing order (only while open).
  - **Rules**: cannot update completed/cancelled orders.
  - **Amount rule**: new amount cannot be lower than the already-used amount.
- **`DELETE /api/orders/{id}`**: Cancel an order (soft cancel by status).
  - **Rules**: only open orders can be cancelled.

### Trades
- **`GET /api/trades`**: List executed trades.
- **`GET /api/trades/{id}`**: View a single trade and related data.
- **`POST /api/trades`**: Create a trade from an order (matching flow).
  - **Rules**: order must be open; trade amount must be ≤ order remaining_amount.
  - **Consistency**: crypto/fiat/price must match the order (no negotiation in this demo).
  - **Role**: if order_type=sell then seller must be the order owner; if order_type=buy then buyer must be the order owner.
  - **On create**: decreases the order’s `remaining_amount`; if it becomes 0, order becomes `completed`.
- **`POST /api/trades/{id}/complete`**: Mark a trade as completed.
  - **Rules**: cannot complete cancelled trades; cannot complete twice.
- **`POST /api/trades/{id}/cancel`**: Cancel a trade.
  - **Rules**: cannot cancel completed trades; cannot cancel twice.
  - **On cancel**: returns trade amount back to the order’s remaining_amount (clamped to not exceed order.amount) and re-opens the order.

### Transfers
- **`POST /api/transfers/internal`**: Transfer crypto between two users inside the system.
  - **Rules**: sender ≠ receiver; sender wallet must exist and have sufficient balance; receiver wallet is created if missing.
  - **On transfer**: sender wallet decreases, receiver wallet increases, transfer status is recorded.
- **`POST /api/transfers/external`**: Transfer crypto out of the system to an external address.
  - **Rules**: sender wallet must exist and have sufficient balance; external_address is required.
  - **On transfer**: sender wallet decreases; transfer is recorded as external.
- **`GET /api/transfers/history?user_id=...`**: List transfer history for a user (as sender or receiver).
  - **Rules**: `user_id` is required.

### Wallets
- **`GET /api/wallets`**: List wallets (balances) in the system.
- **`GET /api/wallets/user/{userId}`**: List wallets for a specific user.
  - **Rules**: user must exist; returns empty list if user has no wallets.

---

## Notes / Gotchas

### `relation "sessions" does not exist`
If you use `SESSION_DRIVER=database` before migrating, Postgres will error because the `sessions` table is not created yet.

- Fix: run migrations (`php artisan migrate` / `migrate:fresh`) before `php artisan serve` or just using `SESSION_DRIVER=cookie`.

### UUID primary keys
All core tables use UUID primary keys and UUID foreign keys.

