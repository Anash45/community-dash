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
            // Add the original event to the events array
            $events[] = array(
                'id' => $row['event_id'],
                'title' => $row['event_title'],
                'start' => $row['event_date'] . 'T' . $row['event_time'],
                'eventType' => 'event', // Assuming event_type indicates it's an event
            );

            // Check if the event is recurring
            if ($row['is_recurring']) {
                $startDate = new DateTime($row['event_date']); // Get the start date of the event
                $recurrenceLimit = 5; // Limit the number of occurrences to 5
                $currentDate = new DateTime(); // Get the current date

                // Loop to generate occurrences based on the recurrence pattern
                for ($i = 0; $i < $recurrenceLimit; $i++) {
                    // Apply recurrence pattern based on the value stored in the database
                    if ($row['recurrence_pattern'] === 'daily') {
                        $startDate->modify('+1 day');
                    } elseif ($row['recurrence_pattern'] === 'weekly') {
                        $startDate->modify('+1 week');
                    } elseif ($row['recurrence_pattern'] === 'monthly') {
                        $startDate->modify('+1 month');
                    }

                    // Check if the occurrence date is in the future
                    if ($startDate > $currentDate) {
                        // Add the occurrence to the events array
                        $events[] = array(
                            'id' => $row['event_id'],
                            'title' => $row['event_title'],
                            'start' => $startDate->format('Y-m-d') . 'T' . $row['event_time'],
                            'eventType' => 'event', // Assuming event_type indicates it's an event
                        );
                    }
                }
            }
        }
    }

}

// Convert events array to JSON format
$events_json = json_encode($events);

// Output JSON data
// header('Content-Type: application/json');
echo $events_json;
?>