# Contributing

## Before You Start

- Check existing issues and pull requests before opening a new one.
- Keep changes focused. Small, isolated pull requests are preferred.
- If the change is substantial, open an issue first so the direction can be agreed before implementation.

## Development Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
```

Run the app:

```bash
composer run dev
```

## Coding Expectations

- Follow the existing Laravel, Vue, and Tailwind conventions already used in the codebase.
- Prefer targeted fixes over broad refactors unless the refactor is necessary.
- Do not add dependencies without discussion.
- Keep public-facing changes documented in the pull request description.

## Tests

Run relevant checks before opening a pull request:

```bash
php artisan test --compact
npm run build
```

If you change PHP code formatting, also run:

```bash
vendor/bin/pint --dirty --format agent
```

## Pull Requests

- Describe what changed and why.
- Reference related issues when applicable.
- Include screenshots for UI changes.
- Call out any follow-up work or known limitations.
