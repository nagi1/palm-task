# PalmOutsourcing Challenge

## Stack

- **Backend:** Laravel 12 (GET `/api/tasks`): Controller + Resource + tiny repo, no database.

- **Frontend:** Next.js (App Router) + TailwindL Fetches tasks and shows responsive grid.

## Quick start

```bash
# clone
git clone git@github.com:nagi1/palm-task.git
cd palm-task

# Backend (Laravel)
cd backend
cp .env.example .env
php artisan key:generate
php artisan serve  # http://127.0.0.1:8000

# Frontend (Next.js)
cd ../frontend
npm install
echo "NEXT_PUBLIC_API_BASE_URL=http://localhost:8000" > .env.local
npm run dev        # http://localhost:3000
```
