# Workspace Customization Rules

## Database & Data Integrity Rules
- **NEVER run fresh migrations**: Never execute `php artisan migrate:fresh`, `migrate:reset`, `migrate:refresh`, or any command that drops or resets database tables.
- **NEVER manipulate or alter existing database data**: Do not modify, update, overwrite, or delete any existing records in the database.
- **NEVER create dummy or test data in the database**: Do not insert dummy, mock, or temporary records (employees, candidates, users, etc.) into the application database for demonstration or testing purposes.
- **NEVER run `php artisan test` or tests with `RefreshDatabase`**: The test suite must never be run with commands that wipe/refresh the live MySQL database. Never execute `php artisan test` against the application database.
