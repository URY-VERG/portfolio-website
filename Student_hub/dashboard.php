<?php
include "php/session.php";
include "php/db.php";

// 🔐 Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: login.php");
    exit();
}

function ensureColumnExists($conn, $table, $column, $definition) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if($check && $check->num_rows === 0){
        $conn->query("ALTER TABLE `$table` ADD COLUMN $column $definition");
    }
}

// Ensure student profile columns exist
ensureColumnExists($conn, 'students', 'profile_image', 'VARCHAR(255) DEFAULT NULL');
ensureColumnExists($conn, 'students', 'class_name', 'VARCHAR(50) DEFAULT NULL');
ensureColumnExists($conn, 'students', 'mobile', 'VARCHAR(20) DEFAULT NULL');

// Ensure exam tables exist
$conn->query("CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    total_questions INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS exam_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS exam_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$student_id = (int)$_SESSION['id'];
$studentResult = $conn->query("SELECT * FROM students WHERE id=$student_id");
$student = $studentResult ? $studentResult->fetch_assoc() : null;

if(isset($_POST['save_profile'])){
    $name = $conn->real_escape_string(trim($_POST['name']));
    $class_name = $conn->real_escape_string(trim($_POST['class_name']));
    $mobile = $conn->real_escape_string(trim($_POST['mobile']));
    $imageFile = $student['profile_image'] ?? null;

    if(isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])){
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)){
            $imageFile = "profile_" . $student_id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/" . $imageFile);
        }
    }

    $imgSql = $imageFile ? ("'" . $conn->real_escape_string($imageFile) . "'") : "NULL";
    $conn->query("UPDATE students SET name='$name', class_name='$class_name', mobile='$mobile', profile_image=$imgSql WHERE id=$student_id");
    $_SESSION['name'] = $name;

    header("Location: dashboard.php?profile_saved=1");
    exit();
}

