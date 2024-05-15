<?php
// Include database connection
include 'db_conn.php';

// Check if taskId and status are set in the POST request
if (isset($_POST['taskId'], $_POST['status'])) {
    // Sanitize input
    $taskId = $_POST['taskId'];
    $status = $_POST['status'];

    // Update task status in the database
    $update_query = "UPDATE tasks SET task_status = ? WHERE task_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $taskId);
    if ($stmt->execute()) {
        echo "Task status updated successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Error: taskId and status parameters not set.";
}
?>