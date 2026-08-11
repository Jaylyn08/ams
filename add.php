<?php
// "Add Student" page. auth.php requires a logged-in user; functions/add.php
// does the actual validation + INSERT and exposes $sections/$grades for the
// dropdowns below (and echoes error/success alerts inline when the form is
// submitted back to this same page).
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/functions/add.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="assets/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="app-content">
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
                            <!-- Options come from the `section` table (includes/sections.php) -->
                            <div class="col-md-4 mb-3">
                                <select id="section" class="form-select" name="section_id" aria-label="Default select example">
                                    <option selected disabled value="">Select Section</option>
                                    <?php foreach ($sections as $sec): ?>
                                        <option value="<?= (int) $sec['id'] ?>"><?= htmlspecialchars($sec['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Options come from the `grade` table (includes/grades.php) -->
                            <div class="col-md-4 mb-3">
                                <select id="grade" class="form-select" name="grade_id" aria-label="Default select example">
                                    <option selected disabled value="">Select Grade</option>
                                    <?php foreach ($grades as $gr): ?>
                                        <option value="<?= (int) $gr['id'] ?>"><?= htmlspecialchars($gr['name']) ?></option>
                                    <?php endforeach; ?>
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
    <script src="assets/app.js"></script>
</body>

</html>