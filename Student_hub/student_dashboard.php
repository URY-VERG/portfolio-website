<?php
include "php/session.php";

// 🔐 Security check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: login.php");
    exit();
}

include "php/db.php";

// 🔥 HANDLE LEAVE APPLY (POST → REDIRECT)
if(isset($_POST['apply'])){
    $reason = $_POST['reason'];
    $student_id = $_SESSION['id'];

    $conn->query("INSERT INTO leave_applications (student_id, reason) VALUES ('$student_id','$reason')");

    header("Location: student_dashboard.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<!-- 🔴 Logout Button -->
<div style="text-align:right;">
    <a href="logout.php" 
    style="padding:8px 15px; background:red; color:white; text-decoration:none;">
        Logout
    </a>
</div>

<h2>Student Dashboard</h2>

<!-- ✅ Success Message -->
<?php if(isset($_GET['success'])){ ?>
    <p style="color:green;">Leave Applied Successfully!</p>
<?php } ?>

<!-- 📚 NOTES -->
<h3>Notes</h3>
<?php
$result = $conn->query("SELECT * FROM notes");

while($row = $result->fetch_assoc()){
?>
    <p>
        <?php echo $row['title']; ?> 

        <!-- 🔥 VIEW -->
        <a href="uploads/<?php echo $row['file_path']; ?>" target="_blank">
            View
        </a>

        <!-- 🔥 DOWNLOAD -->
        <a href="uploads/<?php echo $row['file_path']; ?>" download>
            Download
        </a>
    </p>
<?php } ?>
<!-- 📝 LEAVE APPLY -->
<h3>Apply Leave</h3>

<form method="POST">
    Reason: <input type="text" name="reason" required>
    <button name="apply">Apply</button>
</form>

<hr>

<!-- 🔥 UPDATED LEAVE SECTION -->
<h3>Your Leave Applications</h3>

<div id="leave-container">
    <!-- auto load -->
</div>

<hr>

<!-- 🧮 CALCULATOR -->
<h3>Calculator</h3>

<input type="number" id="a">
<input type="number" id="b">
<button onclick="calc()">Add</button>

<p id="res"></p>

<script>
function calc(){
    let a = Number(document.getElementById("a").value);
    let b = Number(document.getElementById("b").value);
    document.getElementById("res").innerText = a + b;
}
</script>

<hr>

<!-- 📊 SGPA TO % -->
<h3>SGPA to Percentage</h3>

<input type="number" id="sgpa" placeholder="Enter SGPA">
<button onclick="convert()">Convert</button>

<p id="per"></p>

<script>
function convert(){
    let sgpa = document.getElementById("sgpa").value;
    let per = sgpa * 10;
    document.getElementById("per").innerText = "Percentage: " + per;
}
</script>

<!-- 🔥 LIVE UPDATE SCRIPT -->
<script>
function loadLeaves(){
    fetch("fetch_leave.php")
    .then(res => res.text())
    .then(data => {
        document.getElementById("leave-container").innerHTML = data;
    });
}

// first load
loadLeaves();

// auto update every 3 sec
setInterval(loadLeaves, 3000);

// 🔥 DELETE FUNCTION
function deleteLeave(id){
    if(confirm("Delete this leave?")){
        fetch("delete_leave.php?id=" + id)
        .then(() => {
            loadLeaves(); // refresh only section
        });
    }
}
</script>

</body>
</html>