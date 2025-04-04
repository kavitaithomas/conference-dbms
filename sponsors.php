<?php
include 'config.php';

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
</body>
</html>
