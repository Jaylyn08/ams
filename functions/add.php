<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';
require_once __DIR__ . '/../includes/grades.php';

$sections = getSections($conn);
$grades = getGrades($conn);

if (isset($_POST["save_user"])) {
    $fullName = $_POST["full_name"];
    $gender = $_POST["gender"];
    $sectionId = isset($_POST["section_id"]) ? intval($_POST["section_id"]) : 0;
    $gradeId = isset($_POST["grade_id"]) ? intval($_POST["grade_id"]) : 0;
    $errors = array();

    // Validation
    if (trim($fullName) === '') {
        $errors[] = 'Full name is required.';
    }
    $allowedGenders = ['Male', 'Female'];
    if (!in_array($gender, $allowedGenders, true)) {
        $errors[] = 'Please select a valid gender.';
    }
    $validSectionIds = array_map('intval', array_column($sections, 'id'));
    if (!in_array($sectionId, $validSectionIds, true)) {
        $errors[] = 'Please select a valid section.';
    }
    $validGradeIds = array_map('intval', array_column($grades, 'id'));
    if (!in_array($gradeId, $validGradeIds, true)) {
        $errors[] = 'Please select a valid grade.';
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    } else {
        //insert the data into database
        $sql = "INSERT INTO student (full_name, gender, section_id, grade_id) VALUES ( ?, ?, ?, ? )";
        $stmt = mysqli_stmt_init($conn);
        $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
        if ($prepareStmt) {
            mysqli_stmt_bind_param($stmt, "ssii", $fullName, $gender, $sectionId, $gradeId);
            mysqli_stmt_execute($stmt);

            echo "<div class='alert alert-success' role='alert'> Added Successfully! </div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>
                    Not Created!
                    </div>";
        }
    }
}