// Leave apply
if(isset($_POST['apply'])){
    $student_id = (int)$_SESSION['id'];

    $name = $conn->real_escape_string(trim($_POST['student_name']));
    $class = $conn->real_escape_string(trim($_POST['class']));
    $date = $conn->real_escape_string(trim($_POST['leave_date']));
    $reason = $conn->real_escape_string(trim($_POST['reason']));

    $conn->query("INSERT INTO leave_applications 
    (student_id, student_name, class, leave_date, reason) 
    VALUES ('$student_id','$name','$class','$date','$reason')");
    header("Location: dashboard.php?leave_success=1");
    exit();
}

// Submit exam answers
if(isset($_POST['submit_exam'])){
    $student_id = (int)$_SESSION['id'];
    $exam_id = (int)$_POST['exam_id'];
    $score = 0;
    $total = 0;

    $qResult = $conn->query("SELECT id, correct_option FROM exam_questions WHERE exam_id=$exam_id");
    while($q = $qResult->fetch_assoc()){
        $total++;
        $answerKey = "answer_" . $q['id'];
        $selected = isset($_POST[$answerKey]) ? strtoupper(trim($_POST[$answerKey])) : "";
        if($selected === strtoupper($q['correct_option'])){
            $score++;
        }
    }

    $conn->query("INSERT INTO exam_attempts (exam_id, student_id, score, total) VALUES ($exam_id, $student_id, $score, $total)");
    header("Location: dashboard.php?exam_submitted=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title> Student Dashboard</title>

<style>
    /* HEADER */
.notice-header {
    margin-bottom: 20px;
}

/* LIST */
.notice-list {
    max-height: 400px;
    overflow-y: auto;
}

/* CARD */
.notice-card {
    background: white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.notice-card:hover {
    transform: translateY(-4px);
}

/* TITLE */
.notice-card h4 {
    margin: 0;
    color: #2c3e50;
}

/* CONTENT */
.notice-content {
    margin: 8px 0;
    color: #555;
    font-size: 14px;
}

/* DATE */
.notice-date {
    color: #888;
    font-size: 12px;
}
    /* CARD */
.leave-card {
    background: white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.leave-card:hover {
    transform: translateY(-3px);
}

/* TOP */
.leave-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* STATUS */
.status {
    padding: 5px 12px;
    border-radius: 20px;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.approved { background: #2ecc71; }
.rejected { background: #e74c3c; }
.pending { background: #f39c12; }

/* TEXT */
.leave-card p {
    margin: 6px 0;
    font-size: 14px;
}

/* REJECT */
.reject-reason {
    color: red;
    font-size: 13px;
}

/* DELETE BUTTON */
.delete-btn {
    margin-top: 10px;
    padding: 6px 12px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.delete-btn:hover {
    background: #c0392b;
}
    /* TITLE */
.leave-title {
    margin-top: 20px;
    margin-bottom: 10px;
}

/* LIST BOX */
.leave-list {
    max-height: 350px;
    overflow-y: auto;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 10px;
}

/* SINGLE LEAVE CARD */
.leave-card {
    background: white;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

/* TOP ROW */
.leave-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* STATUS */
.status {
    padding: 4px 10px;
    border-radius: 20px;
    color: white;
    font-size: 12px;
}

.approved { background: #2ecc71; }
.rejected { background: #e74c3c; }
.pending { background: #f39c12; }

/* TEXT */
.leave-card p {
    margin: 5px 0;
    font-size: 14px;
}
    /* FORM BOX */
.leave-form {
    background: white;
    padding: 20px;
    border-radius: 12px;
}

/* ROW */
.form-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

/* INPUT */
.form-row input {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

/* BUTTON */
.apply-btn {
    width: 100%;
    padding: 12px;
    background: #1abc9c;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

    /* HEADER */
.notes-header {
    margin-bottom: 20px;
}

/* LIST SCROLL */
.notes-list {
    max-height: 400px;
    overflow-y: auto;
}

/* CARD */
.note-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin: 10px 0;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.note-card:hover {
    transform: translateY(-4px);
}

/* LEFT */
.note-left h3 {
    margin: 0;
    color: #2c3e50;
}

.note-left p {
    margin: 5px 0 0;
    color: #777;
    font-size: 14px;
}

/* RIGHT */
.note-right {
    display: flex;
    gap: 10px;
}

/* BUTTONS */
.btn {
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 14px;
}

/* COLORS */
.view {
    background: #3498db;
}

.download {
    background: #2ecc71;
}

.btn:hover {
    opacity: 0.85;
}
    /* HEADER */
.home-header {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* CARDS */
.home-cards {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.home-card {
    flex: 1;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    padding: 20px;
    border-radius: 12px;
    transition: 0.3s;
}

.home-card:hover {
    transform: translateY(-5px);
}

/* QUICK ACTION */
.quick-actions {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

.quick-actions button {
    margin: 5px;
    padding: 10px 15px;
    border: none;
    background: #2c3e50;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.quick-actions button:hover {
    background: #1abc9c;
}
    /* SIDEBAR MAIN */
.sidebar {
    width: 240px;
    background: linear-gradient(180deg, #1e272e, #2c3e50);
    color: white;
    height: 100vh;
    padding: 20px 15px;
    box-shadow: 3px 0 10px rgba(0,0,0,0.1);
}

/* LOGO */
.logo {
    text-align: center;
    margin-bottom: 25px;
    font-size: 22px;
    letter-spacing: 1px;
}

/* BUTTONS */
.sidebar button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: none;
    background: transparent;
    color: #ecf0f1;
    text-align: left;
    font-size: 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}

/* HOVER EFFECT */
.sidebar button:hover {
    background: #1abc9c;
    color: white;
    transform: translateX(5px);
}

/* ACTIVE BUTTON (optional future use) */
.sidebar button.active {
    background: #1abc9c;
}
    /* SGPA BOX */
.sgpa-box {
    max-width: 350px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
}

/* INPUT */
.sgpa-box input {
    width: 100%;
    padding: 12px;
    margin: 15px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}

/* BUTTON */
.sgpa-box button {
    width: 100%;
    padding: 12px;
    background: #3498db;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}

.sgpa-box button:hover {
    background: #2980b9;
}

/* RESULT BOX */
#result-box {
    margin-top: 20px;
    padding: 15px;
    border-radius: 10px;
    background: #ecf0f1;
}

/* RESULT TEXT */
#per {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
}
    /* LEAVE CARD */
.leave-card {
    background: white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

/* TOP LINE */
.leave-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* STATUS */
.status {
    padding: 5px 10px;
    border-radius: 20px;
    color: white;
    font-size: 12px;
}

.approved { background: #2ecc71; }
.rejected { background: #e74c3c; }
.pending { background: #f39c12; }

/* REJECT REASON */
.reject-reason {
    color: red;
    font-size: 14px;
}

/* DELETE BUTTON */
.delete-btn {
    margin-top: 10px;
    padding: 6px 12px;
    border: none;
    background: #e74c3c;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.delete-btn:hover {
    background: #c0392b;
}
    /* FORM */
.leave-form {
    max-width: 420px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

/* FORM INPUT */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* BUTTON */
.apply-btn {
    width: 100%;
    padding: 10px;
    background: #1abc9c;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.apply-btn:hover {
    background: #16a085;
}

/* 🔥 SCROLLABLE LEAVE LIST */
.leave-list {
    max-height: 300px;   /* 👈 height limit */
    overflow-y: auto;    /* 👈 scroll enable */
    padding-right: 5px;
}

/* LEAVE CARD */
.leave-card {
    background: white;
    padding: 12px;
    margin: 10px 0;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
    .leave-form {
    max-width: 400px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.apply-btn {
    width: 100%;
    padding: 10px;
    background: #1abc9c;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.apply-btn:hover {
    background: #16a085;
}
    /* NOTES CARD */
.note-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin: 10px 0;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.note-card:hover {
    transform: translateY(-3px);
}

/* LEFT */
.note-left h3 {
    margin: 0;
    color: #2c3e50;
}

.note-left p {
    margin: 5px 0 0;
    color: #777;
    font-size: 14px;
}

/* RIGHT */
.note-right {
    display: flex;
    gap: 10px;
}

/* BUTTONS */
.btn {
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 14px;
}

.view {
    background: #3498db;
}

.download {
    background: #2ecc71;
}

.btn:hover {
    opacity: 0.85;
}

/* GLOBAL */
body {
    margin: 0;
    font-family: 'Segoe UI', Arial;
    background: #f4f6f9;
}

/* LAYOUT */
.container { display: flex; }

/* SIDEBAR */
.sidebar {
    width: 230px;
    background: linear-gradient(180deg, #2c3e50, #1a252f);
    color: white;
    height: 100vh;
    padding: 20px;
}

.sidebar h2 { text-align: center; margin-bottom: 25px; }

.sidebar button {
    width: 100%;
    padding: 12px;
    margin: 6px 0;
    border: none;
    border-radius: 8px;
    background: #34495e;
    color: white;
    cursor: pointer;
    transition: 0.3s;
}

.sidebar button:hover {
    background: #1abc9c;
    transform: translateX(5px);
}

/* CONTENT */
.content { flex: 1; padding: 25px; }

/* TOPBAR */
.topbar { text-align: right; margin-bottom: 15px; }

/* SECTION */
.section { display: none; }

/* HOME */
#home { text-align: center; margin-top: 100px; }

/* CARD */
.card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    margin: 10px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* CALCULATOR */
.calc-box {
    width:100%;
    max-width:420px;
    margin:auto;
    padding:20px;
    border-radius:20px;
    background:#2c3e50;
}

#display {
    width:100%;
    height:70px;
    font-size:30px;
    text-align:right;
    border:none;
    border-radius:10px;
    margin-bottom:15px;
    padding:10px;
    background:#ecf0f1;
}

.calc-buttons {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
}

.calc-buttons button {
    height:65px;
    font-size:20px;
    border:none;
    border-radius:10px;
    background:#34495e;
    color:white;
    cursor:pointer;
}

.calc-buttons button:hover {
    background:#1abc9c;
}

.op { background:#e67e22 !important; }
.equal { background:#1abc9c !important; }

</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2 class="logo">⚡ Dashboard</h2>

    <button onclick="showSection('home')">🏠 Home</button>
    <button onclick="showSection('notes')">📚 Notes</button>
    <button onclick="showSection('leave')">📝 Leave</button>
    <button onclick="showSection('calculator')">🧮 Calculator</button>
    <button onclick="showSection('sgpa')">📊 SGPA</button>
    <button onclick="showSection('notice')">📢 Notices</button>
    <button onclick="showSection('exam')">🧪 Exams</button>
    <button onclick="showSection('profile')">👤 Profile</button>
</div>
<!-- CONTENT -->
<div class="content">

<div class="topbar">
<span style="margin-right:15px;font-weight:bold;">👤 <?php echo htmlspecialchars($_SESSION['name']); ?></span>
<a href="logout.php" style="background:red;color:white;padding:8px 15px;text-decoration:none;">Logout</a>
</div>

<!-- HOME -->
<div id="home" class="section">

    <!-- HEADER -->
    <div class="home-header">
        <h1>🎓 Welcome Back!</h1>
        <p>Manage everything from your dashboard</p>
    </div>

    <!-- STATS / CARDS -->
    <div class="home-cards">

        <div class="home-card">
            <h3>📚 Notes</h3>
            <p>View and download study material</p>
        </div>

        <div class="home-card">
            <h3>📝 Leave</h3>
            <p>Apply and track leave requests</p>
        </div>

        <div class="home-card">
            <h3>📢 Notices</h3>
            <p>Check latest announcements</p>
        </div>

        <div class="home-card">
            <h3>🧪 Exams</h3>
            <p>Attend exams and view your score</p>
        </div>

    </div>

    

</div>
<!-- 🔥 NOTES SECTION -->
<div id="notes" class="section">

    <div class="notes-header">
        <h2>📚 Notes Library</h2>
        <p>Access and download all study materials</p>
    </div>

    <!-- 🔥 NOTES LIST -->
    <div class="notes-list">

    <?php
    $result = $conn->query("SELECT * FROM notes");

    while($row = $result->fetch_assoc()){
    ?>

    <div class="note-card">

        <!-- LEFT -->
        <div class="note-left">
            <h3>📄 <?php echo $row['title']; ?></h3>
            <p>Available for viewing & download</p>
        </div>

        <!-- RIGHT -->
        <div class="note-right">
            <a href="./uploads/<?php echo $row['file_path']; ?>" target="_blank" class="btn view">
                👁 View
            </a>

            <a href="./uploads/<?php echo $row['file_path']; ?>" download class="btn download">
                ⬇ Download
            </a>
        </div>

    </div>

    <?php } ?>

    </div>

</div>
<!-- LEAVE -->
<div id="leave" class="section">
<h2>📝 Apply Leave</h2>
<?php if(isset($_GET['leave_success'])){ ?>
    <p style="color:green;"><b>Leave applied successfully.</b></p>
<?php } ?>

<!-- FORM -->
<form method="POST" class="leave-form">

    <div class="form-row">
        <input type="text" name="student_name" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" placeholder="👤 Name" required>
        <input type="text" name="class" value="<?php echo htmlspecialchars($student['class_name'] ?? ''); ?>" placeholder="🏫 Class" required>
    </div>

    <div class="form-row">
        <input type="date" name="leave_date" required>
        <input type="text" name="reason" placeholder="📝 Reason" required>
    </div>

    <button name="apply" class="apply-btn">🚀 Apply</button>

</form>
<hr>

<h3 class="leave-title">📋 Your Leaves</h3>

<div class="leave-list" id="leave-container">
    <!-- Leaves dynamically load hotil -->
</div>
</div>
<!-- CALCULATOR -->
<div id="calculator" class="section">
<h2>Calculator</h2>

<div class="calc-box">
<input type="text" id="display" readonly>

<div class="calc-buttons">
<button class="op" onclick="clearDisplay()">C</button>
<button class="op" onclick="del()">⌫</button>
<button class="op" onclick="append('%')">%</button>
<button class="op" onclick="append('/')">÷</button>

<button onclick="append('7')">7</button>
<button onclick="append('8')">8</button>
<button onclick="append('9')">9</button>
<button class="op" onclick="append('*')">×</button>

<button onclick="append('4')">4</button>
<button onclick="append('5')">5</button>
<button onclick="append('6')">6</button>
<button class="op" onclick="append('-')">−</button>

<button onclick="append('1')">1</button>
<button onclick="append('2')">2</button>
<button onclick="append('3')">3</button>
<button class="op" onclick="append('+')">+</button>

<button onclick="append('0')" style="grid-column: span 2;">0</button>
<button onclick="append('.')">.</button>
<button class="equal" onclick="calculate()">=</button>
</div>
</div>
</div>

<div id="sgpa" class="section">
<h2 style="text-align:center;">📊 SGPA to Percentage</h2>

<div class="sgpa-box">

    <label>Enter SGPA</label>
    <input type="number" id="sg" step="0.01" placeholder="e.g. 8.5">

    <button onclick="convert()">Convert</button>

    <div id="result-box">
        <p id="per"></p>
    </div>

</div>

</div>
<!-- NOTICE -->
<div id="notice" class="section">

    <!-- HEADER -->
    <div class="notice-header">
        <h2>📢 Notices Board</h2>
        <p>Latest updates and announcements</p>
    </div>

    <!-- 🔥 NOTICE LIST -->
    <div class="notice-list" id="notice-container"></div>

</div>
<!-- EXAM -->
<div id="exam" class="section">
    <div class="notice-header">
        <h2>🧪 Exams</h2>
        <p>Attempt exams created by admin and check your results.</p>
    </div>

    <?php if(isset($_GET['exam_submitted'])){ ?>
        <p style="color:green;"><b>Exam submitted successfully.</b></p>
    <?php } ?>

    <h3>Available Exams</h3>
    <div class="notice-list">
        <?php
        $examList = $conn->query("SELECT * FROM exams ORDER BY id DESC");
        while($exam = $examList->fetch_assoc()){
        ?>
        <div class="notice-card">
            <h4>📝 <?php echo htmlspecialchars($exam['title']); ?></h4>
            <p class="notice-content"><?php echo htmlspecialchars($exam['description']); ?></p>

            <?php
            $qList = $conn->query("SELECT * FROM exam_questions WHERE exam_id=" . (int)$exam['id']);
            if($qList->num_rows === 0){
                echo "<p>No questions available.</p>";
            } else {
            ?>
            <form method="POST">
                <input type="hidden" name="exam_id" value="<?php echo (int)$exam['id']; ?>">
                <?php while($q = $qList->fetch_assoc()){ ?>
                    <div style="margin:12px 0;padding:10px;background:#f8f9fa;border-radius:8px;">
                        <p><b><?php echo htmlspecialchars($q['question_text']); ?></b></p>
                        <label><input type="radio" name="answer_<?php echo (int)$q['id']; ?>" value="A" required> <?php echo htmlspecialchars($q['option_a']); ?></label><br>
                        <label><input type="radio" name="answer_<?php echo (int)$q['id']; ?>" value="B"> <?php echo htmlspecialchars($q['option_b']); ?></label><br>
                        <label><input type="radio" name="answer_<?php echo (int)$q['id']; ?>" value="C"> <?php echo htmlspecialchars($q['option_c']); ?></label><br>
                        <label><input type="radio" name="answer_<?php echo (int)$q['id']; ?>" value="D"> <?php echo htmlspecialchars($q['option_d']); ?></label>
                    </div>
                <?php } ?>
                <button type="submit" name="submit_exam" class="apply-btn">Submit Exam</button>
            </form>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <h3>Your Scores</h3>
    <div class="notice-list">
        <?php
        $sid = (int)$_SESSION['id'];
        $myScores = $conn->query("SELECT ea.score, ea.total, ea.submitted_at, e.title
                                  FROM exam_attempts ea
                                  JOIN exams e ON e.id = ea.exam_id
                                  WHERE ea.student_id=$sid
                                  ORDER BY ea.id DESC");
        while($s = $myScores->fetch_assoc()){
        ?>
        <div class="notice-card">
            <h4><?php echo htmlspecialchars($s['title']); ?></h4>
            <p class="notice-content"><b>Score:</b> <?php echo (int)$s['score']; ?>/<?php echo (int)$s['total']; ?></p>
            <small class="notice-date">🕒 <?php echo $s['submitted_at']; ?></small>
        </div>
        <?php } ?>
    </div>
</div>
<!-- PROFILE -->
<div id="profile" class="section">
    <div class="notice-header">
        <h2>👤 Student Profile</h2>
        <p>Update your details and profile photo.</p>
    </div>
    <?php if(isset($_GET['profile_saved'])){ ?>
        <p style="color:green;"><b>Profile updated successfully.</b></p>
    <?php } ?>
    <form method="POST" enctype="multipart/form-data" class="leave-form" style="max-width:500px;">
        <div style="text-align:center;margin-bottom:15px;">
            <?php if(!empty($student['profile_image'])){ ?>
                <img src="./uploads/<?php echo htmlspecialchars($student['profile_image']); ?>" alt="profile" style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #1abc9c;">
            <?php } else { ?>
                <div style="width:110px;height:110px;border-radius:50%;margin:auto;background:#dfe6e9;display:flex;align-items:center;justify-content:center;font-size:36px;">👤</div>
            <?php } ?>
        </div>
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label>Class</label>
            <input type="text" name="class_name" value="<?php echo htmlspecialchars($student['class_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Mobile</label>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Profile Image</label>
            <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <button type="submit" name="save_profile" class="apply-btn">Save Profile</button>
    </form>
</div>
<script>

// section switch
function showSection(id){
document.querySelectorAll(".section").forEach(s=>s.style.display="none");
document.getElementById(id).style.display="block";
}
showSection('home');

// calculator
function append(v){document.getElementById("display").value+=v;}
function clearDisplay(){document.getElementById("display").value="";}
function del(){let v=document.getElementById("display").value;document.getElementById("display").value=v.slice(0,-1);}
function calculate(){
try{
let r=eval(document.getElementById("display").value);
document.getElementById("display").value=r;
}catch{alert("Invalid");}
}

// sgpa
function convert(){
    let sg = parseFloat(document.getElementById("sg").value);

    if(isNaN(sg) || sg < 0 || sg > 10){
        document.getElementById("per").innerText = "Enter valid SGPA (0-10)";
        return;
    }

    let percentage = (sg * 10).toFixed(2);

    document.getElementById("per").innerText = 
    "Percentage: " + percentage + "%";
}

// leave auto update
function loadLeaves(){
fetch("fetch_leave.php", {
    credentials: 'same-origin'
})
.then(res=>res.text())
.then(d=>{
    document.getElementById("leave-container").innerHTML=d;
});
}
loadLeaves();
setInterval(loadLeaves,3000);

// notice live update
function loadNotices(){
fetch("fetch_notices.php", {
    credentials: 'same-origin'
})
.then(res=>res.text())
.then(d=>{
    const box = document.getElementById("notice-container");
    if(box){ box.innerHTML = d; }
});
}
loadNotices();
setInterval(loadNotices,5000);

// delete leave
function deleteLeave(id){
fetch("delete_leave.php?id="+id, {
    credentials: 'same-origin'
})
.then(()=>loadLeaves());
}

</script>

</body>
</html>