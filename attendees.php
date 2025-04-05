<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
include 'config.php';

$success = '';
$error = '';

// Handle submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? '';  // Get the manually entered ID
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $fee = $_POST['fee'] ?? '';
    
    try {
        if ($type === 'student') {
            // Insert student with the manually entered ID
            $stmt = $pdo->prepare("INSERT INTO student (id, firstname, lastname, fee) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $fname, $lname, $fee]);

            // Check if room is assigned
            $roomNum = $_POST['roomNum'] ?? null;
            if (!empty($roomNum)) {
                $assignStmt = $pdo->prepare("INSERT INTO assigned (studentID, roomNum) VALUES (?, ?)");
                $assignStmt->execute([$id, $roomNum]);
            }

            $success = "Student added successfully.";

        } elseif ($type === 'professional') {
            // Insert professional with the manually entered ID
            $stmt = $pdo->prepare("INSERT INTO professional (id, firstname, lastname, fee) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $fname, $lname, $fee]);
            $success = "Professional added successfully.";

        } elseif ($type === 'sponsor') {
            // Insert sponsor with the manually entered ID
            $stmt = $pdo->prepare("INSERT INTO sponsor (id, firstname, lastname, fee) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $fname, $lname, $fee]);
            $success = "Sponsor added successfully.";
        }
    } catch (PDOException $e) {
        $error = "Error adding attendee: " . $e->getMessage();
    }
}

// Fetch attendees
$students = $pdo->query("SELECT id, firstname, lastname, fee FROM student")->fetchAll(PDO::FETCH_ASSOC);
$professionals = $pdo->query("SELECT id, firstname, lastname, fee FROM professional")->fetchAll(PDO::FETCH_ASSOC);
$sponsors = $pdo->query("SELECT id, firstname, lastname, fee FROM sponsor")->fetchAll(PDO::FETCH_ASSOC);
$rooms = $pdo->query("SELECT num FROM room ORDER BY num")->fetchAll(PDO::FETCH_ASSOC);

// Calculate total intake for registration fees and sponsorship
$totalRegistration = 0;
$totalSponsorship = 0;

foreach ($students as $student) {
    $totalRegistration += $student['fee'];
}

foreach ($professionals as $professional) {
    $totalRegistration += $professional['fee'];
}

foreach ($sponsors as $sponsor) {
    $totalSponsorship += $sponsor['fee'];
}

$totalIntake = $totalRegistration + $totalSponsorship;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendees</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="background">
    <div class="button-container">
        <a href="conference.php">Go back home</a>
    </div>
    <h1>Conference Attendees</h1>

    <?php if ($success): ?>
        <p style="color: green"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form-row">
        <!-- Student Form -->
        <div class="form-container">
            <h2>Add Student</h2>
            <form method="post">
                <input type="hidden" name="type" value="student">
                ID: <input type="text" name="id" required><br>
                First Name: <input type="text" name="fname" required><br>
                Last Name: <input type="text" name="lname" required><br>
                Registration Fee: <input type="number" name="fee" step="0.01" required><br>
                Assign to Room:
                <select name="roomNum">
                    <option value="">-- Select Room --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= htmlspecialchars($r['num']) ?>"><?= htmlspecialchars($r['num']) ?></option>
                    <?php endforeach; ?>
                </select><br>
                <input type="submit" value="Add Student" class="submit-btn">
            </form>
        </div>

        <!-- Professional Form -->
        <div class="form-container">
            <h2>Add Professional</h2>
            <form method="post">
                <input type="hidden" name="type" value="professional">
                ID: <input type="text" name="id" required><br>
                First Name: <input type="text" name="fname" required><br>
                Last Name: <input type="text" name="lname" required><br>
                Registration Fee: <input type="number" name="fee" step="0.01" required><br>
                <input type="submit" value="Add Professional" class="submit-btn">
            </form>
        </div>

        <!-- Sponsor Form -->
        <div class="form-container">
            <h2>Add Sponsor</h2>
            <form method="post">
                <input type="hidden" name="type" value="sponsor">
                ID: <input type="text" name="id" required><br>
                First Name: <input type="text" name="fname" required><br>
                Last Name: <input type="text" name="lname" required><br>
                Registration Fee: <input type="number" name="fee" step="0.01" required><br>
                <input type="submit" value="Add Sponsor" class="submit-btn">
            </form>
        </div>
    </div>


    <h2>All Attendees</h2>
<div class="form-row">
    <div class="form-container">
        <h3>Students</h3>
        <?php if ($students): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['id']) ?></td>
                            <td><?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname']) ?></td>
                            <td>$<?= $s['fee'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No students yet.</p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Professionals</h3>
        <?php if ($professionals): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professionals as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['id']) ?></td>
                            <td><?= htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) ?></td>
                            <td>$<?= $p['fee'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No professionals yet.</p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Sponsors</h3>
        <?php if ($sponsors): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $sp): ?>
                        <tr>
                            <td><?= htmlspecialchars($sp['id']) ?></td>
                            <td><?= htmlspecialchars($sp['firstname'] . ' ' . $sp['lastname']) ?></td>
                            <td>$<?= $sp['fee'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No sponsors yet.</p>
        <?php endif; ?>
    </div>
</div>


    <!-- Total Intake Display -->
    <h2>Total Intake</h2>
    <p><strong>Total Registration Fees:</strong> $<?= number_format($totalRegistration, 2) ?></p>
    <p><strong>Total Sponsorship Fees:</strong> $<?= number_format($totalSponsorship, 2) ?></p>
    <p><strong>Total Intake:</strong> $<?= number_format($totalIntake, 2) ?></p>

</body>
</html>

