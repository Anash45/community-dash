<?php
// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isUser()
{
    return isset($_SESSION['role']) && $_SESSION['role'] == 'user';
}

function getAllBoards($user_id)
{
    global $conn;
    // Initialize an empty array to store the boards
    $boards = array();

    // Prepare and execute query to fetch boards of the user along with the count of tasks
    $query = "SELECT b.*, COUNT(t.task_id) AS num_tasks 
              FROM boards b 
              LEFT JOIN tasks t ON b.board_id = t.board_id 
              WHERE b.user_id = ? 
              GROUP BY b.board_id 
              ORDER BY b.board_order";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Loop through the result set and store each board with the number of tasks in the array
    while ($row = $result->fetch_assoc()) {
        $boards[] = $row;
    }

    // Return the array of boards
    return $boards;
}


function getBoardById($board_id)
{
    global $conn;

    // Prepare and execute query to fetch boards of the user
    $query = "SELECT * FROM boards WHERE board_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $board_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $board = $result->fetch_assoc();
    // Return the array of boards
    return $board;
}

function getAllMessages()
{
    global $conn;
    // Initialize an empty array to store the messages
    $messages = array();

    // Prepare and execute query to fetch all messages
    $query = "SELECT * FROM messages";
    $result = $conn->query($query);

    // Check if the query was successful
    if ($result) {
        // Loop through the result set and store each message in the array
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        // Free the result set
        $result->free();
    } else {
        // Handle query error (optional)
        echo "Error: " . $conn->error;
    }

    // Return the array of messages
    return $messages;
}
function getUserById($user_id)
{
    global $conn;
    // Prepare and execute query to fetch user by ID
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the query returned a result
    if ($result->num_rows == 1) {
        // Fetch the user details as an associative array
        $user = $result->fetch_assoc();
        // Free the result set
        $result->free();
        // Return the user details
        return $user;
    } else {
        // Return null if user not found
        return null;
    }
}

// Function to insert message into database
function insertMessage($message, $user_id)
{
    global $conn;
    // Prepare and execute query to insert message
    $query = "INSERT INTO messages (message_text, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $message, $user_id);
    // Execute the statement
    if ($stmt->execute()) {
        // Message inserted successfully
        return true;
    } else {
        // Error inserting message
        return false;
    }
}

function readMessage($message_id)
{
    $user_id = $_SESSION['user_id'];
    global $conn;
    $sql = "INSERT message_read (`message_id`,`user_id`) VALUES ('$message_id','$user_id')";
    if ($conn->query($sql)) {
        return true;
    }
}

function countUnreadMessages()
{
    $user_id = $_SESSION['user_id'];
    global $conn;
    $messages = getAllMessages();
    $unreadCount = 0;
    foreach ($messages as $message) {
        $message_id = $message['message_id'];
        $sql = "SELECT * FROM message_read WHERE user_id = '$user_id' AND message_id = '$message_id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if (empty($row)) {
            $unreadCount++;
        }
    }
    return $unreadCount;
}

function getLastUnread()
{
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT MAX(message_id) as message_id FROM message_read WHERE user_id = '$user_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if (!empty($row)) {
        return $row['message_id'];
    } else {
        return null;
    }
}

