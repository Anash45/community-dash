<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
if (isset($_GET['delete_note'])) { // Check if update_note form is submitted
    // Retrieve form data
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $note_id = $_GET['delete_note'];

    // Check if the same user already has a note with the same name
    $check_query = "SELECT * FROM notes WHERE user_id = ? AND note_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $note_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the note name in the database
        $delete_query = "DELETE FROM notes WHERE note_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $note_id);
        if ($stmt->execute()) {
            $info = '<div class="alert alert-success">Note deleted successfully!</div>';
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
}
$page = 'notes';
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
                    <h1 class="section-title pt-3 fw-bold text-center text-white">Notes</h1>
                    <?php echo $info; ?>
                    <div class="row py-5">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $notes = getAllNotes($user_id);

                        if (!empty($notes)) {

                            foreach ($notes as $note) {
                                ?>
                                <div class="col-md-4">
                                    <div class="note h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="note-title">
                                                <?php echo $note['title']; ?>
                                            </span>
                                            <a href="?delete_note=<?php echo $note['note_id']; ?>"
                                                class="bg-transparent px-2 py-1 btn-danger btn-sm"><i
                                                    class="fa fa-trash"></i></a>
                                        </div>
                                        <p class="note-description mb-0">
                                            <?php
                                            if (strlen($note['content']) > 50) {
                                                // Shorten the content to 50 characters
                                                $shortContent = substr($note['content'], 0, 50);
                                                // Add the "Read more" link
                                                echo '<span class="pe-2">'.$shortContent.'...</span>' . ' <a href="#" class="text-white" onclick="openContentModal(`' . htmlspecialchars(nl2br($note['content'])) . '`)">Read more</a>';
                                            } else {
                                                // If the content is less than or equal to 50 characters, just display it
                                                echo $note['content'];
                                            }
                                            ?>
                                        </p>
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