# Repository Guidelines

## Project Structure & Module Organization

This repository is a Laravel 12 application for PSA. Business logic lives in `app/` (models, controllers, jobs, mail, console commands, and Filament resources). HTTP and console routes are in `routes/`; migrations, factories, and seeders are in `database/`. Blade views and frontend source assets are under `resources/`, with public files in `public/`. Tests are in `tests/Feature` and `tests/Unit`. Do not edit generated dependencies in `vendor/` or `node_modules/`.

## Build, Test, and Development Commands

- `composer run setup` installs PHP and Node dependencies, prepares `.env`, migrates the database, and builds assets.
- `composer run dev` starts the Laravel server, queue listener, and Vite development server together.
- `php artisan test` or `composer run test` runs the Pest suite; the Composer script clears cached config first.
- `npm run dev` serves frontend assets with Vite; `npm run build` creates production assets in `public/build`.
- `vendor/bin/pint` formats PHP according to Laravel Pint.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, four spaces, and a final newline. Use Laravel conventions: singular `StudlyCase` model names, `camelCase` methods and variables, and descriptive `snake_case` migration filenames. Keep classes in their matching `app/` namespace and organize Filament resources by feature. Use Pint for PHP formatting; keep frontend code consistent with nearby files.

## Testing Guidelines

The project uses Pest 3 with Laravel's test integration. Place request and workflow coverage in `tests/Feature` and isolated behavior checks in `tests/Unit`; name files `*Test.php` and use readable `it()` or `test()` descriptions. Run `php artisan test` after changes that affect application behavior. No coverage threshold is configured.

## Commit & Pull Request Guidelines

Recent commits use short subjects, sometimes with `upd:` or `update:` prefixes, but there is no consistent convention. Use a concise imperative subject that describes the change (for example, `Update registration QR handling`). Pull requests should summarize user-visible and data changes, link relevant issues, list validation performed, and include screenshots for UI changes. Call out migrations or configuration changes explicitly.

## Security & Configuration

Keep credentials and environment-specific values in `.env`; never commit secrets. Use `.env.example` for documented configuration keys, and review migrations for safe effects on existing member and registration data.