function convertToJsDateTime($datetimeString)
{
    // Create a DateTime object from the provided datetime string
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetimeString);

    // Check if the conversion was successful
    if ($datetime) {
        // Convert the datetime to ISO 8601 format
        $iso8601DateTime = $datetime->format('Y-m-d\TH:i:s.v\Z');
        return $iso8601DateTime;
    } else {
        // Return null or handle the error as needed
        return null;
    }
}
function startTimer($duration)
{
    require_once './db_conn.php';
    $user_id = $_SESSION['user_id'];

    // Check if there is already an active timer for the user
    $activeTimerQuery = "SELECT * FROM timer WHERE user_id = ? AND end_time IS NULL";
    $stmt = $conn->prepare($activeTimerQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If there is an active timer, return false
    if ($result->num_rows > 0) {
        return array('success' => false);
    }

    // Get the current date/time in UTC timezone
    $currentDateTimeUTC = new DateTime('now', new DateTimeZone('UTC'));
    $start_time = $currentDateTimeUTC->format('Y-m-d H:i:s');

    // Prepare and bind the SQL statement to insert a new timer record
    $stmt = $conn->prepare("INSERT INTO timer (user_id, start_time, duration_minutes) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $start_time, $duration);

    // Execute the statement
    if ($stmt->execute()) {
        // Timer session started successfully, fetch the inserted row
        $insertedId = $stmt->insert_id;
        $insertedRow = array(
            'timer_id' => $insertedId,
            'user_id' => $user_id,
            'start_time' => convertToJsDateTime($start_time),
            'duration_minutes' => $duration
        );
        return array('success' => true, 'timer' => $insertedRow);
    } else {
        // Error occurred while inserting timer session
        return array('success' => false);
    }
}

function endTimer()
{
    require_once 'db_conn.php'; // Include your database connection script
    $user_id = $_SESSION['user_id'];
    // Get the current date/time in UTC timezone
    $currentDateTimeUTC = new DateTime('now', new DateTimeZone('UTC'));
    $end_time = $currentDateTimeUTC->format('Y-m-d H:i:s');

    // Get all timers started by the user whose end_time is null
    $query = "SELECT start_time, duration_minutes,id FROM timer WHERE user_id = ? AND end_time IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Loop through each timer
    while ($row = $result->fetch_assoc()) {
        // Calculate the expected end time based on the duration of the timer
        $start_time = $row['start_time'];
        $duration = $row['duration_minutes'];
        $expected_end_time = date('Y-m-d H:i:s', strtotime($start_time . ' +' . $duration . ' minutes')); // Add duration to start time

        // If the current end time is greater than the expected end time, update it to the expected end time
        if ($end_time > $expected_end_time) {
            $end_time = $expected_end_time;
        }

        // Calculate the exact duration based on the start and end times
        $start_datetime = new DateTime($start_time);
        $end_datetime = new DateTime($end_time);
        $exact_duration = $start_datetime->diff($end_datetime)->format('%i'); // Duration in minutes

        // Prepare and execute the SQL query to update the timer record
        $update_query = "UPDATE timer SET end_time = ?, duration_minutes = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sii", $end_time, $exact_duration, $row['id']);
        $update_stmt->execute();
    }

    return true; // All timers updated successfully
}



function getUserTimer()
{
    global $conn;
    $user_id = $_SESSION['user_id'];
    // Prepare and execute the SQL query to fetch the latest timer record for the user
    $query = "SELECT * FROM timer WHERE user_id = ? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a record was found
    if ($result->num_rows > 0) {
        // Fetch the timer record
        $timer = $result->fetch_assoc();
        return $timer;
    } else {
        // No active timer found
        return null;
    }
}
function addNote($title, $content)
{
    global $conn;

    $user_id = $_SESSION['user_id'];
    // Check if the note with the same title and content already exists
    $check_query = "SELECT * FROM notes WHERE title = ? AND content = ? AND user_id = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("ssi", $title, $content, $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Note with the same title and content already exists
        return array('success' => false, 'message' => 'Note with the same title and content already exists.');
    } else {
        // Prepare and bind the SQL statement to insert a new note record
        $stmt_insert = $conn->prepare("INSERT INTO notes (title, content, user_id) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("ssi", $title, $content, $user_id);

        // Execute the statement
        if ($stmt_insert->execute()) {
            // Note added successfully
            return array('success' => true, 'message' => 'Note added successfully.');
        } else {
            // Error occurred while adding note
            return array('success' => false, 'message' => 'Error occurred while adding note.');
        }
    }
}
function getAllNotes($user_id)
{
    global $conn;

    // Prepare and execute query to fetch all notes for the user
    $query = "SELECT * FROM notes WHERE user_id = ? ORDER BY note_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an empty array to store the notes
    $notes = array();

    // Loop through the result set and store each note in the array
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }

    // Return the array of notes
    return $notes;
}
function getAllEvents()
{
    global $conn;

    // Prepare and execute query to fetch all events for the user with the number of attendees
    $query = "
        SELECT e.*, COUNT(a.attendee_id) AS num_attendees
        FROM events e
        LEFT JOIN attendees a ON e.event_id = a.event_id
        GROUP BY e.event_id
        ORDER BY e.event_id DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an empty array to store the events
    $events = array();

    // Loop through the result set and store each event in the array
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    // Return the array of events
    return $events;
}
function getEventById($event_id)
{
    global $conn;

    // Get the user_id from the session
    $user_id = $_SESSION['user_id'];

    // Prepare and execute query to fetch the event by its ID
    $query = "SELECT *, 
              (SELECT COUNT(*) FROM attendees WHERE event_id = ? AND user_id = ?) AS is_attending 
              FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $event_id, $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the event was found
    if ($result->num_rows > 0) {
        // Fetch and return the event
        return $result->fetch_assoc();
    } else {
        // Event not found
        return null;
    }
}

