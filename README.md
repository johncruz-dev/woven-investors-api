# Woven Investors API

Laravel backend service for importing investor CSV data and exposing it through RESTful JSON APIs.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (production) or SQLite (local/testing)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### MySQL (recommended for assessment)

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=woven_investors
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database, then run migrations:

```bash
php artisan migrate
```

### SQLite (quick local start)

The default `.env.example` uses SQLite. Ensure the database file exists:

```bash
touch database/database.sqlite
php artisan migrate
```

## Running the application

```bash
php artisan serve
```

The API is available at `http://localhost:8000/api/v1`.

## Running tests

```bash
php artisan test
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/import` | Upload and import a CSV file |
| `GET` | `/api/v1/metrics/average-age` | Average age across all investors |
| `GET` | `/api/v1/metrics/average-investment-amount` | Average amount per investment record |
| `GET` | `/api/v1/metrics/total-investments` | Total number of investment records |
| `GET` | `/api/v1/investors` | Paginated list of unique investors |
| `GET` | `/api/v1/investors?format=csv` | Export all investors as CSV |

### Import CSV

```bash
curl -X POST http://localhost:8000/api/v1/import \
  -F "file=@investors_with_dates.csv"
```

Expected CSV columns:

```
investor_id,name,age,investment_amount,investment_date
```

Date format: `DD-MM-YYYY` (e.g. `13-11-2024`).

### Example responses

**Average age**

```json
{ "average_age": 48.25 }
```

**Investors list**

```json
{
  "data": [
    {
      "investor_id": 1001,
      "name": "Daniel Nelson",
      "age": 28,
      "investment_amount": 328085.43
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 200,
    "last_page": 4
  }
}
```

Query parameters for `/investors`:

- `per_page` — results per page (default 50, max 200)
- `page` — page number
- `format=csv` — stream a CSV download

## Architecture

Service-Oriented Architecture with thin controllers:

```
Controllers → Services → Repositories → Models
```

- **CsvImportService** — chunked CSV parsing and batch upserts (500 rows per chunk)
- **InvestorMetricsService** — aggregate calculations via database queries
- **InvestorRepository / InvestmentRepository** — data access layer

### Data model

- **investors** — one row per unique `investor_id` (stored as `external_id`)
- **investments** — one row per investor per date; unique on `(investor_id, investment_date)`

Re-importing the same CSV safely upserts existing records.

### Scalability notes

- CSV import uses chunked reads and `upsert` batch writes to handle 10k+ rows
- Aggregate endpoints use SQL `AVG` / `COUNT` — no full-table loading into memory
- Investor listing uses pagination and `withSum` for efficient totals

## Assumptions & next steps

| Assumption | Detail |
|------------|--------|
| Average investment amount | Calculated as the mean of all individual investment records, not per-investor totals |
| Investor `investment_amount` in list API | Sum of all investments for that investor |
| Re-imports | Upsert semantics — same investor/date updates the amount |
| Auth | Not implemented; would add API tokens or OAuth for production |

**Possible improvements given more time:**

- Queue-based async import for very large files
- OpenAPI/Swagger documentation
- Row-level validation reporting (skip bad rows, return a summary)
- Database indexes on frequently filtered columns
- API versioning middleware and rate limiting

## License

MIT
