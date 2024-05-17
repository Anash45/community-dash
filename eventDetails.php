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
    header('location: index.php');
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
        <title>MISSIONBERLIN2024</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="./assets/fontawesome/css/all.css">
        <link rel="stylesheet" href="./assets/css/style.css?v=2">
    </head>

    <body>
        <?php include './header.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <?php include './sidebar.php'; ?>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h1 class="section-title pt-3 fw-bold text-center text-white"><span class="lang-en">Event
                                Details</span> <span class="lang-de">Veranstaltungsdetails</span></h1>
                        <?php
                        if (!$event['is_attending']) {
                            ?>
                            <a class="btn" href="?event_id=<?php echo $event_id; ?>&attend=<?php echo $event_id; ?>"><i
                                    class="fa fa-check"></i><span><span class="lang-en">Attend</span><span
                                        class="lang-de">Teilnehmen</span></span></a>
                            <?php
                        } else {
                            $eventTitle = $event['event_title'];
                            $startTime = date('Ymd', strtotime($event['event_date'])) . 'T' . $event['event_time'];
                            $endTime = date('Ymd', strtotime($event['event_date'])) . 'T' . $event['event_time'];
                            $description = $event['event_description'];
                            $location = $event['event_location'];

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
                                    class="btn border-0 btn-success"><span class="lang-en">Add to Google Calendar</span>
                                    <span class="lang-de">Zu Google Kalender hinzufügen </span>
                                </a>
                                <a class="btn" href="?event_id=<?php echo $event_id; ?>&attend=<?php echo $event_id; ?>"><i
                                        class="fa fa-times"></i><span><span class="lang-en">NOT Attend</span><span
                                            class="lang-de">Nicht teilnehmen </span></span>
                                </a>
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
                                        <a href="index.php?delete_event=<?php echo $event['event_id']; ?>"
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
                                <span class="event_date_time d-flex align-items-center gap-2 mt-3">
                                    <span class="badge bg-info text-dark"><i
                                            class="fa fa-map-pin me-2"></i><?php echo $event['event_location']; ?></span>
                                </span>
                                <?php
                                if ($event['is_recurring']) {
                                    $recurrenceDetails = '';
                                    $recurrenceLimit1 = $event['recurrence_limit'];
                                    if ($event['recurrence_pattern'] === 'daily') {
                                        $recurrenceDetails = 'for '.$recurrenceLimit1.' days';
                                    } elseif ($event['recurrence_pattern'] === 'weekly') {
                                        $recurrenceDetails = 'for the same day of the '.$recurrenceLimit1.' weeks';
                                    } elseif ($event['recurrence_pattern'] === 'monthly') {
                                        $recurrenceDetails = 'for the same date of '.$recurrenceLimit1.' months';
                                    }
                                    ?>
                                    <div class="d-flex gap-2 align-items-center text-info recurring mt-2">
                                        <div><i class="fa fa-redo me-1"></i> <span class="fw-bold">Recurring</span> </div>
                                        <p class="mb-0"><?php echo $recurrenceDetails; ?></p>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="row mt-3">
                                    <div class="col-lg-5 col-md-6 col-12 mb-md-0 mb-3">
                                        <img src="./<?php echo $event['event_image']; ?>" class="event_img img-fluid" />
                                    </div>
                                    <div class="col-lg-7 col-md-6 col-12">
                                        <p class="note-description mb-0 mt-2">
                                            <?php
                                            echo nl2br($event['event_description']);
                                            ?>
                                        </p>
                                    </div>
                                </div>
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
                            echo '<thead><tr><th colspan="3" class="py-3 text-center"><span class="lang-en">Attendees</span><span class="lang-de">Teilnehmer</span></th></tr></thead>';
                            echo '<thead><tr><th><span class="lang-en">Name</span><span class="lang-de">Name</span></th><th><span class="lang-en">Email</span><span class="lang-de">Email</span></th><th><span class="lang-en">City</span><span class="lang-de">Stadt</span></th></tr></thead>';
                            echo '<tbody>';
                            foreach ($attendees as $attendee) {
                                echo '<tr>';
                                echo '<td>' . $attendee['name'] . '</td>';
                                echo '<td>' . $attendee['email'] . '</td>';
                                echo '<td>' . $attendee['city'] . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<span class="lang-en">No attendees found.</span><span class="lang-de">Keine Teilnehmer gefunden.</span>';
                        }
                    }
                    ?>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js?v=2"></script>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>