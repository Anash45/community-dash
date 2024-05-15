<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
if (isset($_POST['add_event']) && isAdmin()) {

    $title = $_POST['event_title'];
    $description = $_POST['event_description'];
    $date = $_POST['event_date'];
    $time = $_POST['event_time'];
    $checkEvent = addEvent($title, $description, $date, $time);
    if ($checkEvent['success']) {
        header('location: ./events.php?added');
    } else {
        $info = '<div class="alert mb-0 py-2 px-3 alert-'. $class = ($checkEvent['success']) ? 'success' : 'danger'.'">' . $checkEvent['message'] . '</div>';
    }
} elseif (isset($_GET['delete_event']) && isAdmin()) { // Check if update_event form is submitted
    // Retrieve form data
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $event_id = $_GET['delete_event'];

    // Check if the same user already has a event with the same name
    $check_query = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the event name in the database
        $delete_query = "DELETE FROM events WHERE event_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $info = '<div class="alert mb-0 py-2 px-3 alert-success">Event deleted successfully!</div>';
        } else {
            $info = '<div class="alert mb-0 py-2 px-3 alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
} else if (isset($_GET['added'])) {
    $info = '<div class="alert mb-0 py-2 px-3 alert-success">Event added successfully!</div>';
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
                        <h1 class="section-title pt-3 fw-bold text-center text-white">Events</h1>
                        <?php
                        if (isAdmin()) {
                            ?>
                            <button class="btn" data-bs-toggle="modal" data-bs-target="#addEventModal">Add Event</button>
                            <?php
                        }
                        ?>
                    </div>
                    <?php echo $info; ?>
                    <div class="row py-5">
                        <?php
                        $events = getAllEvents();

                        if (!empty($events)) {

                            foreach ($events as $event) {
                                ?>
                                <div class="col-md-4">
                                    <div class="note h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="note-title">
                                                <?php echo $event['event_title']; ?>
                                            </span>
                                            <?php
                                            if (isAdmin()) {
                                                ?>
                                                <a href="?delete_event=<?php echo $event['event_id']; ?>"
                                                    class="bg-transparent px-2 py-1 btn-danger btn-sm"><i
                                                        class="fa fa-trash"></i></a>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="d-flex align-items-center event_date_time justify-content-between">
                                            <span class="d-flex align-items-center gap-2 badge bg-primary"><i
                                                    class="fa fa-calendar"></i><span><?php echo date('d M, Y', strtotime($event['event_date'])); ?></span></span>
                                            <span class="d-flex align-items-center gap-2 badge bg-warning text-dark"><i
                                                    class="fa fa-clock"></i><span><?php echo date('h:i a', strtotime($event['event_time'])); ?></span></span>
                                        </div>
                                        <span class="event_date_time d-flex align-items-center gap-2 mt-1">
                                            <span>Attendees: </span> <span
                                                class="badge bg-success"><?php echo $event['num_attendees']; ?></span>
                                        </span>
                                        <p class="note-description mb-0 mt-2">
                                            <?php
                                            if (strlen($event['event_description']) > 50) {
                                                // Shorten the content to 50 characters
                                                $shortContent = substr($event['event_description'], 0, 50);
                                                // Add the "Read more" link
                                                echo '<span class="pe-2">' . $shortContent . '...</span>' . ' <a href="#" class="text-white" onclick="openContentModal(`' . htmlspecialchars(nl2br($event['event_description'])) . '`)">Read more</a>';
                                            } else {
                                                // If the content is less than or equal to 50 characters, just display it
                                                echo $event['event_description'];
                                            }
                                            ?>
                                        </p>
                                        <a href="eventDetails.php?event_id=<?php echo $event['event_id']; ?>"
                                            class="border-0 text-decoration-none px-2 py-1 btn-primary btn-sm mt-2 d-inline-block">Details</a>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
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
        <div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="contentModalLabel">Note Description</h5>
                        <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <p id="noteContent"></p>
                    </div>
                </div>
            </div>
        </div>
        <script src="./assets/js/script.js"></script>
        <?php
        include './essentials.php';
        if (isset($_GET['task_id'])) {
            ?>
            <script>
                $(document).ready(function () {
                    var task_id = <?php echo $_GET['task_id']; ?>;
                    $('.task[data-id="' + task_id + '"]').click();
                })
            </script>
            <?php
        }
        ?>
    </body>

</html>