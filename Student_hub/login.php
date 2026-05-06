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

    $result = $conn->query("SELECT * FROM students WHERE email='$user' AND password='$password'");

    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();

        session_regenerate_id(true);

        $_SESSION['role'] = "student";
        $_SESSION['id'] = $row['id'];
        $_SESSION['name'] = $row['name'];

        if($isApi){
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'userId' => $row['id'],
                'name' => $row['name']
            ]);
            exit();
        }

        header("Location: dashboard.php");
        exit();
    }

    if($isApi){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Student login invalid!'
        ]);
        exit();
    }

    $msg = "❌ Student login invalid!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* CARD */
.login-box {
    background: white;
    padding: 35px;
    border-radius: 16px;
    width: 340px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.6s ease;
}

/* ANIMATION */
@keyframes fadeIn {
    from {opacity:0; transform: translateY(20px);}
    to {opacity:1; transform: translateY(0);}
}

/* TITLE */
.login-box h2 {
    text-align: center;
    margin-bottom: 25px;
}

/* INPUT */
.input-box {
    position: relative;
    margin-bottom: 18px;
}

.input-box input {
    width: 88%;
    padding: 12px;
    padding-right: 40px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
    transition: 0.3s;
}

/* FOCUS */
.input-box input:focus {
    border-color: #00c9a7;
    box-shadow: 0 0 5px rgba(0,201,167,0.4);
    outline: none;
}

/* EYE ICON */
.toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #00c9a7, #00b894);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* MESSAGE */
.msg {
    text-align: center;
    margin-bottom: 10px;
    color: red;
}

/* LINK */
.links {
    text-align: center;
    margin-top: 12px;
}

.links a {
    color: #555;
    text-decoration: none;
}

.links a:hover {
    color: #00b894;
}
</style>

</head>

<body>

<div class="login-box">

<h2>🎓 Student Login</h2>

<p class="msg"><?php echo $msg; ?></p>

<form method="POST" autocomplete="off">

<div class="input-box">
<input type="text" name="user" placeholder="📧 Student Email" required>
</div>

<div class="input-box">
<input type="password" id="password" name="password" placeholder="🔒 Password" required>
<span class="toggle" onclick="togglePassword()">👁</span>
</div>

<button name="login">Login</button>

</form>

<div class="links">
    <a href="register.php">Create Student Account</a>
    <br>
    <a href="staff_login.php">Staff Login</a>
</div>

</div>

<script>
function togglePassword(){
    let input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>