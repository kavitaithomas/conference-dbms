<?php
include 'config.php';

$selectedCompany = $_POST['company'] ?? null;
$companyJobs = [];
$allJobs = [];

try {
    // Fetch all companies for the dropdown
    $companies = $pdo->query("SELECT name FROM company ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    // If a company was selected, get their jobs
    if ($selectedCompany) {
        $stmt = $pdo->prepare("
            SELECT j.title, j.salary, j.location
            FROM jobAd j
            JOIN posts p ON j.title = p.jobTitle
            WHERE p.companyName = :companyName
        ");
        $stmt->execute(['companyName' => $selectedCompany]);
        $companyJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all jobs
    $stmt = $pdo->query("
        SELECT p.companyName, j.title, j.salary, j.location
        FROM jobAd j
        JOIN posts p ON j.title = p.jobTitle
        ORDER BY p.companyName
    ");
    $allJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

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
    <div class="button-container">
        <a href="index.php">Go back home</a>
    </div>

    <h1>Jobs by Company</h1>
    <form method="post" action="jobs.php">
        <label for="company">Choose a company:</label>
        <select name="company" id="company" required>
            <option value="">--Select--</option>
            <?php foreach ($companies as $c): ?>
                <option value="<?= htmlspecialchars($c['name']) ?>" <?= ($c['name'] === $selectedCompany) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="View Jobs" class="submit-btn">
    </form>
    

    <?php if ($selectedCompany): ?>
        <h2>Jobs posted by <?= htmlspecialchars($selectedCompany) ?></h2>
        <?php if ($companyJobs): ?>
            <table class="data-table">
                <tr><th>Title</th><th>Salary</th><th>Location</th></tr>
                <?php foreach ($companyJobs as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['title']) ?></td>
                        <td><?= htmlspecialchars($job['salary']) ?></td>
                        <td><?= htmlspecialchars($job['location']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No jobs found for this company.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>All Job Listings</h2>
    <?php if ($allJobs): ?>
        <table class="data-table">
            <tr><th>Company</th><th>Title</th><th>Salary</th><th>Location</th></tr>
            <?php foreach ($allJobs as $job): ?>
                <tr>
                    <td><?= htmlspecialchars($job['companyName']) ?></td>
                    <td><?= htmlspecialchars($job['title']) ?></td>
                    <td><?= htmlspecialchars($job['salary']) ?></td>
                    <td><?= htmlspecialchars($job['location']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No jobs available.</p>
    <?php endif; ?>
</body>
</html>
