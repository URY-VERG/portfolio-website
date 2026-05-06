<?php
include __DIR__ . "/php/session.php";
include __DIR__ . "/php/db.php";

// 🔐 Only staff allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    echo "Unauthorized";
    exit();
}

// 🔥 Get ID
$id = intval($_GET['id']);

// 🔥 Delete query
$conn->query("DELETE FROM notes WHERE id=$id");

// 🔥 Response for fetch
echo "Deleted";
?>