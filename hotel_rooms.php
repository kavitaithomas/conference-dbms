<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Hotel Room Students</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="background">
    <div>
        <div class="button-container">
        <a href="conference.php">Go back home</a>
        </div>

        <h1>Hotel Room Assignments</h1>
        <p>Select a hotel room to view the students assigned to it:</p>

        <!-- Filter form for selecting a specific room -->
        <form method="post" action="hotel_rooms.php">
            <label for="room">Hotel Room:</label>
            <select name="room" id="room" required>
                <option value="">-- Select Room --</option>
                <?php
                include 'config.php';

                try {
                    $stmt = $pdo->query("SELECT num FROM room ORDER BY num");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (isset($_POST['room']) && $_POST['room'] == $row['num']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['num']) . '" ' . $selected . '>' . htmlspecialchars($row['num']) . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option disabled>Error loading rooms</option>';
                }
                ?>
            </select><br><br>
            <input type="submit" value="View Students" class="submit-btn">
        </form>

        <?php
        // Show selected room's students if the form is submitted
        if (!empty($_POST['room'])) {
            $roomNum = $_POST['room'];
            echo "<h2 class='results-heading'>Students in Room " . htmlspecialchars($roomNum) . "</h2>";

            try {
                $sql = "
                    SELECT s.firstname, s.lastname
                    FROM student s
                    JOIN assigned a ON s.id = a.studentID
                    WHERE a.roomNum = :roomNum
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute(['roomNum' => $roomNum]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($students) {
                    echo "<table class='results-table'><tr><th>First Name</th><th>Last Name</th></tr>";
                    foreach ($students as $s) {
                        echo "<tr><td>" . htmlspecialchars($s['firstname']) . "</td><td>" . htmlspecialchars($s['lastname']) . "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p class='no-results'>No students assigned to this room.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error-message'>Error fetching students: " . $e->getMessage() . "</p>";
            }
        }

        // Show all rooms in a single table with room number, number of beds, and number of students
        echo "<h2 class='results-heading'>All Hotel Rooms</h2>";
        echo "<table class='results-table'>
                <tr>
                    <th>Room Number</th>
                    <th>Number of Beds</th>
                    <th>Number of Students</th>
                </tr>";

        try {
            $sql = "
                SELECT r.num AS roomNum, r.num_beds, COUNT(a.studentID) AS numStudents
                FROM room r
                LEFT JOIN assigned a ON r.num = a.roomNum
                GROUP BY r.num
                ORDER BY r.num
            ";

            $stmt = $pdo->query($sql);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rooms as $room) {
                echo "<tr>
                        <td>" . htmlspecialchars($room['roomNum']) . "</td>
                        <td>" . htmlspecialchars($room['num_beds']) . "</td>
                        <td>" . htmlspecialchars($room['numStudents']) . "</td>
                    </tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo "<p class='error-message'>Error fetching room data: " . $e->getMessage() . "</p>";
        }

        // Close the PDO connection
        $pdo = null;
        ?>
    </div>
</body>
</html>


