<?php
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = "";

// ========== HANDLE REGISTRATION ==========
if (isset($_POST['register'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM User WHERE User_mail = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Email already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO User (User_mail, Password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);
        if ($stmt->execute()) {
            $success = "Registration successful. You can now log in.";
        } else {
            $errors[] = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// ========== HANDLE LOGIN ==========
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM User WHERE User_mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['User_id'];
            $_SESSION['user_mail'] = $row['User_mail'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Invalid password.";
        }
    } else {
        $errors[] = "No account found with this email.";
    }
    $stmt->close();
}
?>

<div class="auth-container">
    <h2>Welcome to University Portal</h2>

    <!-- Show messages -->
    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $e): ?>
                <p><?php echo htmlspecialchars($e); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-box">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tabs">
        <button id="loginTab" onclick="showForm('login')">Sign In</button>
        <button id="registerTab" onclick="showForm('register')">Register</button>
    </div>

    <!-- Login Form -->
    <form id="loginForm" method="post" class="form-box">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit" name="login">Sign In</button>
    </form>

    <!-- Register Form -->
    <form id="registerForm" method="post" class="form-box" style="display:none;">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Password:</label>
        <input type="password" name="password" required>
        <button type="submit" name="register">Register</button>
    </form>
</div>

<script>
function showForm(type) {
    document.getElementById('loginForm').style.display = (type === 'login') ? 'block' : 'none';
    document.getElementById('registerForm').style.display = (type === 'register') ? 'block' : 'none';
}
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