function addEvent($title, $description, $date, $time)
{
    global $conn;

    // Get the current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Combine date and time to create a datetime string for comparison
    $eventDateTime = $date . ' ' . $time;

    // Check if the event date time is in the future
    if ($eventDateTime <= $currentDateTime) {
        return array('success' => false, 'message' => 'Event date and time should be in the future.');
    }

    // Check if the event already exists
    $check_query = "SELECT * FROM events WHERE event_title = ? AND event_description = ? AND event_date = ? AND event_time = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ssss", $title, $description, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Event with same details already exists
        return array('success' => false, 'message' => 'Event with the same details already exists.');
    } else {
        // Insert the event into the database
        $insert_query = "INSERT INTO events (event_title, event_description, event_date, event_time) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $title, $description, $date, $time);
        if ($stmt->execute()) {
            // Event added successfully
            return array('success' => true, 'message' => 'Event added successfully.');
        } else {
            // Error occurred while adding event
            return array('success' => false, 'message' => 'Error: ' . $conn->error);
        }
    }
}


function toggleEventAttendance($event_id)
{
    global $conn;

    // Initialize the response array
    $response = array();

    $user_id = $_SESSION['user_id'];

    // Check if the user is already attending the event
    $check_query = "SELECT * FROM attendees WHERE event_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is already attending, remove them
    if ($result->num_rows > 0) {
        $delete_query = "DELETE FROM attendees WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $response['message'] = "User removed from attendees list.";
    } else {
        // If the user is not attending, add them
        $insert_query = "INSERT INTO attendees (event_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $response['message'] = "User added to attendees list.";
    }

    $response['success'] = true;

    // Return the response array
    return $response;
}

function getEventAttendees($event_id)
{
    global $conn;

    // Prepare and execute query to fetch the attendees' details for the event
    $query = "SELECT attendees.attendee_id, users.name, users.email 
              FROM attendees 
              INNER JOIN users ON attendees.user_id = users.user_id
              WHERE attendees.event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an empty array to store the attendees' details
    $attendees = array();

    // Loop through the result set and store each attendee's details in the array
    while ($row = $result->fetch_assoc()) {
        $attendees[] = $row;
    }

    // Return the array of attendees' details
    return $attendees;
}

function getTimerMinutesPerDay($user_id)
{
    global $conn;

    // Prepare and execute query to get sum of minutes per day
    $query = "SELECT DATE(start_time) AS day, SUM(duration_minutes) AS total_minutes, COUNT(*) AS timer_count FROM timer WHERE user_id = ? GROUP BY DATE(start_time) ORDER BY DATE(start_time)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an empty array to store the results
    $data = array();

    // Loop through the result set and store the data
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}


// Function to fetch the last 5 users added to the website
function getLastFiveUsers()
{
    global $conn;

    // Query to fetch the last 5 users added to the website
    $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($query);

    // Initialize an array to store user data
    $users = array();

    // Fetch user data
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}
// Function to fetch the last 5 events added
function getLastFiveEvents()
{
    global $conn;

    // Query to fetch the last 5 events added to the website
    $query = "SELECT * FROM events ORDER BY event_id DESC LIMIT 5";
    $result = $conn->query($query);

    // Initialize an array to store event data
    $events = array();

    // Fetch event data
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    return $events;
}

// Function to get all users except those with the role as admin
function getUsersExceptAdmin()
{
    global $conn;

    // Query to fetch all users except those with the role as admin
    $query = "SELECT * FROM users WHERE role != 'admin'";
    $result = $conn->query($query);

    // Initialize an array to store user data
    $users = array();

    // Fetch user data
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}
function deleteUser($user_id)
{
    global $conn;

    // Prepare and execute the SQL query to delete the user
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // User deleted successfully
        return true;
    } else {
        // Error occurred while deleting user
        return false;
    }
}
