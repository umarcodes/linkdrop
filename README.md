# LinkDrop

[![CI](https://github.com/umarcodes/linkdrop/actions/workflows/ci.yml/badge.svg)](https://github.com/umarcodes/linkdrop/actions/workflows/ci.yml)

A self-hosted bio link manager — your own Linktree, on your own server.

## Features

- 🔗 Unlimited links with emoji icons and edit-in-place
- 📊 Click analytics with per-link and daily breakdowns
- 🌙 Clean dark UI with live phone preview
- 🔒 Token-based auth via Laravel Sanctum
- 📱 Public profile at `/{username}`
- 🚀 One-command deploy script

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13 |
| Frontend | Vue 3 (Composition API) |
| Auth | Laravel Sanctum (token) |
| Database | SQLite (dev) / MySQL 8 (production) |
| Build | Vite 8 + Tailwind CSS v4 |
| Web Server | Nginx + PHP-FPM |
| CI | GitHub Actions |

## Requirements

- PHP 8.3+
- Node.js 22+
- Composer

## Local Setup

```bash
# 1. Clone
git clone https://github.com/umarcodes/linkdrop.git
cd linkdrop

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Database
touch database/database.sqlite
php artisan migrate

# 5. Storage symlink
php artisan storage:link

# 6. Start dev servers
php artisan serve &
npm run dev
```

Visit `http://localhost:8000`.

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `LinkDrop` | Application name |
| `APP_URL` | `http://localhost:8000` | Base URL |
| `DB_CONNECTION` | `sqlite` | `sqlite` or `mysql` |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_DATABASE` | — | MySQL database name |
| `DB_USERNAME` | — | MySQL username |
| `DB_PASSWORD` | — | MySQL password |
| `VITE_API_URL` | `/api` | API base URL (leave as-is for local) |

## API Reference

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | — | Register, returns token |
| POST | `/api/login` | — | Login, returns token |
| POST | `/api/logout` | ✓ | Revoke current token |
| GET | `/api/me` | ✓ | Authenticated user |
| GET | `/api/links` | ✓ | List links |
| POST | `/api/links` | ✓ | Create link |
| PUT | `/api/links/{id}` | ✓ | Update link |
| DELETE | `/api/links/{id}` | ✓ | Delete link |
| POST | `/api/links/reorder` | ✓ | Reorder links |
| GET | `/api/analytics` | ✓ | Analytics data |
| GET | `/api/p/{username}` | — | Public profile |
| POST | `/api/p/{username}/click/{id}` | — | Track link click |

## Deployment

```bash
# Configure Nginx
cp deploy/nginx.conf /etc/nginx/sites-available/linkdrop
ln -s /etc/nginx/sites-available/linkdrop /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Deploy
bash deploy/deploy.sh
```

## License

MIT — see [LICENSE](LICENSE).
