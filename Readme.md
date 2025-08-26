# PalmOutsourcing Challenge

## Overview

This repository contains a small full-stack sample app used for the PalmOutsourcing challenge.

- Backend: Laravel 12 API that exposes /api/tasks and includes migrations, a Task model and a seeder.
- Frontend: Next.js (App Router) + Tailwind CSS, fetches tasks from the backend and shows them in a responsive grid.

## Quick start

These steps assume you have Git, PHP (with Composer), Node.js and npm installed.
The default ports used by the project are:

- Backend: http://127.0.0.1:8000
- Frontend: http://localhost:3000

1. Clone

```fish
git clone git@github.com:nagi1/palm-task.git
cd palm-task
```

2. Backend (Laravel)

```fish
cd backend
composer install
cp .env.example .env
php artisan key:generate
# ensure the sqlite database file exists (the project includes database/database.sqlite)
touch database/database.sqlite
# run migrations and seeders
php artisan migrate --seed
# start the development server
php artisan serve --host=127.0.0.1 --port=8000
# the API will be available at http://127.0.0.1:8000/api/tasks
```

Notes:

- If you prefer MySQL/Postgres, update `DB_CONNECTION`, `DB_HOST`, etc. in `.env` before running migrations.

3. Frontend (Next.js)

```fish
cd ../frontend
npm install
# create an env file pointing to the backend API
printf "NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000\n" > .env.local
# start the dev server
npm run dev
# open http://localhost:3000
```

Notes:

- The frontend reads `NEXT_PUBLIC_API_BASE_URL` to call the backend API.
- Linting and build scripts are available in `package.json`.

## Project structure (high level)

- backend/ — Laravel app, migrations, models, seeders and API routes.
- frontend/ — Next.js app using the App Router and Tailwind CSS.

## Useful commands

- Backend

  - composer install
  - php artisan migrate --seed
  - php artisan serve
  - php artisan test

- Frontend
  - npm install
  - npm run dev
  - npm run build
  - npm run start
