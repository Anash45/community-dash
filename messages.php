<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}

$user_id = $_SESSION['user_id'];
$info = '';
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {
    // Get the message from the form
    $message = $_POST["message"];
    // Call the function to insert the message into the database
    if (insertMessage($message, $user_id)) {
        header('location: messages.php');
    }
}

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
                    <div class="chat-container">
                        <div class="messages py-3" id="messages">
                            <?php
                            $messages = getAllMessages();
                            $newMsg = false;
                            if (!empty($messages)) {
                                foreach ($messages as $message) {
                                    $user = getUserById($message['user_id']);
                                    $lastUnreadMessage = getLastUnread();
                                    if ($lastUnreadMessage != null && ($lastUnreadMessage + 1) == $message['message_id'] && $newMsg == false) {
                                        echo '<p class="new-messages text-center" id="new-messages"><span>New Messages</span></p>';
                                        $newMsg = true;
                                    }
                                    readMessage($message['message_id']);
                                    ?>
                                    <div class="message">
                                        <div
                                            class="message-box <?php echo $user['user_id'] == $user_id ? 'sender' : 'receiver'; ?>">
                                            <div class="message-header">
                                                <span class="fw-bold"><?php echo $user['name']; ?></span>
                                                <?php echo $user['role'] == 'admin' ? '<span class="badge ms-3 fs-8 bg-danger">Admin</span>' : '<span class="badge ms-3 fs-8 bg-primary">User</span>'; ?>
                                            </div>
                                            <div class="message-text my-2">
                                                <p class="mb-0"><?php echo nl2br($message['message_text']) ?></p>
                                            </div>
                                            <div class="message-footer">
                                                <span><?php echo date('h:i a  |  d M, Y', strtotime($message['timestamp'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {

                                ?>
                                <p class="text-center">No messages yet!</p>
                                <?php
                            }
                            ?>
                        </div>
                        <form action="" method="post" class="d-flex message-form needs-validation" novalidate>
                            <textarea name="message" required id="message"
                                class="form-control message-input bg-transparent"></textarea>
                            <button type="submit" class="btn btn-primary send-btn"><i
                                    class="fa fa-paper-plane fs-18"></i></button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>