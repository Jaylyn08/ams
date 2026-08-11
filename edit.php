<?php
require_once __DIR__ . '/includes/db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $full_name = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $section = $_POST['section'] ?? '';

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE student SET full_name = ?, gender = ?, section = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sssi', $full_name, $gender, $section, $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Student updated successfully.';
                mysqli_stmt_close($stmt);
                header('Location: student.php');
                exit;
            } else {
                $_SESSION['message'] = 'Unable to update student.';
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$student = null;
if ($id > 0) {
    $q = mysqli_prepare($conn, "SELECT id, full_name, gender, section FROM student WHERE id = ? LIMIT 1");
    if ($q) {
        mysqli_stmt_bind_param($q, 'i', $id);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        if ($res && mysqli_num_rows($res) > 0) {
            $student = mysqli_fetch_assoc($res);
        }
        mysqli_stmt_close($q);
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Student</title>
</head>

<body>
    <div class="container mt-4">
        <?php include('message.php'); ?>
        <div class="card col-md-8 mx-auto">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit Student</h4>
                <a href="student.php" class="btn btn-secondary col-md-2">Back</a>
            </div>
            <div class="card-body">
                <?php if ($student): ?>
                    <form action="edit.php" method="post">
                        <input type="hidden" name="id" value="<?= $student['id'] ?>">
                        <div class="mb-1 col-md-8">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                        </div>
                        <div class="mb-1 col-md-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label">Section</label>
                            <select name="section" class="form-select" required>
                                <option value="Gumamela" <?= $student['section'] === 'Gumamela' ? 'selected' : '' ?>>Gumamela</option>
                                <option value="Tulip" <?= $student['section'] === 'Tulip' ? 'selected' : '' ?>>Tulip</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary col-md-3">Save Changes</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Student not found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>