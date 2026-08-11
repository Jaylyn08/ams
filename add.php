<?php
session_start();
require_once __DIR__ . '/includes/db.php';

//To do
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">

        <?php include('message.php'); ?>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4> Add Student
                            <a href="student.php" class="btn btn-secondary float-end">Back</a>
                        </h4>

                    </div>
                    <div class="card-body">

                        <form id="addForm" action="add.php" method="post">

                            <div id="clientErrors"></div>

                            <div class="col-md-7 mb-3">
                                <label class="form-label"> Full Name </label>
                                <input type="text" id="full_name" name="full_name" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <select id="gender" class="form-select" name="gender" aria-label="Default select example">
                                    <option selected disabled value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select id="section" class="form-select" name="section" aria-label="Default select example">
                                    <option selected disabled value="">Select Section</option>
                                    <option value="Gumamela">Gumamela</option>
                                    <option value="Tulip">Tulip</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <button type="submit" name="save_user" class="btn btn-primary">Save</button>

                            </div>

                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>




    <!-- Option 1:Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        (function() {
            var form = document.getElementById('addForm');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                var errors = [];
                var full = document.getElementById('full_name');
                var gender = document.getElementById('gender');
                var section = document.getElementById('section');
                var allowedGenders = ['Male', 'Female'];
                var allowedSections = ['Gumamela', 'Tulip'];

                if (!full || full.value.trim() === '') {
                    errors.push('Full name is required.');
                }
                if (!gender || allowedGenders.indexOf(gender.value) === -1) {
                    errors.push('Please select a valid gender.');
                }
                if (!section || allowedSections.indexOf(section.value) === -1) {
                    errors.push('Please select a valid section.');
                }

                var container = document.getElementById('clientErrors');
                container.innerHTML = '';
                if (errors.length > 0) {
                    e.preventDefault();
                    var html = '<div class="alert alert-danger"><ul>';
                    errors.forEach(function(msg) {
                        html += '<li>' + msg + '</li>';
                    });
                    html += '</ul></div>';
                    container.innerHTML = html;
                    if (full && full.value.trim() === '') full.focus();
                    return false;
                }
                return true;
            });
        })();
    </script>
</body>

</html>