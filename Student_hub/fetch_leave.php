<?php
include "php/session.php";
include "php/db.php";

$isApi = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
$student_id = $_SESSION['id'];

$result = $conn->query("SELECT * FROM leave_applications 
WHERE student_id='$student_id' ORDER BY id DESC");

if($isApi){
    $items = [];
    while($row = $result->fetch_assoc()){
        $items[] = [
            'id' => (int)$row['id'],
            'title' => $row['student_name'],
            'subtitle' => $row['reason'],
            'status' => $row['status'],
            'actionLabel' => 'Details'
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($items);
    exit();
}

while($row = $result->fetch_assoc()){
?>

<div class="leave-card">

    <!-- TOP -->
    <div class="leave-top">
        <div>
            <h4>👤 <?php echo $row['student_name']; ?></h4>
            <small>🏫 <?php echo $row['class']; ?></small>
        </div>

        <span class="status 
        <?php 
        if($row['status']=="Approved") echo "approved";
        elseif($row['status']=="Rejected") echo "rejected";
        else echo "pending";
        ?>">
        <?php 
        if($row['status']=="Approved") echo "✔ Approved";
        elseif($row['status']=="Rejected") echo "❌ Rejected";
        else echo "⏳ Pending";
        ?>
        </span>
    </div>

    <!-- DETAILS -->
    <p>📅 <b>Date:</b> <?php echo $row['leave_date']; ?></p>

    <p>📝 <b>Reason:</b> <?php echo $row['reason']; ?></p>

    <?php if($row['status']=="Rejected"){ ?>
        <p class="reject-reason">
            ❌ <b>Reject Reason:</b> <?php echo $row['reject_reason']; ?>
        </p>
    <?php } ?>

    <!-- ACTION -->
    <button class="delete-btn" onclick="deleteLeave(<?php echo $row['id']; ?>)">
        🗑 Delete
    </button>

</div>

<?php } ?>