<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Organizer</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body class="background">
    <div class="homepage">
        <h1>Welcome to the Conference Organizer Portal</h1>
        
        <!-- Image container -->
        <div class="img-container">
            <img src="main_page.jpeg" alt="Conference Image">
        </div>

        <!-- Navigation menu -->
        <div class="button-container">
                <a href="subcommittees.php">Subcommittees</a>
                <a href="hotel_rooms.php">Hotel Rooms</a>
                <a href="schedule.php">Schedule</a>
                <a href="sponsors.php">Sponsors</a>
                <a href="jobs.php">Jobs</a>
                <a href="attendees.php">Attendees</a>
        </div>
    </div>

</body>
</html>
