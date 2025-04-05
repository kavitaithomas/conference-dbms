<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all available session dates for dropdown
try {
    $dateQuery = "SELECT DISTINCT date FROM session ORDER BY date";
    $dateStmt = $pdo->query($dateQuery);
    $dates = $dateStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching available dates: " . $e->getMessage() . "</p>";
}

// Handle form submission for filtering sessions by date
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_date'])) {
    $selectedDate = $_POST['schedule_date'];

    try {
        // Fetch sessions based on selected date
        $sql = "
            SELECT s.location, s.date, s.stime, s.etime, sp.firstname, sp.lastname
            FROM session s
            LEFT JOIN speaksAt sa ON s.location = sa.location AND s.date = sa.date AND s.stime = sa.stime
            LEFT JOIN speaker sp ON sa.speakerID = sp.id
            WHERE s.date = :selectedDate
            ORDER BY s.stime
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['selectedDate' => $selectedDate]);
        $scheduleByDate = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Error fetching schedule: " . $e->getMessage() . "</p>";
    }
}

// Fetch all sessions if no specific date is selected
try {
    $sql = "
        SELECT s.location, s.date, s.stime, s.etime, sp.firstname, sp.lastname
        FROM session s
        LEFT JOIN speaksAt sa ON s.location = sa.location AND s.date = sa.date AND s.stime = sa.stime
        LEFT JOIN speaker sp ON sa.speakerID = sp.id
        ORDER BY s.date, s.stime
    ";
    $stmt = $pdo->query($sql);
    $allSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching schedule: " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Conference Schedule</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="background">

    <div class="button-container">
    <a href="conference.php">Go back home</a>
    </div>

    <h1>View Conference Schedule</h1>
    <p>Select a date to view all sessions scheduled for that day:</p>

    <!-- Form to select the date from available session dates -->
    <form action="schedule.php" method="post" class="form">
        <label for="schedule_date">Date:</label>
        <select id="schedule_date" name="schedule_date" required class="dropdown">
            <option value="">-- Select --</option>
            <?php
            foreach ($dates as $date) {
                echo '<option value="' . htmlspecialchars($date['date']) . '">' . htmlspecialchars($date['date']) . '</option>';
            }
            ?>
        </select>
        <input type="submit" value="View Schedule" class="submit-btn">
    </form>

    <!-- Display schedule for the selected day if there is any -->
    <?php if (isset($scheduleByDate)): ?>
        <?php if ($scheduleByDate): ?>
            <h2>Sessions on <?= htmlspecialchars($selectedDate) ?></h2>
            <table class="styled-table">
                <tr>
                    <th>Location</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Speaker</th>
                </tr>
                <?php foreach ($scheduleByDate as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['stime']) ?></td>
                        <td><?= htmlspecialchars($row['etime']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['firstname']) ? htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']) : '—' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No sessions scheduled for this date.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>All Sessions</h2>
    <table class="styled-table">
        <tr>
            <th>Location</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Speaker</th>
        </tr>
        <?php foreach ($allSessions as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['stime']) ?></td>
                <td><?= htmlspecialchars($row['etime']) ?></td>
                <td>
                    <?= htmlspecialchars($row['firstname']) ? htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']) : '—' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>

