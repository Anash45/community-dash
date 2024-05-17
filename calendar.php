<?php
require_once './db_conn.php';
require './functions.php';
if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
$page = 'home';
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
        <link rel="stylesheet" href="./assets/css/style.css?v=2">
    </head>

    <body>
        <?php include './header.php'; ?>
        <div class="container-fluid">
            <div class="row">
                <?php include './sidebar.php'; ?>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
                    <?php echo $info; ?>
                    <div class="row">
                        <?php
                        if (isAdmin()) {

                            // Get the last 5 users
                            $last_five_users = getLastFiveUsers();
                            ?>
                            <!-- HTML code to display the last 5 users in a card table format -->
                            <div class="col-md-6 mb-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h3 class="fw-bold mb-0 text-center">
                                            <span class="lang-en">Last 5 Users</span>
                                            <span class="lang-de">Letzte 5 Benutzer</span>
                                        </h3>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-transparent text-white">
                                                <thead>
                                                    <tr>
                                                        <th><span class="lang-en">User ID</span><span
                                                                class="lang-de">Benutzer-ID</span></th>
                                                        <th><span class="lang-en">Username</span><span
                                                                class="lang-de">Benutzername</span></th>
                                                        <th><span class="lang-en">Email</span><span
                                                                class="lang-de">E-Mail</span></th>
                                                        <th><span class="lang-en">Registration Date</span><span
                                                                class="lang-de">Anmeldedatum</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($last_five_users as $user): ?>
                                                        <tr>
                                                            <td><?php echo $user['user_id']; ?></td>
                                                            <td><?php echo $user['name']; ?></td>
                                                            <td><?php echo $user['email']; ?></td>
                                                            <td><?php echo $user['created_at']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php

                        // Get the last 5 events
                        $last_five_events = getLastFiveEvents();
                        if (isAdmin()) {
                            ?>
                            <!-- HTML code to display the last 5 events -->
                            <div class="col-md-6 mb-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h3 class="fw-bold text-center mb-0">
                                            <span class="lang-en">Last 5 Events</span>
                                            <span class="lang-de">Letzte 5 Ereignisse</span>
                                        </h3>
                                    </div>
                                    <div class="card-body pt-2">
                                        <div class="table-responsive">
                                            <table class="table table-transparent text-white">
                                                <thead>
                                                    <tr>
                                                        <th><span class="lang-en">Event ID</span><span
                                                                class="lang-de">Ereignis-ID</span></th>
                                                        <th><span class="lang-en">Event Name</span><span
                                                                class="lang-de">Ereignisname</span></th>
                                                        <th><span class="lang-en">Event Date</span><span
                                                                class="lang-de">Ereignisdatum</span></th>
                                                        <!-- Add more columns as needed -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($last_five_events as $event): ?>
                                                        <tr>
                                                            <td><?php echo $event['event_id']; ?></td>
                                                            <td><?php echo $event['event_title']; ?></td>
                                                            <td><?php echo $event['event_date']; ?></td>
                                                            <!-- Add more columns as needed -->
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="col-12">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js?v=2"></script>
        <div>
            <?php include './essentials.php'; ?>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.11/index.global.min.js"
            integrity="sha512-WPqMaM2rVif8hal2KZZSvINefPKQa8et3Q9GOK02jzNL51nt48n+d3RYeBCfU/pfYpb62BeeDf/kybRY4SJyyw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            $(document).ready(function () {
                // Initialize FullCalendar
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth', // Display month view by default
                    events: [], // Initialize with no events
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridYear,dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    eventClick: function (info) {
                        if (info.event.extendedProps.eventType === 'event') {
                            // Redirect to eventDetails.php for events
                            window.location = "eventDetails.php?event_id=" + info.event.id;
                        }
                    },
                    eventContent: function (arg) {
                        const eventType = arg.event.extendedProps.eventType;
                        const title = arg.event.title;
                        let content = '';
                        if (eventType === 'event') {
                            // Customize rendering for events
                            content = '<div class="event-event"><span><span class="lang-en">Event:</span><span class="lang-de">Ereignis:</span></span>' + title + '</div>';
                        }
                        return { html: content };
                    }
                });

                // Fetch events
                $.ajax({
                    url: 'fetchEvents.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (eventResponse) {
                        // Add events to the calendar with a different color
                        calendar.addEventSource({
                            events: eventResponse,
                            color: '#4ec700', // Adjust the color for events as needed
                            textColor: 'white'
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        console.log('Error fetching events.' + error);
                    }
                });


                // Render the calendar
                calendar.render();
            });

        </script>
    </body>

</html>