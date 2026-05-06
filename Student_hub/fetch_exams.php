<?php
include "php/session.php";
include "php/db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== "student"){
    exit();
}

header('Content-Type: application/json');

$result = $conn->query("SELECT id, title, description, total_questions FROM exams ORDER BY id DESC");
$data = [];
while($row = $result->fetch_assoc()){
    $data[] = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'subtitle' => $row['description'] ?: 'No description available',
        'status' => (int)$row['total_questions'] > 0 ? "${row['total_questions']} questions" : "No questions",
        'actionLabel' => 'Attempt'
    ];
}

echo json_encode($data);
