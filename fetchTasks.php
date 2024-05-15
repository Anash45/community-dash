<?php
// Include database connection
include 'db_conn.php';

// Initialize array to store events data
$events = array();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Query to fetch tasks from the database
    $query = "SELECT * FROM tasks WHERE `user_id` = '$user_id'";

    // Execute the query
    $result = $conn->query($query);

    // Check if there are any tasks
    if ($result->num_rows > 0) {
        // Fetch tasks data
        while ($row = $result->fetch_assoc()) {
            // Format task data into FullCalendar event format
            $event = array(
                'id' => $row['task_id'],
                'board_id' => $row['board_id'],
                'title' => $row['task_title'],
                'start' => $row['deadline_date'],
                'eventType' => 'task'// Assuming deadline_date is the start date for the task
                // You can add more properties like 'end' if you have end dates for tasks
            );
            // Add the event to the events array
            $events[] = $event;
        }
    }
}

// Convert events array to JSON format
$events_json = json_encode($events);

// Output JSON data
header('Content-Type: application/json');
echo $events_json;
?>