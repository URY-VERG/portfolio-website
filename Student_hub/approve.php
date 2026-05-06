<?php
include "php/session.php";   // 🔥 MUST
include "php/db.php";

// 🔐 Only staff allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    echo "Unauthorized";
    exit();
}

$id = intval($_POST['id']);   // 🔥 use POST (correct)

// Update
$conn->query("UPDATE leave_applications SET status='Approved' WHERE id=$id");

echo "Approved";   // 🔥 IMPORTANT for fetch
?>