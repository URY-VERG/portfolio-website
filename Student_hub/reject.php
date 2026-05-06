<?php
include "php/session.php";   // 🔥 MUST
include "php/db.php";

// 🔐 Only staff allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    echo "Unauthorized";
    exit();
}

$id = intval($_POST['id']);
$reason = $conn->real_escape_string($_POST['reason']);

// Check current status
$check = $conn->query("SELECT status FROM leave_applications WHERE id=$id");
$row = $check->fetch_assoc();

if(strtolower($row['status']) == "pending"){

    $conn->query("UPDATE leave_applications 
    SET status='Rejected', reject_reason='$reason' 
    WHERE id=$id");

    echo "Rejected";

} else {
    echo "Already Processed";
}
?>