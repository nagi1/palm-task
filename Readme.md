# Palmcraper – Product Scraping Challenge

![Screenshot](docs/screenshot.png)

Lightweight multi-service stack for on-demand scraping of product metadata (Amazon and Jumia) with a live product grid UI.

## Overview

Three cooperating pieces:
1. Laravel 12 API (MySQL) – stores latest snapshot of each scraped product and exposes REST endpoints (list + show + scrape).
2. Next.js (App Router) frontend – auto-refreshing product grid page (`/` & `/products`) every 30s plus individual product detail pages (`/products/:id`).
3. Go rotation microservice – provides rotating user-agent (and optional proxy) to help reduce blocking.

Only the scrape endpoint is authenticated (Laravel Sanctum personal access token). Product listing is public. No historical pricing table: each scrape overwrites current record.

## Architecture & Flow
1. Client issues `POST /api/scrape` with a product URL + Bearer token.
2. Backend determines marketplace source from URL.
3. Backend requests rotation data from the Go service (`GET /v1/rotation`) – if unreachable, falls back to local `config/scrape.php` list.
4. Marketplace-specific parser action fetches HTML, extracts fields (title, price, image) and normalizes price.
5. Product is upserted (`title, price, image_url`) using URL-derived key (e.g. ASIN for Amazon).
6. Frontend `/products` re-fetches every 30 seconds and re-renders the grid.

## Tech Stack
Backend: Laravel 12, PHP 8+, MySQL (SQLite possible for local quick runs)
Frontend: Next.js 15 (App Router), TypeScript, Tailwind CSS
Microservice: Go 1.22 (simple net/http)
Auth: Laravel Sanctum (personal access tokens)

## Data Model (Products)
Fields (table: `products`):
```
id (PK, bigint)
source (enum)
source_identifier (string, e.g. ASIN)
title (string)
price (decimal)
image_url (string nullable)
url (string original)
created_at / updated_at
```

## API Endpoints
Base URL (dev): `http://127.0.0.1:8000`

Public:
| Method | Path | Description |
|--------|------|-------------|
| GET | /api/products | List stored products (paginated) |
| GET | /api/products/{id} | Fetch single product by id |

Authenticated (Bearer token via Sanctum):
| Method | Path | Body | Description |
|--------|------|------|-------------|
| POST | /api/scrape | `{ "url": "https://..." }` | Scrape a single product URL, upsert product |

Rotation Service (Go) – default on `http://127.0.0.1:8081`:
| Method | Path | Description |
|--------|------|-------------|
| GET | /v1/rotation | Returns `{ user_agent, proxy? }` |
| GET | /health-check | Liveness probe |

## Environment Variables

### Backend (`backend/.env`)
| Key | Example | Notes |
|-----|---------|-------|
| APP_ENV | local |  |
| APP_KEY | (generated) | via `php artisan key:generate` |
| DB_CONNECTION | mysql | or `sqlite` for quick start |
| DB_DATABASE | palmcraper |  |
| DB_USERNAME | root | adjust per local setup |
| DB_PASSWORD | secret |  |
| PROXY_SERVICE_URL | http://127.0.0.1:8081 | Optional – if unset fallback list used |

### Frontend (`frontend/.env.local`)
| Key | Example | Notes |
|-----|---------|-------|
| NEXT_PUBLIC_API_BASE_URL | http://127.0.0.1:8000 | Must point to Laravel API |

### Proxy Service (`proxy-service`)
| Key | Example | Notes |
|-----|---------|-------|
| USER_AGENTS | Mozilla/5.0 A,CustomUA/1.0 | Comma-separated list; fallback internal defaults |
| PROXIES | http://user:pass@1.2.3.4:9000 | Optional comma-separated proxy URLs |

## Quick Start

Prerequisites: Git, PHP 8.x + Composer, Node.js 18+, npm, Go 1.22+, MySQL (or use SQLite for a shortcut).

### 1. Clone
```fish
git clone git@github.com:nagi1/palm-task.git
cd dev-tickets
```

### 2. Backend (Laravel API)
```fish
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Create (or reuse) a user + token
php artisan tinker --execute='($u=App\\Models\\User::first() ?: App\\Models\\User::create(["name"=>"Scrape User","email"=>"scrape@example.com","password"=>bcrypt("password")])); echo $u->createToken("scrape")->plainTextToken."\n";'

# Start dev server
php artisan serve --host=127.0.0.1 --port=8000
```

### 3. Frontend (Next.js)
```fish
cd ../frontend
npm install
printf "NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000\n" > .env.local
npm run dev
# Visit http://localhost:3000/products
```
The page revalidates (client refetch) every 30 seconds.

### 4. Go Rotation Service
```fish
cd ../proxy-service
go run main.go
# Service: :8081 (/v1/rotation, /health-check)
```

## Seed Data using Artisan Command
I have composed a simple list of Amazon and Jumia product URLs in `backend/product_urls.txt` for testing.
Run `php artisan products:scrape-url --file=<path-to-the-file>` to scrape and populate the database.

The command is also scheduled to run hourly if the file exists (see `app/Console/Kernel.php`).

## Postman Collection
A ready-to-use Postman collection is provided at `docs/Palmcraper.postman_collection.json` covering:
 - Laravel API: list products, scrape product (Bearer token auth)
 - Go Rotation Service: rotation data + health check

### Import & Configure
1. Open Postman → Import → choose the JSON file.
2. Go to the collection Variables tab and set:
   - `api_base_url` (default `http://127.0.0.1:8000`)
   - `rotation_base_url` (default `http://127.0.0.1:8081`)
   - `auth_token` (paste Sanctum personal access token output from tinker command)
   - `sample_product_url` (replace with a real Amazon/Jumia product URL)
3. Save variables (make sure current value is set).
4. Run requests individually or use the Postman Runner (the tests assert status codes and basic JSON fields).

## Useful Commands Summary
Backend:
```fish
composer install
php artisan migrate
php artisan serve
php artisan test
```
Frontend:
```fish
npm install
npm run dev
npm run build
```
Proxy service:
```fish
go run main.go
