<?php
include __DIR__ . "/php/db.php";

function ensureColumnExists($conn, $table, $column, $definition) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if($check && $check->num_rows === 0){
        $conn->query("ALTER TABLE `$table` ADD COLUMN $column $definition");
    }
}

ensureColumnExists($conn, 'students', 'profile_image', 'VARCHAR(255) DEFAULT NULL');

$msg = "";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if($name=="" || $email=="" || $password==""){
        $msg = "All fields required!";
    } else {
        $check = $conn->query("SELECT * FROM students WHERE email='$email'");

        if($check->num_rows > 0){
            $msg = "Email already registered!";
        } else {
            $image = null;
            if(isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])){
                $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                if(in_array($ext, ['jpg','jpeg','png','webp'], true)){
                    $image = "student_" . time() . "_" . rand(100,999) . "." . $ext;
                    move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/" . $image);
                }
            }

            $imgSql = $image ? ("'" . $conn->real_escape_string($image) . "'") : "NULL";
            $conn->query("INSERT INTO students (name,email,password,profile_image) VALUES ('$name','$email','$password',$imgSql)");
            $msg = "✅ Registered successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>

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
.register-box {
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
.register-box h2 {
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

/* FOCUS EFFECT */
.input-box input:focus {
    border-color: #00c9a7;
    box-shadow: 0 0 5px rgba(0,201,167,0.4);
    outline: none;
}

/* ICON */
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
a {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: #555;
    text-decoration: none;
}

a:hover {
    color: #00b894;
}
</style>

</head>

<body>

<div class="register-box">

<h2>✨ Create Account</h2>

<p class="msg"><?php echo $msg; ?></p>

<form method="POST" enctype="multipart/form-data" autocomplete="off">

<div class="input-box">
<input type="text" name="name" placeholder="👤 Full Name" required>
</div>

<div class="input-box">
<input type="email" name="email" placeholder="📧 Email" required>
</div>

<div class="input-box">
<input type="password" id="password" name="password" placeholder="🔒 Password" required>
<span class="toggle" onclick="togglePassword('password')">👁</span>
</div>

<div class="input-box">
<input type="password" id="confirm_password" placeholder="🔒 Confirm Password" required>
<span class="toggle" onclick="togglePassword('confirm_password')">👁</span>
</div>

<div class="input-box">
<input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp">
</div>

<button name="register">Register</button>

</form>

<a href="login.php">Already have account? Login</a>

</div>

<script>
function togglePassword(id){
    let input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

document.querySelector("form").addEventListener("submit", function(e){
    let pass = document.getElementById("password").value;
    let confirm = document.getElementById("confirm_password").value;

    if(pass !== confirm){
        alert("Passwords do not match!");
        e.preventDefault();
    }
});
</script>

</body>
</html>