<?php
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>Please <a href='login.php'>login</a> to view your profile.</p>";
    include __DIR__ . "/../includes/footer.php";
    exit;
}

$errors = [];
$success = "";

// Fetch user info
$stmt = $conn->prepare("SELECT User_mail, Profile_pic FROM User WHERE User_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $profile_pic);
$stmt->fetch();
$stmt->close();

// Determine user type and get details
$type_result = $conn->query("SELECT * FROM Student WHERE User_id = $user_id");
if ($type_result->num_rows > 0) {
    $user_type = "student";
    $row = $type_result->fetch_assoc();
    $name = $row['User_name'];
    $department = $row['Department'];
} else {
    $user_type = "alumni";
    $row = $conn->query("SELECT * FROM Alumni WHERE User_id = $user_id")->fetch_assoc();
    $name = $row['User_name'];
    $session = $row['Session'];
    $designation = $row['Designation'];
    $job_location = $row['Job_location'];
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name_new = trim($_POST['name']);
    $department_new = trim($_POST['department'] ?? '');
    $session_new = trim($_POST['session'] ?? '');
    $designation_new = trim($_POST['designation'] ?? '');
    $job_location_new = trim($_POST['job_location'] ?? '');

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = __DIR__ . "/../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $filename = basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $filename;
            $stmt = $conn->prepare("UPDATE User SET Profile_pic=? WHERE User_id=?");
            $stmt->bind_param("si", $profile_pic, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    // Update user-specific info
    if ($user_type == "student") {
        $stmt = $conn->prepare("UPDATE Student SET User_name=?, Department=? WHERE User_id=?");
        $stmt->bind_param("ssi", $name_new, $department_new, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE Alumni SET User_name=?, Session=?, Designation=?, Job_location=? WHERE User_id=?");
        $stmt->bind_param("ssssi", $name_new, $session_new, $designation_new, $job_location_new, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    $success = "Profile updated successfully!";
}
?>

<div class="auth-container">
    <h2>My Profile</h2>

    <?php if ($errors): ?>
        <div class="error-box">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-box">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-box">
        <label>Profile Picture:</label>
        <?php if ($profile_pic): ?>
            <img src="../uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width:100px;border-radius:50%;margin-bottom:10px;">
        <?php endif; ?>
        <input type="file" name="profile_pic" accept="image/*">

        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <?php if ($user_type == "student"): ?>
            <label>Department:</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>">
        <?php else: ?>
            <label>Session:</label>
            <input type="text" name="session" value="<?php echo htmlspecialchars($session); ?>">

            <label>Designation:</label>
            <input type="text" name="designation" value="<?php echo htmlspecialchars($designation); ?>">

            <label>Job Location:</label>
            <input type="text" name="job_location" value="<?php echo htmlspecialchars($job_location); ?>">
        <?php endif; ?>

        <label>Email (cannot edit):</label>
        <input type="email" value="<?php echo htmlspecialchars($email); ?>" disabled>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
