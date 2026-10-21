<?php
session_start();
require __DIR__ . '/database.php';

// Escape user-provided text before displaying it in HTML.
function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Each form includes a token that belongs to this browser session.
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$sql = 'SELECT students.*, courses.course_name
        FROM students
        JOIN courses ON students.course_id = courses.id
        ORDER BY students.id DESC';
$studentStatement = $pdo->query($sql);
$students = $studentStatement->fetchAll();
$title = 'Students';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>
<?php echo escapeHtml($title); ?> · Student Records</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <nav class="navbar navbar-dark bg-dark mb-4" aria-label="Main navigation">
                <div class="container">
                    <a class="navbar-brand" href="index.php">Student Records</a>
                </div>
            </nav>
            <main class="container pb-5">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h2 mb-0">Students</h1>
    <a class="btn btn-primary" href="create.php">Add student</a>
</div>
<?php if (isset($_SESSION['message'])) { ?>
<div class="alert alert-success" role="status">
<?php echo escapeHtml($_SESSION['message']); ?>
</div>
<?php
    unset($_SESSION['message']);
}
?>
<?php if (count($students) === 0) { ?>
<div class="border rounded p-4 text-center">
    <h2>No students yet</h2>
    <p>Add your first student to start building your directory.</p>
    <a class="btn btn-primary" href="create.php">Add student</a>
</div>
<?php } else { ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Student name</th>
                <th scope="col">Email address</th>
                <th scope="col">Course</th>
                <th scope="col">Year level</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student) { ?>
            <tr>
                <td class="text-secondary">#<?php echo escapeHtml($student['id']); ?>
                </td>
                <td class="fw-medium">
                    <?php echo escapeHtml($student['name']); ?>
                </td>
                <td>
                    <?php echo escapeHtml($student['email']); ?>
                </td>
                <td>
                    <span>
                        <?php echo escapeHtml($student['course_name']); ?>
                    </span>
                </td>
                <td>Year <?php echo escapeHtml($student['year_level']); ?>
                </td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-primary me-1" href="edit.php?id=<?php echo escapeHtml($student['id']); ?>">Edit<span class="visually-hidden"> <?php echo escapeHtml($student['name']); ?>
                        </span>
                    </a>
                    <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo escapeHtml($student['id']); ?>">Delete<span class="visually-hidden"> <?php echo escapeHtml($student['name']); ?>
                        </span>
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>
</main>
</body>
</html>

