<?php
include "php/session.php";   // 🔥 MUST ADD
include "php/db.php";

// 🔐 Only staff can delete
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    echo "Unauthorized";
    exit();
}

$id = intval($_GET['id']);   // 🔥 safety

$conn->query("DELETE FROM notices WHERE id=$id");

echo "Deleted";
?>