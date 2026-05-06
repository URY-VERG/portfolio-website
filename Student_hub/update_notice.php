<?php
include "php/session.php";   // 🔥 ADD THIS
include "php/db.php";

// 🔐 Security check (optional but recommended)
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    echo "Unauthorized";
    exit();
}

$id = $_POST['id'];
$title = $_POST['title'];
$content = $_POST['content'];

// 🔥 basic protection
$id = intval($id);
$title = $conn->real_escape_string($title);
$content = $conn->real_escape_string($content);

$conn->query("UPDATE notices SET title='$title', content='$content' WHERE id=$id");

echo "Updated";
?>