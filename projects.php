<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
// Check if the add project form is submitted
if (isset($_POST['add_board'])) {
    // Retrieve form data
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $board_name = $_POST['board_name'];

    // Check if the same user already has a board with the same name
    $check_query = "SELECT * FROM boards WHERE user_id = ? AND board_name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("is", $user_id, $board_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $info = '<div class="alert alert-danger">"Error: You already have a board with the same name.</div>';
    } else {
        // Get the maximum board_order for the user's existing boards
        $max_order_query = "SELECT MAX(board_order) AS max_order FROM boards WHERE user_id = ?";
        $stmt = $conn->prepare($max_order_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $max_order_result = $stmt->get_result()->fetch_assoc();
        $max_order = $max_order_result['max_order'];

        // Increment the maximum board_order by 1
        $new_order = $max_order + 1;

        // Insert board data into database with the calculated board_order
        $insert_query = "INSERT INTO boards (user_id, board_name, board_order) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isi", $user_id, $board_name, $new_order);
        if ($stmt->execute()) {
            $info = '<div class="alert alert-success">Project added successfully!</div>';
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
}
if (isset($_GET['delete'])) {
    $info = '<div class="alert alert-success">Project deleted successfully.</div>';
}
$page = 'projects';
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
                    <h1 class="section-title pt-3 fw-bold text-center text-white">Projects</h1>
                    <?php echo $info; ?>
                    <div class="row py-5">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $boards = getAllBoards($user_id);

                        if (!empty($boards)) {

                            foreach ($boards as $board) {
                                ?>
                                <div class="col-md-4">
                                    <a href="tasks.php?board_id=<?php echo $board['board_id']; ?>"
                                        class="task d-flex align-items-center justify-content-between text-decoration-none">
                                        <span class="task-title">
                                            <?php echo $board['board_name']; ?>
                                        </span>
                                        <span class="badge bg-primary"><?php echo $board['num_tasks']; ?> Tasks</span>
                                    </a>
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
        <div class="modal fade" id="addBoardModal" tabindex="-1" aria-labelledby="addBoardModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="addBoardModalLabel">Add project</h5>
                        <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" method="POST" action="" novalidate>
                            <div class="mb-3">
                                <label for="boardName" class="form-label">Project Name</label>
                                <input type="text" class="form-control bg-transparent" name="board_name" id="boardName"
                                    required>
                                <div class="invalid-feedback">Please enter a project name.</div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn" name="add_board">Add</button>
                            </div>
                        </form>
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