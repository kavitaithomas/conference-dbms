<?php
include 'config.php';

// This block handles the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_date'])) {
    $selectedDate = $_POST['schedule_date'];

    try {
        // Query to get sessions and their speakers for the selected date
        $sql = "
            SELECT s.location, s.date, s.stime, s.etime,
                   sp.firstname, sp.lastname
            FROM session s
            LEFT JOIN speaksAt sa ON s.location = sa.location AND s.stime = sa.stime
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
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No sessions scheduled for this date.</p>";
        }
    }
    ?>
</body>
</html>
