# Woven Investors API

Laravel API for importing investor CSV data and serving it via REST endpoints.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure MySQL in `.env` if needed (SQLite works for local dev).

## Web UI

Open `http://localhost:8000` in your browser. The dashboard lets you:

- Upload and import a CSV file
- View metrics (average age, average investment, total investments)
- Browse paginated investors
- Export investors as CSV

## Tests

```bash
php artisan test
```

## API

| POST | `/api/v1/import` | Upload CSV file (`file` field) |
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

## Security

Security features are **disabled by default** so local development works unchanged. Enable them via `.env` when deploying.

### Authentication

Set `SECURITY_API_AUTH_REQUIRED=true`, then authenticate each request with either:

| Method | Header |
|--------|--------|
| API key | `X-Api-Key: your-secret-key` |
| Sanctum token | `Authorization: Bearer {token}` |

Generate a Sanctum token:

```bash
php artisan security:create-api-token user@example.com "import-cli"
```

For the web dashboard, set `SECURITY_DASHBOARD_BEARER_TOKEN` or `SECURITY_DASHBOARD_API_KEY` so browser requests include credentials.

### Rate limiting

| Endpoint group | Default limit | Env variable |
|----------------|---------------|--------------|
| Read (metrics, list) | 120/min | `SECURITY_RATE_LIMIT_READ` |
| Import | 10/min | `SECURITY_RATE_LIMIT_IMPORT` |

Limits are keyed by authenticated user, API key, or client IP.

### Upload hardening

- MIME and size validation (`SECURITY_UPLOAD_MAX_KB`, default 10 MB)
- Content sniffing rejects binary/script payloads
- Row cap (`SECURITY_UPLOAD_MAX_ROWS`, default 50,000)

### Response headers

When `SECURITY_HEADERS_ENABLED=true` (default), responses include `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and HSTS on HTTPS.
