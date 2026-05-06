<?php
include "php/session.php";
include "php/db.php";

header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== "student"){
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = (int)$_SESSION['id'];
$student_name = $conn->real_escape_string(trim($_POST['student_name'] ?? ''));
$class = $conn->real_escape_string(trim($_POST['class'] ?? ''));
$leave_date = $conn->real_escape_string(trim($_POST['leave_date'] ?? ''));
$reason = $conn->real_escape_string(trim($_POST['reason'] ?? ''));

if($student_name === '' || $class === '' || $leave_date === '' || $reason === ''){
    echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
    exit();
}

$conn->query("INSERT INTO leave_applications (student_id, student_name, class, leave_date, reason) VALUES ($student_id, '$student_name', '$class', '$leave_date', '$reason')");

if($conn->affected_rows > 0){
    echo json_encode(['success' => true, 'message' => 'Leave application submitted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to submit leave request']);
}
