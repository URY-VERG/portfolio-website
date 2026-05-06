<?php
include "php/session.php";
include "php/db.php";

if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['student','staff'], true)){
    exit();
}

$isApi = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

$notices = $conn->query("SELECT * FROM notices ORDER BY id DESC");

if($isApi){
    $data = [];
    while($n = $notices->fetch_assoc()){
        $data[] = [
            'id' => (int)$n['id'],
            'title' => $n['title'],
            'subtitle' => $n['content'],
            'status' => '',
            'actionLabel' => 'Open'
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

while($n = $notices->fetch_assoc()){
?>
<div class="notice-card">
    <h4>📌 <?php echo htmlspecialchars($n['title']); ?></h4>
    <p class="notice-content"><?php echo htmlspecialchars($n['content']); ?></p>
    <small class="notice-date">🕒 <?php echo $n['created_at']; ?></small>
</div>
<?php } ?>
