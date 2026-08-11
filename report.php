<?php
require_once __DIR__ . '/includes/db.php';
session_start();

$filterDate = $_GET['date'] ?? date('Y-m-d');
$filterStatus = $_GET['status'] ?? '';
$filterSection = $_GET['section'] ?? '';
$filterDate = mysqli_real_escape_string($conn, $filterDate);
$filterStatus = in_array($filterStatus, ['Present', 'Absent'], true) ? $filterStatus : '';
$filterSection = in_array($filterSection, ['Gumamela', 'Tulip'], true) ? $filterSection : '';

$where = ["a.attendance_date = '{$filterDate}'"];
if ($filterStatus !== '') {
    $where[] = "a.status = '{$filterStatus}'";
}
if ($filterSection !== '') {
    $where[] = "s.section = '{$filterSection}'";
}

$reportQuery = "SELECT s.id, s.full_name, s.gender, s.section, a.status, a.attendance_date
FROM student s
LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = '{$filterDate}'";
if (!empty($where)) {
    $reportQuery .= ' WHERE ' . implode(' AND ', $where);
}
$reportQuery .= ' ORDER BY s.section, s.full_name';

$reportResult = mysqli_query($conn, $reportQuery);

$summaryQuery = "SELECT status, COUNT(*) AS total FROM attendance WHERE attendance_date = '{$filterDate}'";
if ($filterStatus !== '') {
    $summaryQuery .= " AND status = '{$filterStatus}'";
}
$summaryQuery .= ' GROUP BY status';
$summaryResult = mysqli_query($conn, $summaryQuery);

$counts = ['Present' => 0, 'Absent' => 0];
if ($summaryResult) {
    while ($row = mysqli_fetch_assoc($summaryResult)) {
        $counts[$row['status']] = $row['total'];
    }
}

$absentByDateQuery = "SELECT attendance_date, COUNT(*) AS absent_count FROM attendance WHERE status = 'Absent' GROUP BY attendance_date ORDER BY attendance_date DESC LIMIT 10";
$absentByDateResult = mysqli_query($conn, $absentByDateQuery);

// Per-section summary for present/absent on the selected date
$sectionSummaryQuery = "SELECT s.section,
    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
    FROM student s
    LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = '{$filterDate}'
    GROUP BY s.section";
$sectionSummaryResult = mysqli_query($conn, $sectionSummaryQuery);
$sectionSummary = ['Gumamela' => ['present' => 0, 'absent' => 0], 'Tulip' => ['present' => 0, 'absent' => 0]];
if ($sectionSummaryResult) {
    while ($r = mysqli_fetch_assoc($sectionSummaryResult)) {
        $sec = $r['section'];
        if (isset($sectionSummary[$sec])) {
            $sectionSummary[$sec]['present'] = (int)$r['present_count'];
            $sectionSummary[$sec]['absent'] = (int)$r['absent_count'];
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Attendance Report</title>
</head>

<body>
    <div class="topnav">
        <a href="index.php">Home</a>
        <a href="student.php">Student</a>
        <a class="active" href="report.php">Report</a>

    </div>

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="mb-1">Attendance Report</h4>
                    <p class="text-muted mb-0">Showing attendance for <strong><?= htmlspecialchars($filterDate) ?></strong></p>
                </div>

                <form class="row g-2 align-items-end" method="get">
                    <div class="col-auto">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
                    </div>
                    <div class="col-auto">
                        <label for="section" class="form-label">Section</label>
                        <select id="section" name="section" class="form-select">
                            <option value="" <?= $filterSection === '' ? ' selected' : '' ?>>All</option>
                            <option value="Gumamela" <?= $filterSection === 'Gumamela' ? ' selected' : '' ?>>Gumamela</option>
                            <option value="Tulip" <?= $filterSection === 'Tulip' ? ' selected' : '' ?>>Tulip</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>

            <div class="card-body">

                <div class="row mb-3 g-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="p-3 rounded-3 bg-light border   ">
                            <div>
                                <h6 class="text-uppercase text-secondary display-6"><strong>Gumamela</strong></h6>
                                <p class="mb-0">Present: <strong><?= $sectionSummary['Gumamela']['present'] ?></strong></p>
                                <p class="mb-0 ">Absent: <strong><?= $sectionSummary['Gumamela']['absent'] ?></strong></p>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-md-left">
                                <h6 class="text-uppercase text-secondary display-6"><strong>Tulip</strong></h6>
                                <p class="mb-0">Present: <strong><?= $sectionSummary['Tulip']['present'] ?></strong></p>
                                <p class="mb-0">Absent: <strong><?= $sectionSummary['Tulip']['absent'] ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Gender</th>
                                <th>Section</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reportResult && mysqli_num_rows($reportResult) > 0): ?>
                                <?php $index = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($reportResult)): ?>
                                    <?php $rowClass = ($row['status'] === 'Absent') ? 'table-danger' : ''; ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td><?= $index++ ?></td>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td><?= htmlspecialchars($row['gender']) ?></td>
                                        <td><?= htmlspecialchars($row['section']) ?></td>
                                        <td><?= htmlspecialchars($row['status'] ?: 'No mark') ?></td>
                                        <td><?= htmlspecialchars($row['attendance_date'] ?: $filterDate) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No attendance records found for this date.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>



                <h5>Absent count by date</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Absent Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($absentByDateResult && mysqli_num_rows($absentByDateResult) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($absentByDateResult)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['attendance_date']) ?></td>
                                        <td><?= htmlspecialchars($row['absent_count']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-3">No absence history available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>