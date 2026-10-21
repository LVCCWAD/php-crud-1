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

$isEditingStudent = true;
$courseStatement = $pdo->query('SELECT * FROM courses ORDER BY course_name');
$courses = $courseStatement->fetchAll();
$studentData = $student;
$validationErrors = [];
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
    // Read the submitted fields. Reject non-text values before trimming.
    $name = '';
    if (isset($_POST['name']) && is_string($_POST['name'])) {
        $name = trim($_POST['name']);
    }
    $email = '';
    if (isset($_POST['email']) && is_string($_POST['email'])) {
        $email = trim($_POST['email']);
    }
    $yearLevel = '';
    if (isset($_POST['year_level']) && is_string($_POST['year_level'])) {
        $yearLevel = trim($_POST['year_level']);
    }
    $courseId = '';
    if (isset($_POST['course_id']) && is_string($_POST['course_id'])) {
        $courseId = trim($_POST['course_id']);
    }

    // Validate the fields before saving.
$studentData = [
        'name' => $name,
        'email' => $email,
        'year_level' => $yearLevel,
        'course_id' => $courseId
    ];
    $validationErrors = [];

    if ($studentData['name'] === '' || mb_strlen($studentData['name']) > 100) {
        $validationErrors['name'] = 'Enter a name with 1–100 characters.';
    }
    if (!filter_var($studentData['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($studentData['email']) > 100) {
        $validationErrors['email'] = 'Enter a valid email of up to 100 characters.';
    }

    $allowedYearLevels = ['1', '2', '3', '4'];
    if (!in_array($studentData['year_level'], $allowedYearLevels, true)) {
        $validationErrors['year_level'] = 'Choose a year level from 1 to 4.';
    }

    $courseExists = false;
    foreach ($courses as $course) {
        if ($studentData['course_id'] === (string) $course['id']) {
            $courseExists = true;
            break;
        }
    }
    if ($courseExists === false) {
        $validationErrors['course_id'] = 'Choose an available course.';
    }


    if (count($validationErrors) === 0) {
        $sql = 'UPDATE students
                SET name = :name, email = :email, year_level = :year_level, course_id = :course_id
                WHERE id = :id';
        $statement = $pdo->prepare($sql);
        $updateParameters = $studentData;
        $updateParameters['id'] = $student['id'];
        $statement->execute($updateParameters);
        $_SESSION['message'] = 'Student updated successfully.';
        header('Location: index.php', true, 303);
        exit;
    }
    http_response_code(422);
}
$title = 'Edit student';
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
<h1 class="h2">Edit student</h1>
<p class="text-secondary mb-4">Update the information for student #<?php echo escapeHtml($student['id']); ?>.</p>
<?php
// Choose Bootstrap field styles before displaying the form.
$nameInputClass = 'form-control mb-3';
if (isset($validationErrors['name'])) {
    $nameInputClass = 'form-control mb-3 is-invalid';
}

$emailInputClass = 'form-control mb-3';
if (isset($validationErrors['email'])) {
    $emailInputClass = 'form-control mb-3 is-invalid';
}

$courseSelectClass = 'form-select mb-3';
if (isset($validationErrors['course_id'])) {
    $courseSelectClass = 'form-select mb-3 is-invalid';
}

$yearLevelSelectClass = 'form-select mb-3';
if (isset($validationErrors['year_level'])) {
    $yearLevelSelectClass = 'form-select mb-3 is-invalid';
}

?>
<form method="post" class="student-form">
    <input type="hidden" name="csrf" value="<?php echo escapeHtml($_SESSION['csrf']); ?>">
<?php if (count($validationErrors) > 0) { ?>
    <div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div>
<?php } ?>
    <label class="form-label" for="name">Student name</label>
    <input class="<?php echo $nameInputClass; ?>" id="name" name="name" maxlength="100" required autocomplete="name" value="<?php echo escapeHtml($studentData['name']); ?>" placeholder="e.g. Juan">
<?php if (isset($validationErrors['name'])) { ?>
    <p class="text-danger small">
<?php echo escapeHtml($validationErrors['name']); ?>
    </p>
<?php } ?>
    <label class="form-label" for="email">Email address</label>
    <input class="<?php echo $emailInputClass; ?>" type="email" id="email" name="email" maxlength="100" required autocomplete="email" value="<?php echo escapeHtml($studentData['email']); ?>" placeholder="e.g. juan@email.com">
<?php if (isset($validationErrors['email'])) { ?>
    <p class="text-danger small">
<?php echo escapeHtml($validationErrors['email']); ?>
    </p>
<?php } ?>
    <div class="row">
        <div class="col-sm-6">
            <label class="form-label" for="course_id">Course</label>
            <select class="<?php echo $courseSelectClass; ?>" id="course_id" name="course_id" required>
                <option value="">Select a course</option>
<?php
foreach ($courses as $course) {
    $selectedCourseAttribute = '';
    if ((string) $studentData['course_id'] === (string) $course['id']) {
        $selectedCourseAttribute = 'selected';
    }
?>
                <option value="<?php echo escapeHtml($course['id']); ?>" <?php echo $selectedCourseAttribute; ?>>
<?php echo escapeHtml($course['course_name']); ?>
                </option>
<?php } ?>
            </select>
<?php if (isset($validationErrors['course_id'])) { ?>
            <p class="text-danger small">
<?php echo escapeHtml($validationErrors['course_id']); ?>
            </p>
<?php } ?>
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="year_level">Year level</label>
            <select class="<?php echo $yearLevelSelectClass; ?>" id="year_level" name="year_level" required>
                <option value="">Select a year</option>
<?php
for ($yearLevel = 1; $yearLevel <= 4; $yearLevel = $yearLevel + 1) {
    $selectedYearLevelAttribute = '';
    if ((string) $studentData['year_level'] === (string) $yearLevel) {
        $selectedYearLevelAttribute = 'selected';
    }
?>
                <option value="<?php echo $yearLevel; ?>" <?php echo $selectedYearLevelAttribute; ?>>Year <?php echo $yearLevel; ?>
                </option>
<?php } ?>
            </select>
<?php if (isset($validationErrors['year_level'])) { ?>
            <p class="text-danger small">
<?php echo escapeHtml($validationErrors['year_level']); ?>
            </p>
<?php } ?>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <a class="btn btn-secondary" href="index.php">Cancel</a>
        <button class="btn btn-primary" type="submit">
<?php
if ($isEditingStudent) {
    echo 'Save changes';
} else {
    echo 'Add student';
}
?>
        </button>
    </div>
</form>

</main>
</body>
</html>

