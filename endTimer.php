<?php
// Assuming endTimer function is defined in functions.php
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Call endTimer function with duration in minutes
    $result = endTimer();

    // Create an associative array to hold the result
    $response = array(
        "success" => $result // Assuming endTimer returns true on success
    );

    // Encode the response as JSON and echo it
    echo json_encode($response);
}
?>