<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sections.php';

$sections = getSections($conn);
$sectionNames = array_column($sections, 'name');

$filterDate = $_GET['date'] ?? date('Y-m-d');
$filterStatus = $_GET['status'] ?? '';
$filterSection = $_GET['section'] ?? '';
$filterDate = mysqli_real_escape_string($conn, $filterDate);
$filterStatus = in_array($filterStatus, ['Present', 'Absent'], true) ? $filterStatus : '';
$filterSection = in_array($filterSection, $sectionNames, true) ? $filterSection : '';

$where = ["a.attendance_date = '{$filterDate}'"];
if ($filterStatus !== '') {
    $where[] = "a.status = '{$filterStatus}'";
}
if ($filterSection !== '') {
    $where[] = "sec.name = '{$filterSection}'";
}

$reportQuery = "SELECT s.id, s.full_name, s.gender, sec.name AS section, a.status, a.attendance_date
FROM student s
JOIN section sec ON sec.id = s.section_id
LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = '{$filterDate}'";
if (!empty($where)) {
    $reportQuery .= ' WHERE ' . implode(' AND ', $where);
}
$reportQuery .= ' ORDER BY sec.name, s.full_name';

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
$sectionSummaryQuery = "SELECT sec.name AS section,
    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
    FROM student s
    JOIN section sec ON sec.id = s.section_id
    LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = '{$filterDate}'
    GROUP BY sec.name";
$sectionSummaryResult = mysqli_query($conn, $sectionSummaryQuery);
$sectionSummary = [];
foreach ($sectionNames as $sectionName) {
    $sectionSummary[$sectionName] = ['present' => 0, 'absent' => 0];
}
if ($sectionSummaryResult) {
    while ($r = mysqli_fetch_assoc($sectionSummaryResult)) {
        $sec = $r['section'];
        if (isset($sectionSummary[$sec])) {
            $sectionSummary[$sec]['present'] = (int)$r['present_count'];
            $sectionSummary[$sec]['absent'] = (int)$r['absent_count'];
        }
    }
}
