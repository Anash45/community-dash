<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}

$user_id = $_SESSION['user_id'];
$info = '';
// Retrieve conversation type and ID from URL
$type = $_GET['type']; // individual or group
$conversation_id = $_GET['id']; // user_id or group_id

// Handle Individual Chat Form Submission
if (isset($_REQUEST['send_message'])) {
    $receiverType = $_GET['type'];
    if ($receiverType == 'individual') {
        $receiver_id = $_GET['id'];
        $message = $conn->real_escape_string($_REQUEST['message']);

        // Insert message into text_messages table
        $sql_isert_message = "INSERT INTO text_messages (sender_id, receiver_id, message_text) VALUES ($user_id, $receiver_id, '$message')";

        if ($conn->query($sql_isert_message) === TRUE) {
            // Message sent successfully
            header("Location:?type=" . $type . "&id=" . $receiver_id);
            exit;
        } else {
            // Error handling
            echo "Error: " . $sql_isert_message . "<br>" . $conn->error;
        }
    } else if ($receiverType == 'group') {
        $group_id = $_GET['id'];
        $message = $_REQUEST['message'];

        // Check if the logged-in user is a member of the group
        $sql_check_membership = "SELECT * FROM group_members WHERE group_id = $group_id AND user_id = $user_id";
        $result_check_membership = $conn->query($sql_check_membership);

        if ($result_check_membership->num_rows > 0) {
            // Insert message into text_messages table
            $sql_insert_group = "INSERT INTO text_messages (sender_id, group_id, message_text) VALUES ($user_id, $group_id, '$message')";
            if ($conn->query($sql_insert_group) === TRUE) {
                // Message sent successfully
                header("Location:?type=" . $type . "&id=" . $group_id);
                exit;
            } else {
                // Error handling
                echo "Error: " . $sql_insert_group . "<br>" . $conn->error;
            }
        } else {
            // User is not a member of the group
            echo "You are not a member of this group.";
        }
    }
}

// Fetch conversation details based on type
if ($type === 'individual') {
    // Fetch user details
    $sql_user = "SELECT name FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $conversation_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_name = $result_user->fetch_assoc()['name'];
} elseif ($type === 'group') {
    // Fetch group details
    $sql_group = "SELECT group_name FROM `groups` WHERE group_id = ?";
    $stmt_group = $conn->prepare($sql_group);
    $stmt_group->bind_param("i", $conversation_id);
    $stmt_group->execute();
    $result_group = $stmt_group->get_result();
    $group_name = $result_group->fetch_assoc()['group_name'];
}

// Fetch messages for the conversation
$sql_messages = "SELECT * FROM text_messages WHERE ";
if ($type === 'individual') {
    $sql_messages .= "(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
} elseif ($type === 'group') {
    $sql_messages .= "group_id = ?";
}
$sql_messages .= " ORDER BY sent_at ASC";

$stmt_messages = $conn->prepare($sql_messages);

if ($type === 'individual') {
    $stmt_messages->bind_param("iiii", $user_id, $conversation_id, $conversation_id, $user_id);
} elseif ($type === 'group') {
    $stmt_messages->bind_param("i", $conversation_id);
}

$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();


$page = 'messages';
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
                    <div class="chat-container">
                        <div class="messages py-3" id="messages">
                            <ul>
                                <?php

                                if ($type === 'individual') {
                                    // Display individual chat messages
                                    while ($row = $result_messages->fetch_assoc()) {
                                        $sender_id = $row['sender_id'];
                                        $sql1 = "SELECT * from users where user_id = $sender_id";
                                        $result1 = $conn->query($sql1);
                                        $row1 = $result1->fetch_assoc();

                                        // Get sender name and message text
                                        $sender_name = $row1['name'];
                                        $message_text = $row['message_text'];
                                        $sent_at = date("h:i a | d M, Y", strtotime($row['sent_at'])); // Format sent_at date
                                        $className = ($sender_id == $user_id) ? 'sender' : 'receiver';
                                        $role = ($row1['role'] == 'admin') ? '<span class="badge ms-3 fs-8 bg-danger">Admin</span>' : '<span class="badge ms-3 fs-8 bg-primary">User</span>';
                                        // Output message in HTML format
                                        echo '<div class="message">';
                                        echo '<div class="message-box ' . $className . '">';
                                        echo '<div class="message-header">';
                                        echo '<span class="fw-bold">' . $sender_name . '</span>';
                                        echo $role;
                                        echo '</div>';
                                        echo '<div class="message-text my-2">';
                                        echo '<p class="mb-0">' . $message_text . '</p>';
                                        echo '</div>';
                                        echo '<div class="message-footer">';
                                        echo '<span>' . $sent_at . '</span>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                } else if ($type === 'group') {
                                    // Display group chat messages
                                    while ($row = $result_messages->fetch_assoc()) {
                                        $sender_id = $row['sender_id'];
                                        $sql1 = "SELECT * from users where user_id = $sender_id";
                                        $result1 = $conn->query($sql1);
                                        $row1 = $result1->fetch_assoc();

                                        // Get sender name and message text
                                        $sender_name = $row1['name'];
                                        $message_text = $row['message_text'];
                                        $sent_at = date("h:i a | d M, Y", strtotime($row['sent_at'])); // Format sent_at date
                                        $className = ($sender_id == $user_id) ? 'sender' : 'receiver';
                                        $role = ($row1['role'] == 'admin') ? '<span class="badge ms-3 fs-8 bg-danger">Admin</span>' : '<span class="badge ms-3 fs-8 bg-primary">User</span>';
                                        
                                        // Output message in HTML format
                                        echo '<div class="message">';
                                        echo '<div class="message-box '.$className.'">';
                                        echo '<div class="message-header">';
                                        echo '<span class="fw-bold">' . $sender_name . '</span>';
                                        echo $role;
                                        echo '</div>';
                                        echo '<div class="message-text my-2">';
                                        echo '<p class="mb-0">' . $message_text . '</p>';
                                        echo '</div>';
                                        echo '<div class="message-footer">';
                                        echo '<span>' . $sent_at . '</span>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }

                                ?>
                            </ul>
                        </div>
                        <form action="?type=<?php echo $type = ($_GET['type'] == 'group') ? 'group' : 'individual';
                        echo '&id=';
                        echo $id = $_GET['id']; ?>" method="post" class="d-flex message-form needs-validation"
                            novalidate>
                            <textarea name="message" required id="message"
                                class="form-control message-input bg-transparent"></textarea>
                            <button type="submit" name="send_message" class="btn btn-primary send-btn"><i
                                    class="fa fa-paper-plane fs-18"></i></button>
                        </form>
                    </div>
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