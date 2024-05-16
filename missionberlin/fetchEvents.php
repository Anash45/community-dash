<?php
// Include database connection
include 'db_conn.php';
include 'functions.php';

// Initialize array to store events data
$events = array();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isAdmin()) {
        // Query to fetch events the user is attending
        $query = "SELECT * FROM events";

    } else {
        // Query to fetch events the user is attending
        $query = "SELECT e.* FROM events e INNER JOIN attendees a ON e.event_id = a.event_id WHERE a.user_id = '$user_id'";

    }
    // Execute the query
    $result = $conn->query($query);

    // Check if there are any events
    if ($result->num_rows > 0) {
        // Fetch events data
        while ($row = $result->fetch_assoc()) {
            // Format event data into FullCalendar event format
            $startDateTime = $row['event_date'] . 'T' . $row['event_time'];
            $event = array(
                'id' => $row['event_id'],
                'title' => $row['event_title'],
                'start' => $startDateTime, // Combine date and time to create datetime string
                'eventType' => 'event', // Assuming event_type indicates it's an event
                // You can add more properties like 'end' if you have end dates for events
            );
            // Add the event to the events array
            $events[] = $event;
        }
    }
}

// Convert events array to JSON format
$events_json = json_encode($events);

// Output JSON data
// header('Content-Type: application/json');
echo $events_json;
?>