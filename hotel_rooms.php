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
    <div class="container">
    <div class="button-container">
        <a href="index.php">Go back home</a>
    </div>

        <h1>Hotel Room Assignments</h1>
        <p>Select a hotel room to view the students assigned to it:</p>

        <form method="post" action="hotel_rooms.php">
            <label for="room">Hotel Room:</label><br>
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
            <input type="submit" value="View Students" class="home-button">
        </form>

        <?php
        if (!empty($_POST['room'])) {
            $roomNum = $_POST['room'];
            echo "<h2>Students in Room " . htmlspecialchars($roomNum) . "</h2>";

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
                    echo "<table><tr><th>First Name</th><th>Last Name</th></tr>";
                    foreach ($students as $s) {
                        echo "<tr><td>" . htmlspecialchars($s['firstname']) . "</td><td>" . htmlspecialchars($s['lastname']) . "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No students assigned to this room.</p>";
                }
            } catch (PDOException $e) {
                echo "<p>Error fetching students: " . $e->getMessage() . "</p>";
            }

            $pdo = null;
        }
        ?>
    </div>
</body>
</html>
