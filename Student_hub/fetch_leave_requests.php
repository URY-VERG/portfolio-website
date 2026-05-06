<?php
include "php/session.php";
include "php/db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== "staff"){
    exit();
}

header('Content-Type: application/json');

$result = $conn->query("SELECT id, student_name, class, leave_date, reason, status FROM leave_applications ORDER BY id DESC");
$data = [];
while($row = $result->fetch_assoc()){
    $status = $row['status'];
    $label = 'View';
    if($status === 'Pending') {
        $label = 'Review';
    }
    $data[] = [
        'id' => (int)$row['id'],
        'title' => $row['student_name'],
        'subtitle' => $row['class'] . ' • ' . $row['leave_date'],
        'status' => $status,
        'actionLabel' => $label
    ];
}

echo json_encode($data);
