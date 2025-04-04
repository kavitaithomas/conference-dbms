<?php
include 'config.php';

$success = '';
$error = '';

// Handle form submissions for adding a new sponsor and company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sponsor'])) {
    $companyName = $_POST['company_name'] ?? '';
    $sponsorshipLevel = $_POST['sponsorship_level'] ?? '';
    $sponsorID = $_POST['sponsor_id'] ?? ''; // New sponsor's ID from the form
    $sponsorFirstName = $_POST['sponsor_first_name'] ?? ''; // New sponsor's first name
    $sponsorLastName = $_POST['sponsor_last_name'] ?? ''; // New sponsor's last name

    try {
        // Check if company already exists
        $companyStmt = $pdo->prepare("SELECT COUNT(*) FROM company WHERE name = ?");
        $companyStmt->execute([$companyName]);
        $companyExists = $companyStmt->fetchColumn();

        if ($companyExists == 0) {
            // Insert the company if it doesn't exist (avoid duplicates)
            $insertCompanyStmt = $pdo->prepare("INSERT INTO company (name, level) VALUES (?, ?)");
            $insertCompanyStmt->execute([$companyName, $sponsorshipLevel]);
        }

        // Insert the sponsor into the sponsor table (new sponsor)
        $insertSponsorStmt = $pdo->prepare("INSERT INTO sponsor (id, firstname, lastname) VALUES (?, ?, ?)");
        $insertSponsorStmt->execute([$sponsorID, $sponsorFirstName, $sponsorLastName]);

        // Now, associate the newly added sponsor with the company
        $insertRepresentsStmt = $pdo->prepare("INSERT INTO represents (companyName, sponsorID) VALUES (?, ?)");
        $insertRepresentsStmt->execute([$companyName, $sponsorID]);

        $success = "Sponsoring company and new sponsor added successfully.";

    } catch (PDOException $e) {
        $error = "Error adding sponsoring company: " . $e->getMessage();
    }
}

// Handle form submission for deleting a sponsoring company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sponsor'])) {
    $companyNameToDelete = $_POST['company_name_to_delete'] ?? '';

    try {
        // Delete associated attendees first (in the 'assigned' table)
        $deleteAssignedStmt = $pdo->prepare("DELETE FROM represents WHERE companyName = ?");
        $deleteAssignedStmt->execute([$companyNameToDelete]);

        // Delete the company from the 'company' table
        $deleteCompanyStmt = $pdo->prepare("DELETE FROM company WHERE name = ?");
        $deleteCompanyStmt->execute([$companyNameToDelete]);

        $success = "Sponsoring company and associated attendees deleted successfully.";

    } catch (PDOException $e) {
        $error = "Error deleting sponsoring company: " . $e->getMessage();
    }
}

try {
    // Query to get the company name and sponsorship level along with the sponsor's name
    $sql = "
        SELECT c.name AS company_name, c.level AS sponsorship_level, 
               CONCAT(s.firstname, ' ', s.lastname) AS sponsor_name
        FROM company c
        JOIN represents r ON c.name = r.companyName
        JOIN sponsor s ON r.sponsorID = s.id
        ORDER BY c.name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Error fetching sponsors: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Sponsors</title>
</head>
<body>
    <a href="index.php"><button>Go back home</button></a>
    <h1>Sponsor Information</h1>
    <p>Here are the sponsors and their level of sponsorship:</p>

    <?php
    if ($sponsors) {
        echo "<table border='1'>
                <tr>
                    <th>Company Name</th>
                    <th>Sponsorship Level</th>
                    <th>Representative Name</th>
                </tr>";

        foreach ($sponsors as $sponsor) {
            echo "<tr>
                    <td>" . htmlspecialchars($sponsor['company_name']) . "</td>
                    <td>" . htmlspecialchars($sponsor['sponsorship_level']) . "</td>
                    <td>" . htmlspecialchars($sponsor['sponsor_name']) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No sponsors found.</p>";
    }
    ?>

    <!-- Add New Sponsor Form -->
    <h2>Add New Sponsoring Company</h2>
    <form method="POST">
        <input type="text" name="company_name" placeholder="Company Name" required><br>
        <input type="text" name="sponsorship_level" placeholder="Sponsorship Level" required><br>
        <input type="number" name="sponsor_id" placeholder="Sponsor ID" required><br>
        <input type="text" name="sponsor_first_name" placeholder="Rep First Name" required><br>
        <input type="text" name="sponsor_last_name" placeholder="Rep Last Name" required><br>
        <input type="submit" name="add_sponsor" value="Add Sponsoring Company">
    </form>

    <!-- Delete Sponsoring Company Form -->
    <h2>Delete Sponsoring Company</h2>
    <form method="POST">
        <input type="text" name="company_name_to_delete" placeholder="Company Name to Delete" required><br>
        <input type="submit" name="delete_sponsor" value="Delete Sponsoring Company">
    </form>

    <!-- Display Success/Error Messages -->
    <?php if ($success): ?>
        <p style="color: green"><?= htmlspecialchars($success) ?></p>
    <?php elseif ($error): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</body>
</html>
