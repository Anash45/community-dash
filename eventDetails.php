<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    if (isset($_GET['attend'])) {
        $check = toggleEventAttendance($event_id);
        if ($check['success']) {
            $info = '<div class="alert alert-success" role="alert">' . $check['message'] . '</div>';
            header('refresh:3,url=eventDetails.php?event_id=' . $event_id);
        } else {
            $info = '<div class="alert alert-danger" role="alert">' . $check['message'] . '</div>';
            header('refresh:3,url=eventDetails.php?event_id=' . $event_id);
        }
    }
    $event = getEventById($event_id);
} else {
    header('location: events.php');
}
$page = 'events';
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.84.0">
        <title>TeamForceConnect</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="./assets/fontawesome/css/all.css">
        <link rel="stylesheet" href="./assets/css/style.css?v=1">
    </head>

    <body>
        <?php include './header.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <?php include './sidebar.php'; ?>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h1 class="section-title pt-3 fw-bold text-center text-white">Event Details</h1>
                        <?php
                        if (!$event['is_attending']) {
                            ?>
                            <a class="btn" href="?event_id=<?php echo $event_id; ?>&attend=<?php echo $event_id; ?>"><i
                                    class="fa fa-check"></i><span>Attend</span></a>
                            <?php
                        } else {
                            $eventTitle = $event['event_title'];
                            $startTime = date('Ymd', strtotime($event['event_date'])) . 'T' . $event['event_time'];
                            $endTime = date('Ymd', strtotime($event['event_date'])) . 'T' . $event['event_time'];
                            $description = $event['event_description'];
                            $location = "Remote";

                            // Construct the Google Calendar event URL
                            $googleCalendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
                            $googleCalendarUrl .= "&text=" . urlencode($eventTitle);
                            $googleCalendarUrl .= "&dates=" . urlencode($startTime . "/" . $endTime);
                            $googleCalendarUrl .= "&details=" . urlencode($description);
                            $googleCalendarUrl .= "&location=" . urlencode($location);
                            $googleCalendarUrl .= "&sf=true"; // Show event details in the form
                            $googleCalendarUrl .= "&output=xml"; // Output format
                            $googleCalendarUrl .= "&add=true"; // Add the event to the calendar
                        
                            // Output the link
                        
                            ?>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?php echo $googleCalendarUrl; ?>" target="_blank"
                                    class="btn border-0 btn-success">Add to Google Calendar</a>
                                <a class="btn" href="?event_id=<?php echo $event_id; ?>&attend=<?php echo $event_id; ?>"><i
                                        class="fa fa-times"></i><span>NOT Attend</span></a>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php echo $info; ?>
                    <div class="row py-4">
                        <?php
                        if (!empty($event)) {
                            ?>
                            <div class="note h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="note-title">
                                        <?php echo $event['event_title']; ?>
                                    </span>
                                    <?php
                                    if (isAdmin()) {
                                        ?>
                                        <a href="events.php?delete_event=<?php echo $event['event_id']; ?>"
                                            class="bg-transparent px-2 py-1 btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="d-flex align-items-center event_date_time gap-4">
                                    <span class="d-flex align-items-center gap-2 badge bg-primary"><i
                                            class="fa fa-calendar"></i><span><?php echo date('d M, Y', strtotime($event['event_date'])); ?></span></span>
                                    <span class="d-flex align-items-center gap-2 badge bg-warning text-dark"><i
                                            class="fa fa-clock"></i><span><?php echo date('h:i a', strtotime($event['event_time'])); ?></span></span>
                                </div>
                                <p class="note-description mb-0 mt-2">
                                    <?php
                                    echo nl2br($event['event_description']);
                                    ?>
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    // Check if the user is an admin (assuming isAdmin function checks user role)
                    if (isAdmin()) {
                        // Get the event ID from the URL parameter or any other source
                        $event_id = $_GET['event_id']; // Assuming the event_id is passed in the URL
                    
                        // Get the details of all attendees for the event
                        $attendees = getEventAttendees($event_id);

                        // Check if there are any attendees
                        if (!empty($attendees)) {
                            // Display the attendees' details in a Bootstrap 5 dark table
                            echo '<table class="table table-dark mt-4">';
                            echo '<thead><tr><th colspan="2" class="py-3 text-center">Attendees</th></tr></thead>';
                            echo '<thead><tr><th>Name</th><th>Email</th></tr></thead>';
                            echo '<tbody>';
                            foreach ($attendees as $attendee) {
                                echo '<tr>';
                                echo '<td>' . $attendee['name'] . '</td>';
                                echo '<td>' . $attendee['email'] . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo 'No attendees found.';
                        }
                    }
                    ?>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <!-- Modal -->
        <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="addEventModalLabel">Add Event</h5>
                        <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" method="POST" action="" novalidate>
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">Event Title</label>
                                <input type="text" class="form-control bg-transparent" name="event_title"
                                    id="eventTitle" required>
                                <div class="invalid-feedback">Please enter an event title.</div>
                            </div>
                            <div class="mb-3">
                                <label for="eventDescription" class="form-label">Event Description</label>
                                <textarea class="form-control bg-transparent" name="event_description"
                                    id="eventDescription" required></textarea>
                                <div class="invalid-feedback">Please enter an event description.</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="eventDate" class="form-label">Event Date</label>
                                    <input type="date" class="form-control bg-transparent" name="event_date"
                                        id="eventDate" required>
                                    <div class="invalid-feedback">Please select an event date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="eventTime" class="form-label">Event Time</label>
                                    <input type="time" class="form-control bg-transparent" name="event_time"
                                        id="eventTime" required>
                                    <div class="invalid-feedback">Please select an event time.</div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn" name="add_event">Add Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="./assets/js/script.js"></script>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>