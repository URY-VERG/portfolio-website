<?php
include "php/session.php";
include "php/db.php";

$id = $_GET['id'];

// student → own only
if($_SESSION['role'] == "student"){
    $student_id = $_SESSION['id'];
    $conn->query("DELETE FROM leave_applications 
    WHERE id=$id AND student_id='$student_id'");
}

// staff → any
if($_SESSION['role'] == "staff"){
    $conn->query("DELETE FROM leave_applications WHERE id=$id");
}
?>