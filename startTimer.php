<?php
// Assuming startTimer function is defined in functions.php
include 'functions.php';

if (isset($_POST['durationMinutes'])) {
    $durationMinutes = $_POST['durationMinutes'];

    // Call startTimer function with duration in minutes
    $result = startTimer($durationMinutes);

    // Create an associative array to hold the result
    $response = array(
        "success" => $result // Assuming startTimer returns true on success
    );

    // Encode the response as JSON and echo it
    echo json_encode($response);
}
?>