<?php
include "php/session.php";

// 🔐 Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != "staff"){
    header("Location: login.php");
    exit();
}

include "php/db.php";

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

// 📤 Upload Notes
if(isset($_POST['upload'])){
    $title = $_POST['title'];
    $file = $_FILES['file']['name'];

    move_uploaded_file($_FILES['file']['tmp_name'], "uploads/".$file);

    $conn->query("INSERT INTO notes (title,file_path) VALUES ('$title','$file')");

    header("Location: staff_dashboard.php?note_success=1");
    exit();
}

// 📢 Upload Notice (NEW FEATURE)
if(isset($_POST['upload_notice'])){
    $title = $conn->real_escape_string(trim($_POST['notice_title']));
    $content = $conn->real_escape_string(trim($_POST['notice_content']));

    $conn->query("INSERT INTO notices (title, content) VALUES ('$title','$content')");
    header("Location: staff_dashboard.php?notice_success=1");
    exit();
}

// 🧪 Create exam with multiple questions
if(isset($_POST['create_exam'])){
    $exam_title = $conn->real_escape_string(trim($_POST['exam_title']));
    $exam_desc = $conn->real_escape_string(trim($_POST['exam_description']));
    $questionTexts = $_POST['q_text'] ?? [];
    $optionA = $_POST['q_a'] ?? [];
    $optionB = $_POST['q_b'] ?? [];
    $optionC = $_POST['q_c'] ?? [];
    $optionD = $_POST['q_d'] ?? [];
    $correctArr = $_POST['q_correct'] ?? [];

    if($exam_title !== "" && !empty($questionTexts)){
        $conn->query("INSERT INTO exams (title, description, created_by) VALUES ('$exam_title', '$exam_desc', " . (int)$_SESSION['id'] . ")");
        $exam_id = $conn->insert_id;
        $total = 0;

        for($i = 0; $i < count($questionTexts); $i++){
            $qRaw = trim($questionTexts[$i] ?? '');
            $aRaw = trim($optionA[$i] ?? '');
            $bRaw = trim($optionB[$i] ?? '');
            $cRaw = trim($optionC[$i] ?? '');
            $dRaw = trim($optionD[$i] ?? '');
            $correct = strtoupper(trim($correctArr[$i] ?? ''));

            if($qRaw === '' || $aRaw === '' || $bRaw === '' || $cRaw === '' || $dRaw === ''){
                continue;
            }

            if(!in_array($correct, ['A', 'B', 'C', 'D'], true)){
                continue;
            }

            $q = $conn->real_escape_string($qRaw);
            $a = $conn->real_escape_string($aRaw);
            $b = $conn->real_escape_string($bRaw);
            $c = $conn->real_escape_string($cRaw);
            $d = $conn->real_escape_string($dRaw);

            $conn->query("INSERT INTO exam_questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option)
                          VALUES ($exam_id, '$q', '$a', '$b', '$c', '$d', '$correct')");
            $total++;
        }

        if($total > 0){
            $conn->query("UPDATE exams SET total_questions=$total WHERE id=$exam_id");
        } else {
            $conn->query("DELETE FROM exams WHERE id=$exam_id");
        }
    }

    header("Location: staff_dashboard.php?exam_success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Dashboard</title>

<style>
    /* SIDEBAR */
.sidebar {
    width: 220px;
    background: linear-gradient(180deg, #2c3e50, #34495e);
    color: white;
    height: 100vh;
    padding: 20px;
}

.logo {
    margin-bottom: 20px;
    text-align: center;
}

.sidebar button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    background: transparent;
    color: white;
    border: none;
    text-align: left;
    cursor: pointer;
    border-radius: 8px;
    transition: 0.3s;
}

.sidebar button:hover {
    background: #1abc9c;
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.logout-btn {
    background: #e74c3c;
    color: white;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 6px;
}

.logout-btn:hover {
    background: #c0392b;
}

.welcome {
    font-weight: bold;
}

/* HOME BOX */
.home-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* CARDS */
.cards {
    display: flex;
    gap: 15px;
}

.card {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}
    /* FORM BOX */
.notice-form-box {
    max-width: 400px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* FORM */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* BUTTON */
.post-btn {
    width: 100%;
    padding: 10px;
    background: #1abc9c;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.post-btn:hover {
    background: #16a085;
}

/* NOTICE LIST SCROLL */
.notice-list {
    max-height: 350px;
    overflow-y: auto;
}

/* NOTICE CARD */
.notice-card {
    background: white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.notice-card:hover {
    transform: translateY(-3px);
}

/* TITLE */
.notice-top h3 {
    margin: 0;
    color: #2c3e50;
}

/* CONTENT */
.notice-content {
    margin: 10px 0;
    color: #555;
    line-height: 1.5;
}

/* ACTIONS */
.notice-actions {
    display: flex;
    gap: 10px;
}

/* BUTTONS */
.edit-btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.delete-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.edit-btn:hover {
    background: #2980b9;
}

.delete-btn:hover {
    background: #c0392b;
}
    /* NOTICE CARD */
.notice-card {
    background: white;
    padding: 18px;
    margin: 12px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.notice-card:hover {
    transform: translateY(-3px);
}

/* TITLE */
.notice-top h3 {
    margin: 0;
    color: #2c3e50;
}

/* CONTENT */
.notice-content {
    margin: 10px 0;
    color: #555;
    line-height: 1.5;
}

/* ACTIONS */
.notice-actions {
    display: flex;
    gap: 10px;
}

/* BUTTONS */
.edit-btn {
    background: #3498db;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.delete-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.edit-btn:hover {
    background: #2980b9;
}

.delete-btn:hover {
    background: #c0392b;
}
    /* Hover effect */
.staff-leave-card {
    transition: 0.3s;
}

.staff-leave-card:hover {
    transform: translateY(-3px);
}

/* Status glow */
.status {
    font-weight: bold;
}

/* Buttons hover */
.approve-btn:hover {
    background: #27ae60;
}

.reject-btn:hover {
    background: #c0392b;
}

.delete-btn:hover {
    background: #922b21;
}

    .leave-list {
    max-height: 550px;
    overflow-y: auto;
    padding-right: 5px;
}

    /* 🔥 SCROLLABLE NOTES LIST */
.notes-list {
    max-height: 300px;   /* 👈 height limit */
    overflow-y: auto;    /* 👈 scroll enable */
    padding-right: 5px;
}
    /* UPLOAD BOX */
.upload-box {
    max-width: 400px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* FORM */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* BUTTON */
.upload-btn {
    width: 100%;
    padding: 10px;
    background: #1abc9c;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.upload-btn:hover {
    background: #16a085;
}

/* NOTE CARD */
.note-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px;
    border-radius: 10px;
    margin: 10px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* ACTION BUTTONS */
.note-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 14px;
    border: none;
    cursor: pointer;
}

.view {
    background: #3498db;
}

.delete {
    background: #e74c3c;
}

.btn:hover {
    opacity: 0.85;
}
    /* STAFF LEAVE CARD */
.staff-leave-card {
    background: white;
    padding: 15px;
    margin: 12px 0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

/* TOP */
.leave-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* STATUS */
.status {
    padding: 6px 12px;
    border-radius: 20px;
    color: white;
    font-size: 13px;
}

.approved { background: #2ecc71; }
.rejected { background: #e74c3c; }
.pending { background: #f39c12; }

/* BUTTONS */
.actions {
    margin-top: 10px;
}

.approve-btn {
    padding: 6px 12px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.reject-btn {
    padding: 6px 12px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.delete-btn {
    margin-top: 10px;
    padding: 6px 12px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 6px;
}

/* REJECT TEXT */
.reject-reason {
    color: red;
}
body{margin:0;font-family:Arial;}
.container{display:flex;}

.sidebar{
    width:220px;
    background:#2c3e50;
    color:white;
    height:100vh;
    padding:20px;
}

.sidebar h2{margin-bottom:20px;}

.sidebar button{
    width:100%;
    padding:10px;
    margin:5px 0;
    background:#34495e;
    color:white;
    border:none;
    cursor:pointer;
}

.sidebar button:hover{background:#1abc9c;}

.content{flex:1;padding:20px;}

.section{display:none;}

.topbar{text-align:right;}
</style>
</head>

<body>

<div class="container">
    

<!-- 🔥 SIDEBAR -->
<div class="sidebar">
    <h2 class="logo">⚙ Staff Panel</h2>

    <button onclick="showSection('home')">🏠 Home</button>
    <button onclick="showSection('notes')">📚 Notes</button>
    <button onclick="showSection('leave')">📝 Leave Requests</button>
    <button onclick="showSection('notice')">📢 Notices</button>
    <button onclick="showSection('exam')">🧪 Exams</button>
</div>

<!-- 🔥 CONTENT -->
<div class="content">

<!-- 🔝 TOPBAR -->
<div class="topbar">
    <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👨‍🏫</span>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- 🔥 HOME -->
<div id="home" class="section">

    <div class="home-box">
        <h2>Dashboard Overview 🚀</h2>
        <p>Manage notes, leave requests and notices from one place.</p>
    </div>

    <!-- QUICK CARDS -->
    <div class="cards">

        <div class="card">
            <h3>📚 Notes</h3>
            <p>Upload & manage study material</p>
        </div>

        <div class="card">
            <h3>📝 Leaves</h3>
            <p>Approve or reject requests</p>
        </div>

        <div class="card">
            <h3>📢 Notices</h3>
            <p>Post important updates</p>
        </div>

        <div class="card">
            <h3>🧪 Exams</h3>
            <p>Create exams and evaluate student scores</p>
        </div>

    </div>

</div>

<!-- 🔥 NOTES SECTION -->
<div id="notes" class="section">

<h2>📤 Upload Notes</h2>
<?php if(isset($_GET['note_success'])){ ?>
    <p style="color:green;"><b>Note uploaded successfully.</b></p>
<?php } ?>

<div class="upload-box">

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label>Note Title</label>
            <input type="text" name="title" placeholder="Enter note title" required>
        </div>

        <div class="form-group">
            <label>Select File</label>
            <input type="file" name="file" required>
        </div>

        <button name="upload" class="upload-btn">
            Upload Note
        </button>

    </form>

</div>

<hr>

<h3>📚 Uploaded Notes</h3>

<div class="notes-list">  <!-- 🔥 NEW -->

<?php
$notes = $conn->query("SELECT * FROM notes ORDER BY id DESC");
while($n = $notes->fetch_assoc()){
?>

<div class="note-card">

    <div>
        <h4><?php echo $n['title']; ?></h4>
        <p>File Available</p>
    </div>

    <div class="note-actions">
        <a href="./uploads/<?php echo $n['file_path']; ?>" target="_blank" class="btn view">View</a>
        <button onclick="deleteNote(<?php echo $n['id']; ?>)" class="btn delete">Delete</button>
    </div>

</div>

<?php } ?>

</div> <!-- 🔥 END -->

</div> <!-- ✅ CLOSE NOTES SECTION -->
<!-- 🔥 LEAVE SECTION -->
<div id="leave" class="section">

<hr>

<h3>Leave Requests </h3>

<!-- 🔥 NEW SCROLL BOX ADD -->
<div class="leave-list">

<?php
$result = $conn->query("SELECT * FROM leave_applications ORDER BY id DESC");

while($row = $result->fetch_assoc()){
?>      
<div class="staff-leave-card" id="row-<?php echo $row['id']; ?>">

    <div class="leave-top">
        <h3>👨‍🎓 <?php echo $row['student_name']; ?> (<?php echo $row['class']; ?>)</h3>

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

    <p>📅 <b>Date:</b> <?php echo $row['leave_date']; ?></p>

    <p>📝 <b>Reason:</b> <?php echo $row['reason']; ?></p>

    <div class="actions" id="actions-<?php echo $row['id']; ?>">

    <?php if($row['status'] == "Pending"){ ?>

        <button class="approve-btn" onclick="approve(<?php echo $row['id']; ?>)">
            ✔ Approve
        </button>

        <button class="reject-btn" onclick="reject(<?php echo $row['id']; ?>)">
            ❌ Reject
        </button>

    <?php } else { ?>

        <p><b>Decision Completed</b></p>

        <?php if($row['status']=="Rejected"){ ?>
            <p class="reject-reason">
                ❌ Reason: <?php echo $row['reject_reason']; ?>
            </p>
        <?php } ?>

    <?php } ?>

    </div>

    <button class="delete-btn" onclick="deleteLeave(<?php echo $row['id']; ?>)">
        🗑 Delete
    </button>

</div>
<?php } ?>

</div> <!-- 🔥 CLOSE SCROLL BOX -->

</div>

<!-- 🔥 NOTICE SECTION -->
<div id="notice" class="section">

<hr>

<h2>📢 Notice Board</h2>
<?php if(isset($_GET['notice_success'])){ ?>
    <p style="color:green;"><b>Notice posted successfully.</b></p>
<?php } ?>

<!-- 🔥 UPLOAD FORM -->
<div class="notice-form-box">

<form method="POST">

    <div class="form-group">
        <label>Title</label>
        <input type="text" name="notice_title" placeholder="Enter notice title" required>
    </div>

    <div class="form-group">
        <label>Content</label>
        <textarea name="notice_content" placeholder="Write notice..." required></textarea>
    </div>

    <button name="upload_notice" class="post-btn">
        📤 Post Notice
    </button>

</form>

</div>

<hr>

<h3>All Notices</h3>

<!-- 🔥 SCROLLABLE LIST -->
<div class="notice-list">

<?php
$notices = $conn->query("SELECT * FROM notices ORDER BY id DESC");

while($n = $notices->fetch_assoc()){
?>

<div class="notice-card" id="notice-<?php echo $n['id']; ?>">

    <div class="notice-top">
        <h3>📢 <?php echo $n['title']; ?></h3>
    </div>

    <p class="notice-content">
        <?php echo $n['content']; ?>
    </p>

    <div class="notice-actions">
        <button class="edit-btn" onclick="editNotice(<?php echo $n['id']; ?>)">
            ✏ Edit
        </button>

        <button class="delete-btn" onclick="deleteNotice(<?php echo $n['id']; ?>)">
            🗑 Delete
        </button>
    </div>

</div>

<?php } ?>

</div>
</div>

<!-- 🧪 EXAM SECTION -->
<div id="exam" class="section">
<hr>
<h2>🧪 Exam Management</h2>
<?php if(isset($_GET['exam_success'])){ ?>
    <p style="color:green;"><b>Exam saved successfully.</b></p>
<?php } ?>

<div class="notice-form-box">
    <form method="POST">
        <div class="form-group">
            <label>Exam Title</label>
            <input type="text" name="exam_title" placeholder="Example: DBMS Unit Test" required>
        </div>

        <div class="form-group">
            <label>Description (optional)</label>
            <textarea name="exam_description" placeholder="Short instructions for students"></textarea>
        </div>

        <div id="question-wrap">
            <div class="form-group" style="border:1px solid #dfe6e9;padding:10px;border-radius:8px;margin-bottom:12px;">
                <label>Question 1</label>
                <textarea name="q_text[]" placeholder="Enter question" required></textarea>
                <input type="text" name="q_a[]" placeholder="Option A" required style="margin-top:8px;">
                <input type="text" name="q_b[]" placeholder="Option B" required style="margin-top:8px;">
                <input type="text" name="q_c[]" placeholder="Option C" required style="margin-top:8px;">
                <input type="text" name="q_d[]" placeholder="Option D" required style="margin-top:8px;">
                <select name="q_correct[]" required style="margin-top:8px;width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;">
                    <option value="">Select Correct Answer</option>
                    <option value="A">Correct: A</option>
                    <option value="B">Correct: B</option>
                    <option value="C">Correct: C</option>
                    <option value="D">Correct: D</option>
                </select>
            </div>
        </div>
        <button type="button" class="edit-btn" onclick="addQuestionBlock()">+ Add Another Question</button>
        <br><br>

        <button name="create_exam" class="post-btn">Create Exam</button>
    </form>
</div>

<h3>Published Exams</h3>
<div class="notice-list">
<?php
$examResult = $conn->query("SELECT * FROM exams ORDER BY id DESC");
while($exam = $examResult->fetch_assoc()){
?>
    <div class="notice-card">
        <div class="notice-top">
            <h3>📝 <?php echo htmlspecialchars($exam['title']); ?></h3>
        </div>
        <p class="notice-content"><?php echo htmlspecialchars($exam['description']); ?></p>
        <p><b>Questions:</b> <?php echo (int)$exam['total_questions']; ?></p>
    </div>
<?php } ?>
</div>

<h3>Recent Scores</h3>
<div class="notice-list">
<?php
$scoreResult = $conn->query("SELECT ea.score, ea.total, ea.submitted_at, s.name AS student_name, e.title AS exam_title
                             FROM exam_attempts ea
                             JOIN students s ON s.id = ea.student_id
                             JOIN exams e ON e.id = ea.exam_id
                             ORDER BY ea.id DESC
                             LIMIT 25");
while($score = $scoreResult->fetch_assoc()){
?>
    <div class="notice-card">
        <h3>👨‍🎓 <?php echo htmlspecialchars($score['student_name']); ?></h3>
        <p class="notice-content">
            <?php echo htmlspecialchars($score['exam_title']); ?> -
            <b><?php echo (int)$score['score']; ?>/<?php echo (int)$score['total']; ?></b>
        </p>
        <small>🕒 <?php echo $score['submitted_at']; ?></small>
    </div>
<?php } ?>
</div>
</div>
</div>
</div>

<script>

// section switch
function showSection(id){
    document.querySelectorAll(".section").forEach(s=>s.style.display="none");
    document.getElementById(id).style.display="block";
}
showSection('home');

// approve
function approve(id){
    fetch("approve.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        credentials: 'same-origin',
        body: "id=" + id
    })
    .then(() => {
        loadLeaves();
    });
}

// reject
function reject(id){
    let reason = prompt("Enter reject reason:");

    if(reason){
        fetch("reject.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            credentials: 'same-origin',
            body: "id=" + id + "&reason=" + encodeURIComponent(reason)
        })
        .then(() => {
            loadLeaves();
        });
    }
}

function deleteLeave(id){
    if(confirm("Delete this leave?")){
        fetch("delete_leave.php?id=" + id, {
            credentials: 'same-origin'   // 🔥 IMPORTANT
        })
        .then(() => {
            document.getElementById("row-"+id).remove();
        });
    }
}

function deleteNote(id){
    if(confirm("Delete this note?")){
        fetch("delete_note.php?id=" + id, {
            credentials: 'same-origin'
        })
        .then(() => location.reload());
    }
}

// DELETE NOTICE
function deleteNotice(id){
    if(confirm("Delete this notice?")){
        fetch("delete_notice.php?id=" + id, {
            credentials: 'same-origin'   // 🔥 IMPORTANT
        })
        .then(() => {
            document.getElementById("notice-"+id).remove();
        });
    }
}

// EDIT
function editNotice(id){
    let newTitle = prompt("Enter new title:");
    let newContent = prompt("Enter new content:");

    if(newTitle && newContent){
        fetch("update_notice.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            credentials: 'same-origin',   // 🔥 MUST ADD
            body: "id=" + id + 
                  "&title=" + encodeURIComponent(newTitle) + 
                  "&content=" + encodeURIComponent(newContent)
        })
        .then(res => res.text())
        .then(data => {

            if(data.trim() === "Updated"){
                document.getElementById("title-"+id).innerText = newTitle;
                document.getElementById("content-"+id).innerText = newContent;
            }

        });
    }
}

function loadLeaves(){
    location.reload();
}

function addQuestionBlock(){
    const wrap = document.getElementById("question-wrap");
    const idx = wrap.children.length + 1;
    const block = document.createElement("div");
    block.className = "form-group";
    block.style = "border:1px solid #dfe6e9;padding:10px;border-radius:8px;margin-bottom:12px;";
    block.innerHTML =
        '<label>Question ' + idx + '</label>' +
        '<textarea name="q_text[]" placeholder="Enter question" required></textarea>' +
        '<input type="text" name="q_a[]" placeholder="Option A" required style="margin-top:8px;">' +
        '<input type="text" name="q_b[]" placeholder="Option B" required style="margin-top:8px;">' +
        '<input type="text" name="q_c[]" placeholder="Option C" required style="margin-top:8px;">' +
        '<input type="text" name="q_d[]" placeholder="Option D" required style="margin-top:8px;">' +
        '<select name="q_correct[]" required style="margin-top:8px;width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;">' +
            '<option value="">Select Correct Answer</option>' +
            '<option value="A">Correct: A</option>' +
            '<option value="B">Correct: B</option>' +
            '<option value="C">Correct: C</option>' +
            '<option value="D">Correct: D</option>' +
        '</select>';
    wrap.appendChild(block);
}
</script>

</body>
</html>