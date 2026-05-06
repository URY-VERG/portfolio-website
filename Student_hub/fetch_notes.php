<?php
include "php/session.php";
include "php/db.php";

if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['student','staff'], true)){
    exit();
}

$isApi = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

$notes = $conn->query("SELECT * FROM notes ORDER BY id DESC");

if($isApi){
    $data = [];
    while($n = $notes->fetch_assoc()){
        $data[] = [
            'id' => (int)$n['id'],
            'title' => $n['title'],
            'subtitle' => 'Tap to view or download',
            'status' => '',
            'actionLabel' => 'Open',
            'link' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/uploads/' . $n['file_path']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

while($n = $notes->fetch_assoc()){
?>
<div class="notice-card">
    <h4>📌 <?php echo htmlspecialchars($n['title']); ?></h4>
    <p class="notice-content">File Available</p>
    <small class="notice-date">🕒 Available now</small>
</div>
<?php } ?>
