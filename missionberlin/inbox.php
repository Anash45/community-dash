<?php
require_once './db_conn.php';
require './functions.php';

if (!isLoggedIn()) {
    header('location: login.php');
}

$user_id = $_SESSION['user_id'];
$info = '';

if (isset($_POST['create_group'])) {
    $group_name = $_POST['group_name'];
    $users = $_POST['users'];

    // Add the logged-in user to the list of selected users
    $users[] = $_SESSION['user_id'];

    // Get current datetime for created_at
    $created_at = date("Y-m-d H:i:s");
    $created_by = $_SESSION['user_id'];

    // Insert group into groups table
    $sql_insert_group = "INSERT INTO `groups` (group_name, created_by, created_at) VALUES ('$group_name', $created_by, '$created_at')";
    if ($conn->query($sql_insert_group) === TRUE) {

        // Get the ID of the newly inserted group
        $group_id = $conn->insert_id;

        // Insert group members into group_members table
        foreach ($users as $user_id) {
            $sql_insert_member = "INSERT INTO group_members (group_id, user_id) VALUES ($group_id, $user_id)";
            $conn->query($sql_insert_member);
        }
    } else {
        // Error handling
        echo "Error: " . $sql_insert_group . "<br>" . $conn->error;
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
                    <div class="row chats">
                        <div class="col-md-6 col-12 mb-md-0 mb-4">
                            <div class="h-100 chat-col">
                                <div class="cc-info flex-md-row flex-column gap-1">
                                    <h4 class="text-center my-3 text-white fw-bold">
                                        <span class="lang-en">Group Chats</span>
                                        <span class="lang-de">Gruppenchats</span>
                                    </h4>
                                    <div class="d-flex align-items-center gap-1 justify-content-between">
                                        <!-- Button to trigger Start Group Chat Modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#startGroupChatModal">
                                            <span class="lang-en">Start Group Chat</span>
                                            <span class="lang-de">Gruppenchat starten</span>
                                        </button>
                                        <!-- Button to trigger Create Group Modal -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#makeGroup">
                                            <span class="lang-en">Create Group</span>
                                            <span class="lang-de">Gruppe erstellen</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="cc-convos">
                                    <ul class="convo-list">
                                        <?php

                                        // Fetch group conversations
                                        $user_id = $_SESSION['user_id'];

                                        $sql_group_messages = "
    SELECT g.group_id, g.group_name, lm.last_message_text, lm.last_message_time
    FROM `groups` g
    INNER JOIN group_members gm ON g.group_id = gm.group_id
    LEFT JOIN (
        SELECT tm.group_id, tm.message_text AS last_message_text, tm.sent_at AS last_message_time
        FROM text_messages tm
        INNER JOIN (
            SELECT group_id, MAX(sent_at) AS last_message_time
            FROM text_messages
            GROUP BY group_id
        ) subquery ON tm.group_id = subquery.group_id AND tm.sent_at = subquery.last_message_time
    ) lm ON g.group_id = lm.group_id
    WHERE gm.user_id = $user_id
    ORDER BY lm.last_message_time DESC";

                                        $result_group_messages = $conn->query($sql_group_messages);

                                        while ($row = $result_group_messages->fetch_assoc()) {
                                            $group_id = $row['group_id'];
                                            $group_name = $row['group_name'];
                                            $last_message_text = $row['last_message_text'];
                                            $last_message_time = $row['last_message_time'];
                                            $msg = ($last_message_text == '') ? '' : (strlen($last_message_text) > 100 ? substr($last_message_text, 0, 100) . '...' : $last_message_text);

                                            $dateTime = ($last_message_time == '') ? '' : $last_message_time;
                                            echo "<li><a href='messages.php?type=group&id=$group_id'><div class='receiver-name'>$group_name</div> <div class='msg-details'><span class='msg-text'>" . $msg . "</span> <span class='msg-time'>" . date("h:i a | d M, Y", strtotime($dateTime)) . "</span></div></a></li>";
                                        }

                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12 mb-md-0 mb-4">
                            <div class="chat-col">
                                <div class="cc-info flex-md-row flex-column gap-1">
                                    <h4 class="text-center my-3 text-white fw-bold">
                                        <span class="lang-en">Individual Chats</span>
                                        <span class="lang-de">Einzelnachrichten</span>
                                    </h4>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#startIndividualChatModal">
                                        <span class="lang-en">Start Individual Chat</span>
                                        <span class="lang-de">Einzelchat starten</span>
                                    </button>
                                </div>
                                <div class="cc-convos">
                                    <ul class="convo-list">
                                        <?php
                                        // Fetch individual conversations
                                        $sql_individual = "SELECT DISTINCT LEAST(sender_id, receiver_id) AS user1, GREATEST(sender_id, receiver_id) AS user2
                   FROM text_messages
                   WHERE sender_id = ? OR receiver_id = ?";
                                        $stmt_individual = $conn->prepare($sql_individual);
                                        $stmt_individual->bind_param("ii", $user_id, $user_id);
                                        $stmt_individual->execute();
                                        $result_individual = $stmt_individual->get_result();

                                        while ($row = $result_individual->fetch_assoc()) {
                                            $user1 = $row['user1'];
                                            $user2 = $row['user2'];

                                            // Determine the conversation partner
                                            $conversation_partner_id = $user1 == $user_id ? $user2 : $user1;

                                            // Fetch the name of the conversation partner and last message
                                            $sql_partner_last_message = "SELECT u.name AS partner_name, tm.message_text AS last_message_text
                                  FROM users u
                                  JOIN text_messages tm ON u.user_id = CASE
                                      WHEN tm.sender_id = ? THEN tm.receiver_id
                                      ELSE tm.sender_id
                                  END
                                  WHERE (tm.sender_id = ? AND tm.receiver_id = ?) OR (tm.sender_id = ? AND tm.receiver_id = ?)
                                  ORDER BY tm.sent_at DESC
                                  LIMIT 1";
                                            $stmt_partner_last_message = $conn->prepare($sql_partner_last_message);
                                            $stmt_partner_last_message->bind_param("iiiii", $user_id, $user_id, $conversation_partner_id, $conversation_partner_id, $user_id);
                                            $stmt_partner_last_message->execute();
                                            $result_partner_last_message = $stmt_partner_last_message->get_result();

                                            // Check if there is a result
                                            if ($result_partner_last_message->num_rows > 0) {
                                                $row_partner_last_message = $result_partner_last_message->fetch_assoc();
                                                $partner_name = $row_partner_last_message['partner_name'];
                                                $last_message_text = $row_partner_last_message['last_message_text'];
                                                $msg = ($last_message_text == '') ? '' : (strlen($last_message_text) > 100 ? substr($last_message_text, 0, 100) . '...' : $last_message_text);

                                                echo "<li><a href='messages.php?type=individual&id=$conversation_partner_id'><div class='receiver-name'>$partner_name</div> <div class='msg-details'><span class='msg-text'>" . $msg . "</span></div></a></li>";
                                            }
                                        }

                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>
        <div class="modal fade" id="startIndividualChatModal" tabindex="-1"
            aria-labelledby="startIndividualChatModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="startIndividualChatModalLabel">
                            <span class="lang-en">Start Individual Chat</span>
                            <span class="lang-de">Starten Sie den Einzelchat</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="get" action="messages.php">
                            <div class="mb-3">
                                <label for="userSelect" class="form-label">
                                    <span class="lang-en">Select user to chat with:</span>
                                    <span class="lang-de">Wählen Sie einen Benutzer zum Chatten aus:</span>
                                </label>
                                <select class="form-select bg-transparent text-light" id="userSelect" name="id">
                                    <?php
                                    $sql_users = "SELECT user_id, name FROM users WHERE user_id != $user_id";
                                    $result_users = $conn->query($sql_users);
                                    while ($row = $result_users->fetch_assoc()) {
                                        echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
                                        $receiverID = $row['user_id'];
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <span class="lang-en">Enter message:</span>
                                    <span class="lang-de">Nachricht eingeben:</span>
                                </label>
                                <input type="text" class="form-control bg-transparent" id="message" name="message"
                                    placeholder="Enter message">
                            </div>
                            <input type="hidden" name="type" value="individual">
                            <button type="submit" class="btn btn-primary" name="send_message">
                                <span class="lang-en">Send</span>
                                <span class="lang-de">Senden</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Create Group Modal -->
        <!-- Create Group Modal -->
        <div class="modal fade" id="makeGroup" tabindex="-1" aria-labelledby="makeGroupLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="makeGroupLabel">
                            <span class="lang-en">Create Group</span>
                            <span class="lang-de">Gruppe erstellen</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="group_name" class="form-label">
                                    <span class="lang-en">Group Name:</span>
                                    <span class="lang-de">Gruppenname:</span>
                                </label>
                                <input type="text" class="form-control bg-transparent" id="group_name" name="group_name"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="users" class="form-label">
                                    <span class="lang-en">Add Users:</span>
                                    <span class="lang-de">Benutzer hinzufügen:</span>
                                </label><br>
                                <?php
                                // Fetch users excluding the logged-in user
                                $sql_users = "SELECT user_id, name FROM users WHERE user_id != $user_id";
                                $result_users = $conn->query($sql_users);

                                // Display multi-select dropdown for users
                                echo "<select class='form-select bg-transparent text-light' id='users' name='users[]' multiple required>";
                                while ($row = $result_users->fetch_assoc()) {
                                    echo "<option value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
                                }
                                echo "</select>";
                                ?>
                            </div>
                            <button type="submit" class="btn btn-primary" name="create_group">
                                <span class="lang-en">Create Group</span>
                                <span class="lang-de">Gruppe erstellen</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Start Group Chat Modal -->
        <div class="modal fade" id="startGroupChatModal" tabindex="-1" aria-labelledby="startGroupChatModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="startGroupChatModalLabel">
                            <span class="lang-en">Start Group Chat</span>
                            <span class="lang-de">Gruppenchat starten</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="get" action="messages.php">
                            <div class="mb-3">
                                <label for="groupSelect" class="form-label">
                                    <span class="lang-en">Select group to chat with:</span>
                                    <span class="lang-de">Wählen Sie die Gruppe aus, mit der Sie chatten möchten:</span>
                                </label>
                                <select class="form-select bg-transparent text-white" id="groupSelect" name="id">
                                    <?php
                                    // Fetch groups where the logged-in user is a member
                                    $sql_groups = "SELECT g.group_id, g.group_name 
                        FROM `groups` g 
                        INNER JOIN group_members gm ON g.group_id = gm.group_id 
                        WHERE gm.user_id = $user_id";
                                    $result_groups = $conn->query($sql_groups);

                                    while ($row = $result_groups->fetch_assoc()) {
                                        echo "<option value='" . $row['group_id'] . "'>" . $row['group_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <span class="lang-en">Enter message:</span>
                                    <span class="lang-de">Nachricht eingeben:</span>
                                </label>
                                <input type="text" class="form-control bg-transparent" id="message" name="message"
                                    placeholder="Enter message">
                            </div>
                            <input type="hidden" name="type" value="group">
                            <button type="submit" class="btn btn-primary" name="send_message">
                                <span class="lang-en">Send</span>
                                <span class="lang-de">Senden</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>