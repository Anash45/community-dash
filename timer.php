<?php
require_once './db_conn.php';
require './functions.php';
if (!isLoggedIn()) {
    header('location: login.php');
}
$info = '';
$page = 'timer';
?>
<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.84.0">
        <title>TeamForceConnect</title>
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
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
                    <?php echo $info; ?>
                    <div class="row">
                        <div class="col-12">
                            <canvas id="timerChart"></canvas>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Include Chart.js date adapter -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.2.1/dist/chartjs-adapter-moment.min.js"></script>
        <script>
            $(document).ready(function () {
                // PHP data obtained from getTimerMinutesPerDay
                let phpData = <?php echo json_encode(getTimerMinutesPerDay($_SESSION['user_id'])); ?>;

                // Convert PHP data to JavaScript array
                let chartData = phpData.map(item => {
                    return {
                        x: item.day, // Assuming item.day is a date string in the format 'YYYY-MM-DD'
                        y: item.total_minutes,
                        timer_count: item.timer_count // Add the number of times timer was used each day
                    };
                });

                // Draw Line Chart
                let ctx = document.getElementById('timerChart').getContext('2d');
                let myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'Total Minutes per Day',
                            data: chartData,
                            fill: false,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1,
                            yAxisID: 'minutes-axis' // Assign this dataset to 'minutes-axis'
                        }, {
                            label: 'Number of Times Timer Used',
                            data: chartData.map(data => ({ x: data.x, y: data.timer_count })), // Map timer_count to y
                            fill: false,
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1,
                            yAxisID: 'count-axis' // Assign this dataset to 'count-axis'
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'category', // Use 'category' scale for simple date labels
                                labels: chartData.map(data => data.x) // Use date strings as labels
                            },
                        }
                    }
                });
            });

        </script>
        <div>
            <?php include './essentials.php'; ?>
        </div>
    </body>

</html>