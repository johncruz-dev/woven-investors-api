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

## Tests

```bash
php artisan test
```

## API

| Method | Endpoint | Description |
|--------|----------|-------------|
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
- Authentication not implemented
