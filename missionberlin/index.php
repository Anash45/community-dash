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
    $location = $_POST['event_location'];

    // Check if a file was uploaded
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['event_image'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        // Check file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png');

        if (in_array($fileExt, $allowedExtensions)) {
            // Check file size
            if ($fileSize < 5 * 1024 * 1024) { // 5MB in bytes
                // Generate a unique filename to prevent conflicts
                $newFileName = uniqid('', true) . '.' . $fileExt;

                // Move the file to the uploads folder
                $uploadPath = 'uploads/' . $newFileName;
                move_uploaded_file($fileTmpName, $uploadPath);

                // Add event with image
                $checkEvent = addEvent($title, $description, $date, $time, $location, $uploadPath);

                if ($checkEvent['success']) {
                    header('location: ./events.php?added');
                } else {
                    $info = '<div class="alert mb-0 py-2 px-3 alert-danger">' . $checkEvent['message'] . '</div>';
                }
            } else {
                $info = '<div class="alert mb-0 py-2 px-3 alert-danger">File size exceeds 5MB limit.</div>';
            }
        } else {
            $info = '<div class="alert mb-0 py-2 px-3 alert-danger">Unsupported file format. Only JPG, JPEG, and PNG files are allowed.</div>';
        }
    } else {
        // No file uploaded
        $info = '<div class="alert mb-0 py-2 px-3 alert-danger">Please select an image file.</div>';
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
        <title>MISSIONBERLIN2024</title>
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
                        <h1 class="section-title pt-3 fw-bold text-center text-white">
                            <span class="lang-en">Events</span>
                            <span class="lang-de">Veranstaltungen</span>
                        </h1>
                        <?php
                        if (isAdmin()) {
                            ?>
                            <button class="btn" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                <span class="lang-en">Add Event</span>
                                <span class="lang-de">Veranstaltung hinzufügen</span>
                            </button>
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
                                <div class="col-md-4 mb-md-0 mb-3">
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
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="event_date_time d-flex align-items-center gap-2 mt-1">
                                                <span class="lang-en">Attendees: </span>
                                                <span class="lang-de">Teilnehmer: </span>
                                                <span class="badge bg-success"><?php echo $event['num_attendees']; ?></span>
                                            </span>
                                            <span class="event_date_time d-flex align-items-center gap-2 mt-1">
                                                <i class="fa fa-map-pin text-white"></i>
                                                <span class="badge bg-success"><?php echo $event['event_location']; ?></span>
                                            </span>
                                        </div>
                                        <img src="./<?php echo $event['event_image']; ?>" class="event_img img-fluid" />
                                        <p class="note-description mb-0 mt-2">
                                            <?php
                                            if (strlen($event['event_description']) > 50) {
                                                // Shorten the content to 50 characters
                                                $shortContent = substr($event['event_description'], 0, 50);
                                                // Add the "Read more" link
                                                echo '<span class="pe-2">' . $shortContent . '...</span>' . ' <a href="#" class="text-white" onclick="openContentModal(`' . htmlspecialchars(nl2br($event['event_description'])) . '`)"><span class="lang-en">Read more</span><span class="lang-de">Mehr lesen</span>
                                                </a>';
                                            } else {
                                                // If the content is less than or equal to 50 characters, just display it
                                                echo $event['event_description'];
                                            }
                                            ?>
                                        </p>
                                        <a href="eventDetails.php?event_id=<?php echo $event['event_id']; ?>"
                                            class="border-0 text-decoration-none px-2 py-1 btn-primary btn-sm mt-2 d-inline-block">
                                            <span class="lang-en">Details</span>
                                            <span class="lang-de">Details</span>
                                        </a>
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
                        <h5 class="modal-title" id="addEventModalLabel">
                            <span class="lang-en">Add Event</span>
                            <span class="lang-de">Veranstaltung hinzufügen</span>
                        </h5>
                        <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" method="POST" action="" enctype="multipart/form-data" novalidate>
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">
                                    <span class="lang-en">Event Title</span>
                                    <span class="lang-de">Veranstaltungstitel</span>
                                </label>
                                <input type="text" class="form-control bg-transparent" name="event_title"
                                    id="eventTitle" required>
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
                                    id="eventDescription" required></textarea>
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
                                        id="eventDate" required>
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
                                        id="eventTime" required>
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
                                        id="eventLocation" required>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please enter the event location.</span>
                                        <span class="lang-de">Bitte geben Sie den Veranstaltungsort ein.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label for="eventImage" class="form-label">
                                        <span class="lang-en">Event Image</span>
                                        <span class="lang-de">Veranstaltungsbild</span>
                                    </label>
                                    <input type="file" class="form-control bg-transparent" accept=".png, .jpeg, .jpg"
                                        name="event_image" id="eventImage" required>
                                    <div class="invalid-feedback">
                                        <span class="lang-en">Please select an image (PNG, JPEG, JPG).</span>
                                        <span class="lang-de">Bitte wählen Sie ein Bild (PNG, JPEG, JPG) aus.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn" name="add_event">
                                    <span class="lang-en">Add Event</span>
                                    <span class="lang-de">Veranstaltung hinzufügen</span>
                                </button>
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