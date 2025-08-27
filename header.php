<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <h1>University Portal</h1>
        <nav>
            <a href="/public/index.php">Home</a>
            <a href="/public/events.php">Events</a>
            <a href="/public/chat.php">Chat</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/public/profile.php">Profile</a>
                <a href="/public/logout.php">Logout</a>
            <?php else: ?>
                <a href="/public/login.php">Login</a>
                <a href="/public/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
