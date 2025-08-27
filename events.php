<?php
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$user_mail = $_SESSION['user_mail'] ?? null;

$errors = [];
$success = "";

// ========== HANDLE EVENT CREATION ==========
if (isset($_POST['create_event']) && $user_id) {
    $event_name = trim($_POST['event_name']);
    $event_description = trim($_POST['event_description']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);

    if (!$event_name || !$start_time || !$end_time) {
        $errors[] = "Event name and date/time are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Event (User_id, User_mail, Event_creator, Event_name, Event_description, Start_time, End_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $user_mail, $user_mail, $event_name, $event_description, $start_time, $end_time);
        if ($stmt->execute()) {
            $success = "Event submitted successfully! Await admin verification.";
        } else {
            $errors[] = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// ========== FETCH ALL EVENTS ==========
$events = $conn->query("SELECT Event_name, Event_description, Start_time, End_time, Event_creator FROM Event ORDER BY Start_time ASC");
?>

<div class="auth-container">
    <h2>Upcoming Events</h2>

    <!-- Messages -->
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

    <!-- Event List -->
    <section>
        <?php if ($events && $events->num_rows > 0): ?>
            <ul>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($row['Event_name']); ?></strong><br>
                        <?php echo htmlspecialchars($row['Event_description']); ?><br>
                        <small>
                            By: <?php echo htmlspecialchars($row['Event_creator']); ?><br>
                            <?php echo date("M d, Y H:i", strtotime($row['Start_time'])); ?> – 
                            <?php echo date("M d, Y H:i", strtotime($row['End_time'])); ?>
                        </small>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No events available.</p>
        <?php endif; ?>
    </section>

    <!-- Event Submission Form (only for logged-in users) -->
    <?php if ($user_id): ?>
        <h3>Apply to Organize a New Event</h3>
        <form method="post" class="form-box">
            <label>Event Name:</label>
            <input type="text" name="event_name" required>
            <label>Event Description:</label>
            <textarea name="event_description" rows="4"></textarea>
            <label>Start Time:</label>
            <input type="datetime-local" name="start_time" required>
            <label>End Time:</label>
            <input type="datetime-local" name="end_time" required>
            <button type="submit" name="create_event">Submit Event</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to apply for a new event.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
