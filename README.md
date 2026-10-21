# Student CRUD — SQLite + PDO

An introduction-level student management application based on the lesson, with Bootstrap and the final courses-table challenge implemented.

## Run

Requires PHP 8.1+ with PDO SQLite and mbstring.

1. Run `php setup.php` once to create the tables and sample records.
2. Run `php -S 127.0.0.1:8000`.
3. Open <http://127.0.0.1:8000/index.php>.

Alternatively, start the server and visit `/setup.php` before `/index.php`. Setup preserves existing records. If both tables are empty, it inserts Juan, Maria, Alex, BSIT, and BSCS.

## Project structure

```text
school.db      SQLite database file
setup.php      Creates tables and inserts initial sample data
database.php   Opens the shared PDO connection
index.php      Reads students, newest first
create.php     Validates the form and inserts a student
edit.php       Loads, validates, and updates a student
delete.php     Confirms and deletes a student
styles.css     Sets the form width; Bootstrap handles the UI
```

Each CRUD page directly requires `database.php` and contains its own PHP logic and HTML. The repeated form and layout are intentional so beginners can read each page from top to bottom. `escapeHtml()` is a small local function in each page that safely displays text. The code uses descriptive names, full echo statements, and explicit conditions.

The `students` table contains `id`, `name`, `email`, `year_level`, and `course_id`. The `courses` table contains `id` and `course_name`. Each student belongs to one course. This implements the lesson's final challenge rather than its intermediate `course TEXT` activity.

Writes use prepared statements. Update and delete use `WHERE id = :id`. Validation checks names, emails, year levels 1–4, and existing courses. Delete requires a confirmation form and POST. Session tokens protect form submissions, and successful changes redirect to the list.

Existing records from `storage/school.sqlite` were copied to `school.db` during the revision. The old file is retained as a backup and is no longer used by the app.

Bootstrap CSS loads from jsDelivr and requires internet access. This is a local classroom application without authentication; do not expose the database file publicly when deploying.
