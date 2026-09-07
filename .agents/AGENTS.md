# Workspace Customization Rules

## Database & Data Integrity Rules
- **NEVER run fresh migrations**: Never execute `php artisan migrate:fresh`, `migrate:reset`, `migrate:refresh`, or any command that drops or resets database tables.
- **NEVER manipulate or alter existing database data**: Do not modify, update, overwrite, or delete any existing records in the database.
- **NEVER create dummy or test data in the database**: Do not insert dummy, mock, or temporary records (employees, candidates, users, etc.) into the application database for demonstration or testing purposes.
- **NEVER run `php artisan test` or tests with `RefreshDatabase`**: The test suite must never be run with commands that wipe/refresh the live MySQL database. Never execute `php artisan test` against the application database.

---

## Project Knowledge & Architecture Reference

### Tech Stack
- **Framework**: Laravel 13, PHP 8.4
- **Admin Panel**: Filament v5 (in `app/Filament/`)
- **Frontend Stack**: Tailwind CSS v4, Alpine.js, Blade views
- **Database**: MySQL (live data in development; data integrity rules apply)

### Key Modules & File Locations
1. **Candidate Management (`app/Filament/Resources/Candidates/`)**:
   - `CandidateResource.php`: Main resource configuration.
   - `Schemas/CandidateForm.php`: Form schema. Designation selector is dependent on `department_id` (`->options(fn (Get $get) => ...)`), nullable.
   - `Tables/CandidatesTable.php`: Table schema. Department column renders as badge showing `Department Name | Designation Name` when designation exists.
   - Models: `app/Models/Candidate.php`, `app/Models/Department.php`, `app/Models/Designation.php`.

2. **Document & Letter Studio (`resources/views/letters/`)**:
   - `create.blade.php` & `edit.blade.php`: Custom paginated A4 document editor using Alpine.js (`newTemplateState` / `editTemplateState`).
   - Page reflow engine: Uses DOM clone height measurement to split content into `.doc-page` divs with mm margins. `syncContent()` cleans internal markers and trailing empty blocks, then joins pages with `<!-- PAGE_BREAK -->`.
   - Table Editor:
     - Table resizing via `.col-resize-handle` on `td`/`th`.
     - Floating icon toolbar (`#table-ctx-menu`): Compact 2-row icon toolbar for table actions. Any button hovered for > 2 seconds triggers `#table-toolbar-tooltip` with tool info.
     - Cell merge & unmerge: `mergeSelectedCells()` and `unmergeSelectedCells()` using 2D `getTableMatrix()` coordinate map to compute rowSpan/colSpan and restore 1x1 cells on unmerge.
     - Multi-cell selection with `_selectedCells` (Drag, Click, Ctrl/Cmd/Shift+Click), styled with `.cell-selected`. Suppresses browser native drag selection and consumes trailing click with `_ignoreNextClick` to prevent accidental deselection.
     - Selection menu anchoring: `showTableMenuForSelection()` computes bounding rect across selected cells and anchors `#table-ctx-menu` below (or above if nearing viewport bottom).
     - Border side toggles (`_borderSides`: `all`, `inside`, `top`, `bottom`, `left`, `right`). When `inside` is toggled, applies to inner horizontal & vertical cell divider borders across the selection or table.
     - Floating `#ctx-swatch-popover` positioned beside trigger action row with 4x3 palette and custom color button.
     - Color inputs (`#cell-border-inp`, `#cell-bg-inp`) positioned dynamically at clicked trigger before `.click()`, preventing top-left browser default modal spawn.
     - Nested table guard in `insertTable`: checks `getCurrentCell()` to disallow tables inside table cells.
   - Controller: `app/Http/Controllers/LetterController.php`.
