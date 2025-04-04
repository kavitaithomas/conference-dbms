<?php
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission for modifying session
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_session'])) {
    $sessionID = $_POST['session_id'];  // ID or unique identifier for session
    $newDate = $_POST['new_date'];
    $newStime = $_POST['new_stime'];
    $newEtime = $_POST['new_etime'];
    $newLocation = $_POST['new_location'];

    // Debugging: Check if the required variables are set
    if (empty($sessionID) || empty($newDate) || empty($newStime) || empty($newEtime) || empty($newLocation)) {
        echo "Error: One or more required fields are empty!";
        exit;
    }

    try {
        // Update session in the 'session' table
        $updateStmt = $pdo->prepare("
            UPDATE session 
            SET date = ?, stime = ?, etime = ?, location = ?
            WHERE location = ? AND date = ? AND stime = ?
        ");
        
        // Debugging: Check the session_id value
        echo "Session ID received: " . htmlspecialchars($sessionID); // Add this line for debugging
        $sessionDetails = explode(',', $sessionID);  // Extract session details from hidden field
        $location = $sessionDetails[0];
        $date = $sessionDetails[1];
        $stime = $sessionDetails[2];
        
        // Update the session record
        $updateStmt->execute([$newDate, $newStime, $newEtime, $newLocation, $location, $date, $stime]);

        // Success message
        $success = "Session updated successfully.";
    } catch (PDOException $e) {
        // Debugging: Output error if SQL fails
        echo "<p>Error updating session: " . $e->getMessage() . "</p>";
    }
}

// Query to get sessions for the selected date
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_date'])) {
    $selectedDate = $_POST['schedule_date'];

    try {
        // Query to get sessions for the selected date
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
        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "<p>Error fetching schedule: " . $e->getMessage() . "</p>";
    }
}

// Query to fetch distinct session dates
try {
    $dateQuery = "SELECT DISTINCT date FROM session ORDER BY date";
    $dateStmt = $pdo->query($dateQuery);
    $dates = $dateStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching available dates: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conference Schedule</title>
</head>
<body>
    <a href="index.php"><button>Go back home</button></a>
    <h1>View Conference Schedule</h1>
    <p>Select a date to view all sessions scheduled for that day:</p>

    <!-- Form to select the date from available session dates -->
    <form action="schedule.php" method="post">
        <label for="schedule_date">Date:</label>
        <select id="schedule_date" name="schedule_date" required>
            <option value="">-- Select --</option>
            <?php
            // Dynamically populate the dropdown with available session dates
            foreach ($dates as $date) {
                echo '<option value="' . htmlspecialchars($date['date']) . '">' . htmlspecialchars($date['date']) . '</option>';
            }
            ?>
        </select>
        <input type="submit" value="View Schedule">
    </form>

    <!-- Display the schedule if a date is selected -->
    <?php
    if (isset($schedule)) {
        echo "<h2>Schedule for " . htmlspecialchars($selectedDate) . "</h2>";

        if ($schedule) {
            echo "<table border='1'>
                    <tr>
                        <th>Location</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Speaker</th>
                        <th>Action</th>
                    </tr>";

            foreach ($schedule as $row) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['location']) . "</td>
                        <td>" . htmlspecialchars($row['stime']) . "</td>
                        <td>" . htmlspecialchars($row['etime']) . "</td>
                        <td>";
                if ($row['firstname']) {
                    echo htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']);
                } else {
                    echo "—";
                }
                echo "</td>
                        <td>
                            <form method='POST'>
                                <input type='hidden' name='session_id' value='" . htmlspecialchars($row['location']) . "," . htmlspecialchars($row['date']) . "," . htmlspecialchars($row['stime']) . "'>
                                <input type='submit' name='modify_session' value='Modify'>
                            </form>
                        </td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No sessions scheduled for this date.</p>";
        }
    }

    // Display form for modifying session
    if (isset($_POST['modify_session'])) {
        // Extract the session details from the hidden session ID
        list($location, $date, $stime) = explode(',', $_POST['session_id']);

        // Fetch the session details to pre-fill the form
        $fetchSessionStmt = $pdo->prepare("SELECT * FROM session WHERE location = ? AND date = ? AND stime = ?");
        $fetchSessionStmt->execute([$location, $date, $stime]);
        $session = $fetchSessionStmt->fetch(PDO::FETCH_ASSOC);

        if ($session) {
            ?>
            <h2>Modify Session</h2>
            <form method="POST">
                <input type="hidden" name="session_id" value="<?= htmlspecialchars($session['location']) . ',' . htmlspecialchars($session['date']) . ',' . htmlspecialchars($session['stime']) ?>">
                <label for="new_date">New Date:</label>
                <input type="date" name="new_date" value="<?= htmlspecialchars($session['date']) ?>" required><br>
                <label for="new_stime">New Start Time:</label>
                <input type="time" name="new_stime" value="<?= htmlspecialchars($session['stime']) ?>" required><br>
                <label for="new_etime">New End Time:</label>
                <input type="time" name="new_etime" value="<?= htmlspecialchars($session['etime']) ?>" required><br>
                <label for="new_location">New Location:</label>
                <input type="text" name="new_location" value="<?= htmlspecialchars($session['location']) ?>" required><br>
                <input type="submit" name="update_session" value="Update Session">
            </form>
            <?php
        } else {
            echo "<p>Session not found.</p>";
        }
    }
    ?>

    <!-- Display Success/Error Messages -->
    <?php if (isset($success)): ?>
        <p style="color: green"><?= htmlspecialchars($success) ?></p>
    <?php elseif (isset($error)): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</body>
</html>

