<?php
require_once __DIR__ . "/../config/db.php";
include __DIR__ . "/../includes/header.php";

$events = $conn->query("
    SELECT Event_name, Event_description, Start_time, End_time 
    FROM Event
    ORDER BY Start_time ASC 
    LIMIT 5
");
?>

<h2>Welcome to the University Portal</h2>
<p>This is the central hub for students, alumni, and administrators.</p>

<section>
    <h3>Upcoming Events</h3>
    <?php if ($events && $events->num_rows > 0): ?>
        <ul>
            <?php while ($row = $events->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($row['Event_name']); ?></strong><br>
                    <?php echo htmlspecialchars($row['Event_description']); ?><br>
                    <small>
                        <?php echo date("M d, Y H:i", strtotime($row['Start_time'])); ?>
                        – 
                        <?php echo date("M d, Y H:i", strtotime($row['End_time'])); ?>
                    </small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No upcoming events available.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
