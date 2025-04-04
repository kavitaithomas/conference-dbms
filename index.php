<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Organizer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
        <h1>Conference Organizer Portal</h1>
		<?php include 'getdata.php';?>
        <nav>
            <ul>
                <li><a href="subcommittees.php">Subcommittees</a></li>
                <li><a href="hotel_rooms.php">Hotel Rooms</a></li>
                <li><a href="schedule.php">Schedule</a></li>
                <li><a href="sponsors.php">Sponsors</a></li>
                <li><a href="jobs.php">Jobs</a></li>
                <li><a href="attendees.php">Attendees</a></li>
                <li><a href="add_attendee.php">Add Attendee</a></li>
                <li><a href="add_sponsor.php">Add Sponsor</a></li>
            </ul>
        </nav>
    <main>
        <p>Welcome to the Conference Organizer Portal.</p>
    </main>
    <footer>
        <p>Kavita Thomas 2025</p>
    </footer>
</body>
</html>
