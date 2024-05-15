<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}
if (isset($_GET['board_id'])) {
    $board_id = $_GET['board_id'];
} else {
    header('location:index.php');
}
$info = '';
// print_r($board);
// Check if add_task form is submitted
if (isset($_POST['add_task'])) {
    $user_id = $_SESSION['user_id'];
    // Retrieve form data
    $board_id = $_GET['board_id']; // Assuming board_id is passed in the URL
    $task_title = $_POST['task_title'];
    $description = $_POST['description'];
    $deadline_date = $_POST['deadline_date'];
    $mood_perception = $_POST['mood_perception'];
    $task_status = $_POST['task_status'];

    // Check if the same task already exists in the same board
    $check_query = "SELECT * FROM tasks WHERE board_id = ? AND task_title = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("is", $board_id, $task_title);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the same task already exists in the same board
    if ($result->num_rows > 0) {
        $info = '<div class="alert alert-danger">Error: The same task already exists in this board.</div>';
    } else {
        // Insert task data into database
        $insert_query = "INSERT INTO tasks (task_title, description, deadline_date, mood_perception, task_status, board_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssissi", $task_title, $description, $deadline_date, $mood_perception, $task_status, $board_id, $user_id);
        if ($stmt->execute()) {
            $info = '<div class="alert alert-success">Task added successfully!</div>';
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
} elseif (isset($_POST['update_task'])) {
    // Retrieve form data
    $task_id = $_POST['task_id'];
    $task_title = $_POST['task_title'];
    $description = $_POST['description'];
    $deadline_date = $_POST['deadline_date'];
    $mood_perception = $_POST['mood_perception'];
    $task_status = $_POST['task_status'];

    $board_id = $_GET['board_id'];

    // Check if any other task in the same board has the same name
    $check_query = "SELECT * FROM tasks WHERE board_id = ? AND task_title = ? AND task_id != ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("isi", $board_id, $task_title, $task_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // If any other task in the same board has the same name
    if ($result_check->num_rows > 0) {
        $info = '<div class="alert alert-danger">Error: Another task in the same board already has the same name.</div>';
    } else {
        // Update task data in the database
        $update_query = "UPDATE tasks SET task_title = ?, description = ?, deadline_date = ?, mood_perception = ?, task_status = ? WHERE task_id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("sssisi", $task_title, $description, $deadline_date, $mood_perception, $task_status, $task_id);
        if ($stmt_update->execute()) {
            $info = '<div class="alert alert-succcess">Task updated successfully!</div>';
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
} elseif (isset($_POST['update_board'])) { // Check if update_board form is submitted
    // Retrieve form data
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $board_id = $_GET['board_id'];
    $new_board_name = $_POST['board_name'];

    // Check if the same user already has a board with the same name
    $check_query = "SELECT * FROM boards WHERE user_id = ? AND board_name = ? AND board_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("isi", $user_id, $new_board_name, $board_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If board with the same name already exists for the same user
    if ($result->num_rows > 0) {
        $info = '<div class="alert alert-danger">Error: You already have a board with the same name.</div>';
    } else {
        // Update the board name in the database
        $update_query = "UPDATE boards SET board_name = ? WHERE board_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_board_name, $board_id);
        if ($stmt->execute()) {
            $info = '<div class="alert alert-success">Board updated successfully!</div>';
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
} elseif (isset($_POST['delete_board'])) { // Check if update_board form is submitted
    // Retrieve form data
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $board_id = $_GET['board_id'];
    $new_board_name = $_POST['board_name'];

    // Check if the same user already has a board with the same name
    $check_query = "SELECT * FROM boards WHERE user_id = ? AND board_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $board_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If board with the same name already exists for the same user
    if ($result->num_rows > 0) {
        // Update the board name in the database
        $delete_query = "DELETE FROM boards WHERE board_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $board_id);
        if ($stmt->execute()) {
            header('location:projects.php?delete');
        } else {
            $info = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    } else {
        $info = '<div class="alert alert-danger">Error: You don\'t have permission to delete this board.</div>';
    }
}
$board = getBoardById($board_id);
$page = 'tasks';
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
                    <h1 class="section-title pt-3 text-white fw-bold"><?php echo $board['board_name']; ?></h1>
                    <?php echo $info; ?>
                    <div class="row py-5">
                        <div class="col-lg-4">
                            <div class="d-flex gap-2 align-items-center justify-content-between task-info">
                                <div class="d-flex align-items-center fw-semibold gap-2">
                                    <span class="fa fa-circle text-danger"></span>
                                    <span>Todo</span>
                                    <span class="task-counter" id="count_todo">(0)</span>
                                </div>
                                <span class="sortButton">
                                    <i class="fal fa-arrows-v"></i>
                                </span>
                            </div>
                            <?php
                            // Fetch all todo tasks from the database
                            $query1 = "SELECT * FROM tasks WHERE task_status = 'todo' AND board_id = '$board_id' ORDER BY mood_perception DESC";
                            $result1 = $conn->query($query1);

                            echo '<div class="tasks" data-status="todo">';
                            if ($result1->num_rows > 0) {
                                // Display tasks
                                while ($row1 = $result1->fetch_assoc()) {
                                    // Get mood perception color based on the mood perception value
                                    $mood_perception_color = '';
                                    $mood = '';
                                    switch ($row1['mood_perception']) {
                                        case 1:
                                            $mood = 'Low';
                                            $mood_perception_color = 'bg-warning';
                                            break;
                                        case 2:
                                            $mood = 'Medium';
                                            $mood_perception_color = 'bg-info';
                                            break;
                                        case 3:
                                            $mood = 'High';
                                            $mood_perception_color = 'bg-danger';
                                            break;
                                        default:
                                            $mood_perception_color = 'bg-secondary';
                                            break;
                                    }

                                    // Display task details
                                    echo '<div class="task" data-id="' . $row1['task_id'] . '" data-priority="' . $row1['mood_perception'] . '" onclick="populateEditTask(`' . $row1['task_title'] . '`,`' . htmlspecialchars($row1['description']) . '`,`' . $row1['deadline_date'] . '`,`' . $row1['mood_perception'] . '`,`' . $row1['task_status'] . '`,`' . $row1['task_id'] . '`)">';
                                    echo '<span class="task-title">' . $row1['task_title'] . '</span>';
                                    echo '<div class="date-mood"><span class="task-date">' . date('d M, Y', strtotime($row1['deadline_date'])) . '</span>';
                                    echo '<span class="badge ' . $mood_perception_color . '">' . $mood . '</span></div>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                            ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex gap-2 align-items-center justify-content-between task-info">
                                <div class="d-flex align-items-center fw-semibold gap-2">
                                    <span class="fa fa-circle text-primary"></span>
                                    <span>In Progress</span>
                                    <span class="task-counter" id="count_in_progress">(0)</span>
                                </div>
                                <span class="sortButton">
                                    <i class="fal fa-arrows-v"></i>
                                </span>
                            </div>
                            <?php
                            // Fetch all todo tasks from the database
                            $query1 = "SELECT * FROM tasks WHERE task_status = 'in-progress' AND board_id = '$board_id' ORDER BY mood_perception DESC";
                            $result1 = $conn->query($query1);

                            echo '<div class="tasks" data-status="in-progress">';
                            if ($result1->num_rows > 0) {
                                // Display tasks
                                while ($row1 = $result1->fetch_assoc()) {
                                    // Get mood perception color based on the mood perception value
                                    $mood_perception_color = '';
                                    $mood = '';
                                    switch ($row1['mood_perception']) {
                                        case 1:
                                            $mood = 'Low';
                                            $mood_perception_color = 'bg-warning';
                                            break;
                                        case 2:
                                            $mood = 'Medium';
                                            $mood_perception_color = 'bg-info';
                                            break;
                                        case 3:
                                            $mood = 'High';
                                            $mood_perception_color = 'bg-danger';
                                            break;
                                        default:
                                            $mood_perception_color = 'bg-secondary';
                                            break;
                                    }

                                    // Display task details
                                    echo '<div class="task" data-id="' . $row1['task_id'] . '" data-priority="' . $row1['mood_perception'] . '" onclick="populateEditTask(`' . $row1['task_title'] . '`,`' . htmlspecialchars($row1['description']) . '`,`' . $row1['deadline_date'] . '`,`' . $row1['mood_perception'] . '`,`' . $row1['task_status'] . '`,`' . $row1['task_id'] . '`)">';
                                    echo '<span class="task-title">' . $row1['task_title'] . '</span>';
                                    echo '<div class="date-mood"><span class="task-date">' . date('d M, Y', strtotime($row1['deadline_date'])) . '</span>';
                                    echo '<span class="badge ' . $mood_perception_color . '">' . $mood . '</span></div>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                            ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex gap-2 align-items-center justify-content-between task-info">
                                <div class="d-flex align-items-center fw-semibold gap-2">
                                    <span class="fa fa-circle text-success"></span>
                                    <span>Completed</span>
                                    <span class="task-counter" id="count_completed">(0)</span>
                                </div>
                                <span class="sortButton">
                                    <i class="fal fa-arrows-v"></i>
                                </span>
                            </div>
                            <?php
                            // Fetch all completed tasks from the database
                            $query1 = "SELECT * FROM tasks WHERE task_status = 'completed' AND board_id = '$board_id' ORDER BY mood_perception DESC";
                            $result1 = $conn->query($query1);

                            echo '<div class="tasks" data-status="completed">';
                            if ($result1->num_rows > 0) {
                                // Display tasks
                                while ($row1 = $result1->fetch_assoc()) {
                                    // Get mood perception color based on the mood perception value
                                    $mood_perception_color = '';
                                    $mood = '';
                                    switch ($row1['mood_perception']) {
                                        case 1:
                                            $mood = 'Low';
                                            $mood_perception_color = 'bg-warning';
                                            break;
                                        case 2:
                                            $mood = 'Medium';
                                            $mood_perception_color = 'bg-info';
                                            break;
                                        case 3:
                                            $mood = 'High';
                                            $mood_perception_color = 'bg-danger';
                                            break;
                                        default:
                                            $mood_perception_color = 'bg-secondary';
                                            break;
                                    }

                                    // Display task details
                                    echo '<div class="task" data-id="' . $row1['task_id'] . '" data-priority="' . $row1['mood_perception'] . '" onclick="populateEditTask(`' . $row1['task_title'] . '`,`' . htmlspecialchars($row1['description']) . '`,`' . $row1['deadline_date'] . '`,`' . $row1['mood_perception'] . '`,`' . $row1['task_status'] . '`,`' . $row1['task_id'] . '`)">';
                                    echo '<span class="task-title">' . $row1['task_title'] . '</span>';
                                    echo '<div class="date-mood"><span class="task-date">' . date('d M, Y', strtotime($row1['deadline_date'])) . '</span>';
                                    echo '<span class="badge ' . $mood_perception_color . '">' . $mood . '</span></div>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                            ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <?php
        // Check if board_id is set
        if (isset($_GET['board_id'])) {
            $board_id = $_GET['board_id'];

            // Retrieve board details from the database based on board_id
            $query = "SELECT * FROM boards WHERE board_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $board_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if board exists
            if ($result->num_rows > 0) {
                $board_data = $result->fetch_assoc();
                $board_name = $board_data['board_name'];
                // Populate the modal with the board details
                echo '<div class="modal fade" id="editBoardModal" tabindex="-1" aria-labelledby="editBoardModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title" id="editBoardModalLabel">Edit Project</h5>
                                <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </span>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" method="POST" action="?board_id=' . $board_id . '" novalidate>
                                    <div class="mb-3">
                                        <label for="boardName" class="form-label">Board Name</label>
                                        <input type="text" class="form-control bg-transparent" name="board_name" id="boardName"
                                            required value="' . $board_name . '">
                                        <div class="invalid-feedback">Please enter a board name.</div>
                                    </div>
                                    <div class="text-center d-flex align-items-center gap-2">
                                        <button type="submit" class="btn btn-danger" name="delete_board" onclick="return confirm(\'Do you really want to delete this?\');">Delete</button>
                                        <button type="submit" class="btn" name="update_board">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
                echo '<!-- Add Task Modal -->
                <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                                <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </span>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" method="POST" action="?board_id=' . $board_id . '" novalidate>
                                    <div class="mb-3">
                                        <label for="taskTitle" class="form-label">Task Title</label>
                                        <input type="text" class="form-control bg-transparent" id="taskTitle" name="task_title" required>
                                        <div class="invalid-feedback">Please enter a task title.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="taskDescription" class="form-label">Description</label>
                                        <textarea class="form-control bg-transparent" id="taskDescription" required name="description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deadlineDate" class="form-label">Deadline Date</label>
                                        <input type="date" class="form-control bg-transparent" id="deadlineDate" required name="deadline_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="moodPerception" class="form-label">Mood Perception</label>
                                        <select class="form-select form-control bg-transparent" id="moodPerception" required name="mood_perception">
                                            <option value="">Select priority...</option>
                                            <option value="1">Low</option>
                                            <option value="2">Medium</option>
                                            <option value="3">High</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="taskStatus" class="form-label">Task Status</label>
                                        <select class="form-select form-control bg-transparent" id="taskStatus" required name="task_status">
                                            <option value="">Select status...</option>
                                            <option value="todo">To Do</option>
                                            <option value="in-progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" name="add_task">Add Task</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
                echo '<!-- Edit Task Modal -->
                <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                                <span type="button" class="p-2" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </span>
                            </div>
                            <div class="modal-body">
                                <form class="needs-validation" method="POST" action="?board_id=' . $board_id . '" novalidate>
                                <input type="hidden" name="task_id" id="ed_task_id">
                                    <div class="mb-3">
                                        <label for="taskTitle" class="form-label">Task Title</label>
                                        <input type="text" class="form-control bg-transparent" id="ed_taskTitle" name="task_title" required>
                                        <div class="invalid-feedback">Please enter a task title.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="taskDescription" class="form-label">Description</label>
                                        <textarea class="form-control bg-transparent" id="ed_taskDescription" required name="description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deadlineDate" class="form-label">Deadline Date</label>
                                        <input type="date" class="form-control bg-transparent" id="ed_deadlineDate" required name="deadline_date">
                                    </div>
                                    <div class="mb-3">
                                        <label for="moodPerception" class="form-label">Mood Perception</label>
                                        <select class="form-select form-control bg-transparent" id="ed_moodPerception" required name="mood_perception">
                                            <option value="">Select priority...</option>
                                            <option value="1">Low</option>
                                            <option value="2">Medium</option>
                                            <option value="3">High</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="taskStatus" class="form-label">Task Status</label>
                                        <select class="form-select form-control bg-transparent" id="ed_taskStatus" required name="task_status">
                                            <option value="">Select status...</option>
                                            <option value="todo">To Do</option>
                                            <option value="in-progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary" name="update_task">Update Task</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
        }

        ?>
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