<?php
require_once './db_conn.php';
require './functions.php';

// if (!isLoggedIn()) {
//     header('location: login.php');
// }
$info = '';
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    if (isset($_GET['attend'])) {
        if (isLoggedIn()) {
            $check = toggleEventAttendance($event_id);
            if ($check['success']) {
                $info = '<div class="alert alert-success" role="alert">' . $check['message'] . '</div>';
                header('refresh:3,url=eventDetails.php?event_id=' . $event_id);
            } else {
                $info = '<div class="alert alert-danger" role="alert">' . $check['message'] . '</div>';
                header('refresh:3,url=eventDetails.php?event_id=' . $event_id);
            }
        } else {
            $info = '<div class="alert alert-danger" role="alert">Login first!</div>';
            header('refresh:3,url=eventDetails.php?event_id=' . $event_id);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_event']) && isAdmin()) {
        $event_title = $_POST['event_title'];
        $event_language = $_POST['event_language'];
        $event_description = $_POST['event_description'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_location = $_POST['event_location'];
        $is_recurring = $_POST['is_recurring'];
        $recurrence_pattern = $_POST['recurrence_pattern'] ?? null;
        $recurrence_limit = ($_POST['recurrence_limit'] !== '' && is_numeric($_POST['recurrence_limit'])) ? $_POST['recurrence_limit'] : null;

        // print_r($_REQUEST);

        // Check if an image was uploaded
        if (!empty($_FILES['event_image']['name'])) {
            $target_dir = "uploads/";
            $imageFileType = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
            $unique_name = uniqid(time() . '_') . '.' . $imageFileType;
            $target_file = $target_dir . $unique_name;

            // Validate image file type
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array($imageFileType, $allowed_types)) {
                $info = '<div class="alert alert-danger">Sorry, only JPG, JPEG, &amp; PNG files are allowed.</div>';
                exit;
            }

            // Move uploaded file to the target directory
            if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
                $info = '<div class="alert alert-danger">Sorry, there was an error uploading your file.</div>';
                exit;
            }

            // If a new image is uploaded, update the image path
            $event_image = $target_file;
            $response = updateEvent($event_id, $event_title, $event_language, $event_description, $event_date, $event_time, $event_location, $event_image, $is_recurring, $recurrence_pattern, $recurrence_limit);
        } else {
            // If no new image is uploaded, retain the old image path
            $event_image = null;
            $response = updateEvent($event_id, $event_title, $event_language, $event_description, $event_date, $event_time, $event_location, $event_image, $is_recurring, $recurrence_pattern, $recurrence_limit);
        }
        if ($response['success']) {
            $info = '<div class="alert mb-0 py-2 px-3 alert-success">' . $response['message'] . '</div>';
        } else {
            $info = '<div class="alert mb-0 py-2 px-3 alert-danger">' . $response['message'] . '</div>';
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
                    <div class="d-flex align-items-center mt-1 justify-content-between">
                        <h1 class="section-title mb-0 fw-bold text-center text-white"><span class="lang-en">Event
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
                    <div class="mt-3 mb-1">
                        <?php
                        if (isAdmin()) {
                            ?>
                            <button class="btn" data-bs-toggle="modal" data-bs-target="#editEventModal">
                                <span class="lang-en">Edit Event</span>
                                <span class="lang-de">Veranstaltung bearbeiten</span>
                            </button>
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
                                        $recurrenceDetails = '<span class="lang-en">for ' . $recurrenceLimit1 . ' days</span>';
                                        $recurrenceDetails .= '<span class="lang-de">für ' . $recurrenceLimit1 . ' Tage</span>';
                                    } elseif ($event['recurrence_pattern'] === 'weekly') {
                                        $recurrenceDetails = '<span class="lang-en">for the same day of the ' . $recurrenceLimit1 . ' weeks</span>';
                                        $recurrenceDetails .= '<span class="lang-de">für den gleichen Tag der ' . $recurrenceLimit1 . ' Wochen</span>';
                                    } elseif ($event['recurrence_pattern'] === 'monthly') {
                                        $recurrenceDetails = '<span class="lang-en">for the same date of ' . $recurrenceLimit1 . ' months</span>';
                                        $recurrenceDetails .= '<span class="lang-de">für den gleichen Tag des ' . $recurrenceLimit1 . ' Monats</span>';
                                    }
                                    ?>
                                    <div class="d-flex gap-2 align-items-center text-info recurring mt-2">
                                        <div><i class="fa fa-redo me-1"></i> <span class="fw-bold"><span
                                                    class="lang-en">Recurring</span><span
                                                    class="lang-de">Wiederkehrend</span></span> </div>
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
        <!-- Modal -->
        <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="editEventModalLabel">
                            <span class="lang-en">Edit Event</span>
                            <span class="lang-de">Veranstaltung bearbeiten</span>
                        </h5>
                        <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" method="POST"
                            action="eventDetails.php?event_id=<?php echo $event_id; ?>" enctype="multipart/form-data"
                            novalidate>
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">
                                    <span class="lang-en">Event Title</span>
                                    <span class="lang-de">Veranstaltungstitel</span>
                                </label>
                                <input type="text" class="form-control bg-transparent" name="event_title"
                                    id="eventTitle" value="<?php echo htmlspecialchars($event['event_title']); ?>"
                                    required>
                                <div class="invalid-feedback">
                                    <span class="lang-en">Please enter an event title.</span>
                                    <span class="lang-de">Bitte geben Sie einen Veranstaltungstitel ein.</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="eventLanguage" class="form-label">
                                    <span class="lang-en">Language</span>
                                    <span class="lang-de">Language</span>
                                </label>
                                <select class="form-control bg-transparent" name="event_language" id="eventLanguage"
                                    required>
                                    <option value="en" <?php if ($event['language'] == 'en')
                                        echo 'selected'; ?>>English
                                    </option>
                                    <option value="de" <?php if ($event['language'] == 'de')
                                        echo 'selected'; ?>>German
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    <span class="lang-en">Please enter an event title.</span>
                                    <span class="lang-de">Bitte geben Sie einen Veranstaltungstitel ein.</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="eventDescription" class="form-label">
                                    <span class="lang-en">Event Description</span>
                                    <span class="lang-de">Veranstaltungsbeschreibung</span>
                                </label>
                                <textarea class="form-control bg-transparent" name="event_description"
                                    id="eventDescription"
                                    required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                                <div class="invalid-feedback">
                                    <span class="lang-en">Please enter an event description.</span>
                                    <span class="lang-de">Bitte geben Sie eine Veranstaltungsbeschreibung ein.</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="eventDate" class="form-label">
                                        <span class="lang-en">Event Date</span>
                                        <span class="lang-de">Veranstaltungsdatum</span>
                                    </label>
                                    <input type="date" class="form-control bg-transparent" name="event_date"
                                        id="eventDate" value="<?php echo htmlspecialchars($event['event_date']); ?>"
                                        required>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please select an event date.</span>
                                        <span class="lang-de">Bitte wählen Sie ein Veranstaltungsdatum aus.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="eventTime" class="form-label">
                                        <span class="lang-en">Event Time</span>
                                        <span class="lang-de">Veranstaltungszeit</span>
                                    </label>
                                    <input type="time" class="form-control bg-transparent" name="event_time"
                                        id="eventTime" value="<?php echo htmlspecialchars($event['event_time']); ?>"
                                        required>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please select an event time.</span>
                                        <span class="lang-de">Bitte wählen Sie eine Veranstaltungszeit aus.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="eventLocation" class="form-label">
                                        <span class="lang-en">Event Location</span>
                                        <span class="lang-de">Veranstaltungsort</span>
                                    </label>
                                    <input type="text" class="form-control bg-transparent" name="event_location"
                                        id="eventLocation"
                                        value="<?php echo htmlspecialchars($event['event_location']); ?>" required>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please enter the event location.</span>
                                        <span class="lang-de">Bitte geben Sie den Veranstaltungsort ein.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="eventImage" class="form-label">
                                        <span class="lang-en">Event Image</span>
                                        <span class="lang-de">Veranstaltungsbild</span>
                                    </label>
                                    <div class="my-1">
                                        <img src="<?php echo $event['event_image']; ?>" height="100" class="rounded"
                                            id="event_image_display" alt="Uploaded Image">
                                    </div>
                                    <input type="file" class="form-control bg-transparent"
                                        onchange="imageDisplay(event)" accept=".png, .jpeg, .jpg" name="event_image"
                                        id="eventImage">
                                    <p class="text-muted mb-0"><small><span class="lang-en">Only upload the image if you
                                                want to update. Otherwise leave it.</span> <span class="lang-de">Laden
                                                Sie das Bild nur hoch, wenn Sie es aktualisieren möchten. Ansonsten lass
                                                es.</span></small></p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">
                                        <span class="lang-en">Is Recurring?</span>
                                        <span class="lang-de">Wiederholt sich?</span>
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_recurring"
                                            id="isRecurringNo" value="0" <?php if ($event['is_recurring'] == 0)
                                                echo 'checked'; ?> required>
                                        <label class="form-check-label" for="isRecurringNo">
                                            <span class="lang-en">No</span>
                                            <span class="lang-de">Nein</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_recurring"
                                            id="isRecurringYes" value="1" <?php if ($event['is_recurring'] == 1)
                                                echo 'checked'; ?> required>
                                        <label class="form-check-label" for="isRecurringYes">
                                            <span class="lang-en">Yes</span>
                                            <span class="lang-de">Ja</span>
                                        </label>
                                    </div>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please select if the event is recurring.</span>
                                        <span class="lang-de">Bitte wählen Sie, ob sich die Veranstaltung
                                            wiederholt.</span>
                                    </div>
                                </div>
                            </div>
                            <div id="recurrence_sec"
                                style="display: <?php echo ($event['is_recurring'] == 1) ? 'block' : 'none'; ?>;">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">
                                            <span class="lang-en">Recurrence Pattern</span>
                                            <span class="lang-de">Wiederholungsmuster</span>
                                        </label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recurrence_pattern"
                                                id="recurrencePatternDaily" value="daily" <?php if ($event['recurrence_pattern'] == 'daily')
                                                    echo 'checked'; ?>>
                                            <label class="form-check-label" for="recurrencePatternDaily">
                                                <span class="lang-en">Daily</span>
                                                <span class="lang-de">Täglich</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recurrence_pattern"
                                                id="recurrencePatternWeekly" value="weekly" <?php if ($event['recurrence_pattern'] == 'weekly')
                                                    echo 'checked'; ?>>
                                            <label class="form-check-label" for="recurrencePatternWeekly">
                                                <span class="lang-en">Weekly</span>
                                                <span class="lang-de">Wöchentlich</span>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recurrence_pattern"
                                                id="recurrencePatternMonthly" value="monthly" <?php if ($event['recurrence_pattern'] == 'monthly')
                                                    echo 'checked'; ?>>
                                            <label class="form-check-label" for="recurrencePatternMonthly">
                                                <span class="lang-en">Monthly</span>
                                                <span class="lang-de">Monatlich</span>
                                            </label>
                                        </div>
                                        <div class="invalid-feedback">
                                            <span class="lang-en">Please select a recurrence pattern.</span>
                                            <span class="lang-de">Bitte wählen Sie ein Wiederholungsmuster.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label for="recurrenceLimit" class="form-label">
                                            <span class="lang-en">Recurrence Limit</span>
                                            <span class="lang-de">Wiederholungslimit</span>
                                        </label>
                                        <input type="number" class="form-control bg-transparent" name="recurrence_limit"
                                            id="recurrenceLimit"
                                            value="<?php echo htmlspecialchars($event['recurrence_limit']); ?>">
                                        <div class="invalid-feedback">
                                            <span class="lang-en">Please enter a valid recurrence limit.</span>
                                            <span class="lang-de">Bitte geben Sie ein gültiges Wiederholungslimit
                                                ein.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn" name="edit_event">
                                    <span class="lang-en">Edit Event</span>
                                    <span class="lang-de">Veranstaltung bearbeiten</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="./assets/js/script.js?v=2"></script>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>