<?php
require_once __DIR__ . '/../includes/db.php';

if (isset($_POST["save_user"])) {
    $fullName = $_POST["full_name"];
    $gender = $_POST["gender"];
    $section = $_POST["section"];
    $errors = array();

    // Validation
    if (trim($fullName) === '') {
        $errors[] = 'Full name is required.';
    }
    $allowedGenders = ['Male', 'Female'];
    if (!in_array($gender, $allowedGenders, true)) {
        $errors[] = 'Please select a valid gender.';
    }
    $allowedSections = ['Gumamela', 'Tulip'];
    if (!in_array($section, $allowedSections, true)) {
        $errors[] = 'Please select a valid section.';
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    } else {
        //insert the data into database
        $sql = "INSERT INTO student (full_name, gender, section) VALUES ( ?, ?, ? )";
        $stmt = mysqli_stmt_init($conn);
        $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
        if ($prepareStmt) {
            mysqli_stmt_bind_param($stmt, "sss", $fullName, $gender, $section);
            mysqli_stmt_execute($stmt);

            echo "<div class='alert alert-success' role='alert'> Added Successfully! </div>";
        } else {
            echo "<div class='alert alert-danger' role='alert'>
                    Not Created!
                    </div>";
        }
    }
}
