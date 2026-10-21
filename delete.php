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

// Load the student identified by the URL.
$requestedStudentId = '';
    if (isset($_GET['id'])) {
        $requestedStudentId = $_GET['id'];
    }

    $studentId = filter_var($requestedStudentId, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($studentId === false) {
        http_response_code(404);
        exit('Student not found.');
    }

    $sql = 'SELECT students.*, courses.course_name
            FROM students
            JOIN courses ON courses.id = students.course_id
            WHERE students.id = :id';
    $statement = $pdo->prepare($sql);
    $statement->execute(['id' => $studentId]);
    $student = $statement->fetch();

    if ($student === false) {
        http_response_code(404);
        exit('Student not found.');
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the form token before making changes.
if (!isset($_POST['csrf']) || !is_string($_POST['csrf'])) {
        http_response_code(403);
        exit('Missing form token. Return to the student list and try again.');
    }

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid form token. Return to the student list and try again.');
    }
    $sql = 'DELETE FROM students
                WHERE id = :id';
    $statement = $pdo->prepare($sql);
    $statement->execute(['id' => $student['id']]);
    $_SESSION['message'] = 'Student deleted successfully.';
        header('Location: index.php', true, 303);
        exit;
}
$title = 'Delete student';
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

<a class="d-inline-block mb-3" href="index.php">← Back to students</a>
<h1 class="h2">Delete student?</h1>
<p class="text-secondary mb-4">This permanently removes the student record.</p>
<form method="post" class="student-form border rounded p-4">
    <input type="hidden" name="csrf" value="<?php echo escapeHtml($_SESSION['csrf']); ?>">
    <h2 class="h5">
<?php echo escapeHtml($student['name']); ?>
    </h2>
    <p>
<?php echo escapeHtml($student['email']); ?>
    </p>
    <p>
        <span class="fw-semibold">
<?php echo escapeHtml($student['course_name']); ?>
        </span> · Year <?php echo escapeHtml($student['year_level']); ?>
    </p>
    <div class="d-flex gap-2 mt-4">
        <a class="btn btn-secondary" href="index.php">Keep student</a>
        <button class="btn btn-danger" type="submit">Delete student</button>
    </div>
</form>
</main>
</body>
</html>

