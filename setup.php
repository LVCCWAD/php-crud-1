<?php
// Run this file once before opening index.php.
require __DIR__ . '/database.php';

try {
    $pdo->beginTransaction();

    $sql = 'CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_name TEXT NOT NULL
            )';
    $pdo->exec($sql);

    $sql = 'CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                year_level INTEGER NOT NULL CHECK (year_level BETWEEN 1 AND 4),
                course_id INTEGER NOT NULL REFERENCES courses(id)
            )';
    $pdo->exec($sql);

    // Add the lesson examples only when both tables are empty.
    $courseStatement = $pdo->query('SELECT COUNT(*) FROM courses');
    $courseCount = (int) $courseStatement->fetchColumn();
    $studentStatement = $pdo->query('SELECT COUNT(*) FROM students');
    $studentCount = (int) $studentStatement->fetchColumn();

    if ($courseCount === 0 && $studentCount === 0) {
        $sql = 'INSERT INTO courses (course_name) VALUES (:course_name)';
        $statement = $pdo->prepare($sql);
        $statement->execute(['course_name' => 'BSIT']);
        $informationTechnologyCourseId = $pdo->lastInsertId();
        $statement->execute(['course_name' => 'BSCS']);
        $computerScienceCourseId = $pdo->lastInsertId();

        $sql = 'INSERT INTO students (name, email, year_level, course_id)
                VALUES (:name, :email, :year_level, :course_id)';
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'name' => 'Juan',
            'email' => 'juan@email.com',
            'year_level' => 2,
            'course_id' => $informationTechnologyCourseId
        ]);
        $statement->execute([
            'name' => 'Maria',
            'email' => 'maria@email.com',
            'year_level' => 1,
            'course_id' => $computerScienceCourseId
        ]);
        $statement->execute([
            'name' => 'Alex',
            'email' => 'alex@email.com',
            'year_level' => 3,
            'course_id' => $informationTechnologyCourseId
        ]);
    }

    $pdo->commit();
    echo 'Database setup complete. Existing records have been preserved.';
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Database setup failed. Check the PHP error log for details.');
}
