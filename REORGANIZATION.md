# Project Reorganization — 2026-08-11

This document describes a cleanup pass that separated stylesheets/scripts
into `assets/` and PHP business logic into `functions/`, matching the
structure already started in `functions/add.php`. **Not committed yet** —
working tree only.

## New structure

```
ams/
├── assets/
│   ├── style.css      # was root style.css
│   └── app.js         # was empty; now holds all consolidated inline JS
├── functions/
│   ├── add.php        # add-student form processing
│   ├── admin.php       # user list, edit, delete
│   ├── edit.php         # edit-student form processing
│   ├── index.php        # dashboard session/role guard
│   ├── login.php        # login form processing
│   ├── register.php     # registration form processing
│   ├── report.php       # attendance report queries
│   └── student.php      # attendance save/delete + student listing
├── includes/
│   └── db.php          # unchanged — shared mysqli connection
├── img/
│   └── pmanalologo.jpg # unchanged
├── config.php           # unchanged — DB credentials
├── message.php           # unchanged — shared flash-message partial
├── add.php, admin.php, edit.php, index.php, login.php,
├── register.php, report.php, student.php
│                        # now thin: session_start() + one require_once + HTML
└── logout.php            # unchanged (no logic to extract)
```

## Pattern applied to every page

Each page file went from "PHP logic + HTML in one file" to:

```php
<?php
session_start();                              // only where the original had it
require_once __DIR__ . '/functions/<page>.php';
?>
<!doctype html>
...
```

`functions/<page>.php` holds everything that used to sit above the
`<!doctype html>` line: the `require_once .../includes/db.php`, POST/GET
handling, validation, and DB queries — unchanged in behavior, just moved.
Variables the HTML depends on (`$errors`, `$student`, `$users`, etc.) are
still set the same way, so the markup below didn't need to change.

## CSS / JS consolidation

- Every `<link href="style.css">` (and the one built into `admin.php`'s
  inline "Forbidden" HTML string) now points at `assets/style.css`.
- `assets/style.css`'s background-image rule was updated from
  `url("img/pmanalologo.jpg")` to `url("../img/pmanalologo.jpg")` —
  CSS `url()` paths resolve relative to the stylesheet's own location, so
  moving the file into `assets/` would otherwise have broken the image
  silently.
- Three inline `<script>` blocks were extracted into `assets/app.js`,
  each still guarded by an element-existence check so the file is safe
  to load on every page even though each block only applies to one:
  - `login.php` — password-visibility toggle
  - `add.php` — client-side add-student form validation
  - `student.php` — present/absent checkbox exclusivity, "Reset All
    Selection" button, live clock
- Every page now loads `assets/app.js` via `<script src="assets/app.js">`
  in place of its old inline block. Bootstrap's own CDN `<script>` tags
  were left untouched.

## File-by-file diff summary

| Page | Before | After | Extracted to |
|---|---|---|---|
| `add.php` | 159 lines | 78 lines | `functions/add.php` |
| `admin.php` | 178 lines | 120 lines | `functions/admin.php` |
| `edit.php` | 97 lines | 58 lines | `functions/edit.php` |
| `index.php` | 43 lines | 37 lines | `functions/index.php` |
| `login.php` | 109 lines | 57 lines | `functions/login.php` |
| `register.php` | 120 lines | 70 lines | `functions/register.php` |
| `report.php` | 208 lines | 146 lines | `functions/report.php` |
| `student.php` | 395 lines | 170 lines | `functions/student.php` |

## Bugs fixed along the way

- `functions/add.php` (which you had already started) required
  `__DIR__ . '/includes/db.php'` — wrong, since `functions/` is a
  subdirectory; `includes/` lives one level up. Fixed to
  `__DIR__ . '/../includes/db.php'`. It also called `session_start()`,
  which would have double-started the session once `add.php` called it
  too; removed from the functions file, kept only in the page.

## Deliberately left alone

- `message.php` — a shared HTML partial (`include('message.php')`), not
  business logic, so it stayed at the root next to the pages that use it.
- `logout.php` — has no extractable logic worth a functions file.
- `includes/db.php`, `config.php` — untouched.
- Root `app.js` / `style.css` — these were already deleted from the
  working tree before this session (superseded by the `assets/` copies
  you'd created); this pass just finished wiring references to point at
  the new location.

## Known pre-existing issue (not caused by this reorg)

While verifying against the live XAMPP server, every page that touches
`includes/db.php` fails with:

```
Fatal error: Uncaught mysqli_sql_exception: Access denied for user
'root'@'localhost' (using password: NO) in includes/db.php:5
```

This reproduces identically on the original, un-refactored code (verified
via `git stash`), so it's a local MySQL credential mismatch in
`config.php` (`DB_PASS` is empty, but the local MySQL instance has a root
password set) — unrelated to this cleanup. Let me know if you'd like help
fixing it.
