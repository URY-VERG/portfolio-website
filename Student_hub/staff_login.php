<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . "/php/session.php";
include __DIR__ . "/php/db.php";

$msg = "";
$isApi = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if(isset($_POST['login'])){
    $user = trim($_POST['user']);
    $password = trim($_POST['password']);

    $result = $conn->query("SELECT * FROM staff WHERE username='$user' AND password='$password'");
    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        session_regenerate_id(true);
        $_SESSION['role'] = "staff";
        $_SESSION['id'] = $row['id'];
        $_SESSION['name'] = $row['username'];

        if($isApi){
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'userId' => $row['id'],
                'name' => $row['username']
            ]);
            exit();
        }

        header("Location: staff_dashboard.php");
        exit();
    }

    if($isApi){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Staff login invalid!'
        ]);
        exit();
    }

    $msg = "❌ Staff login invalid!";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Staff Login</title>
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:radial-gradient(circle at top,#2b1055,#120d2e);height:100vh;display:flex;align-items:center;justify-content:center;}
.box{width:360px;background:#fff;padding:30px;border-radius:16px;box-shadow:0 15px 35px rgba(0,0,0,.35)}
h2{text-align:center;margin-top:0;color:#2b1055}
.inp{margin-bottom:14px}
.inp input{width:100%;padding:12px;border-radius:8px;border:1px solid #cfd8dc;box-sizing:border-box}
button{width:100%;padding:12px;border:0;border-radius:8px;background:linear-gradient(135deg,#7c4dff,#512da8);color:#fff;font-weight:600;cursor:pointer}
.msg{text-align:center;min-height:20px;color:#d63031}
.links{text-align:center;margin-top:12px}
.links a{text-decoration:none;color:#4a148c}
</style>
</head>
<body>
<div class="box">
    <h2>🛡 Staff Login</h2>
    <p class="msg"><?php echo $msg; ?></p>
    <form method="POST" autocomplete="off">
        <div class="inp"><input type="text" name="user" placeholder="Username" required></div>
        <div class="inp"><input type="password" name="password" placeholder="Password" required></div>
        <button name="login">Login to Staff Panel</button>
    </form>
    <div class="links">
        <a href="login.php">Student Login</a>
    </div>
</div>
</body>
</html>
