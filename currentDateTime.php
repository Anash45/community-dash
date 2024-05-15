<?php
include './functions.php';
// Call the function to get the current date/time
if (isset($_GET)) {
    echo convertToJsDateTime($_GET['dateTime']);
}