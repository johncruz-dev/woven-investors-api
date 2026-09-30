# Woven Investors API

Laravel API for importing investor CSV data and serving it via REST endpoints.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Configure MySQL in `.env` if needed (SQLite works for local dev).

**Seeded users (password: `password`):**

| Email | Role |
|-------|------|
| `admin@example.com` | Admin (can import) |
| `viewer@example.com` | Viewer (read-only) |

## Web UI

Open `http://localhost:8000` in your browser.

- `/login` and `/register` for session auth
- Dashboard: metrics, paginated investors, CSV export
- Admins can upload CSV; viewers see a read-only dashboard

With `SECURITY_AUTH_REQUIRED=false` (local default), the dashboard stays open without login.

## Tests

```bash
php artisan test
```

## API

| POST | `/api/v1/import` | Upload CSV (`admin` only when auth is on) |
| GET | `/api/v1/metrics/average-age` | Average investor age |
| GET | `/api/v1/metrics/average-investment-amount` | Average investment amount |
| GET | `/api/v1/metrics/total-investments` | Total investment count |
| GET | `/api/v1/investors` | Paginated investor list |
| GET | `/api/v1/investors?format=csv` | Export as CSV |

**CSV columns:** `investor_id,name,age,investment_amount,investment_date`  
**Date format:** `DD-MM-YYYY`

**Import example (Windows):**

```powershell
curl.exe -X POST http://localhost:8000/api/v1/import -F "file=@investors_with_dates.csv"
```

## Architecture

`Controllers → Services → Repositories → Models`

- Chunked CSV import with batch upserts (handles 10k+ rows)
- SQL aggregates for metrics (no full-table loads)
- Paginated investor listing with investment totals

## Assumptions

- `investment_amount` in the list API = sum of all investments per investor
- Average investment amount = mean across all investment records
- Re-importing the same CSV upserts existing data

## Auth & Security

### Defaults

| Environment | Auth required |
|-------------|---------------|
| `local` / `testing` | Off (unless `SECURITY_AUTH_REQUIRED=true`) |
| staging / production | **On** by default |

When auth is required:

- Dashboard redirects guests to `/login`
- API needs a **session cookie** (Sanctum SPA), **Bearer token**, or **`X-Api-Key`**
- **Admin** can import; **admin + viewer** can read
- Self-registration creates **viewer** accounts only

### Enable locally

```env
SECURITY_AUTH_REQUIRED=true
SECURITY_REGISTRATION_ENABLED=true
```

Then visit `/login` with a seeded user, or register a viewer.

### API credentials

| Method | How |
|--------|-----|
| Session (browser) | Log in via `/login` — cookie auth on same origin |
| API key | `X-Api-Key: your-secret` (`SECURITY_API_KEY`) |
| Sanctum token | `Authorization: Bearer {token}` |

```bash
php artisan security:create-api-token admin@example.com "import-cli"
```

### Roles

| Role | Dashboard | Import | Read APIs |
|------|-----------|--------|-----------|
| Admin | Full | Yes | Yes |
| Viewer | Read-only | No | Yes |

### Rate limiting & uploads

| Endpoint group | Default | Env |
|----------------|---------|-----|
| Read | 120/min | `SECURITY_RATE_LIMIT_READ` |
| Import | 10/min | `SECURITY_RATE_LIMIT_IMPORT` |

Upload limits: `SECURITY_UPLOAD_MAX_KB`, `SECURITY_UPLOAD_MAX_ROWS`. Response security headers are on by default (`SECURITY_HEADERS_ENABLED`).
