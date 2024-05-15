(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()

function populateEditTask(title, description, deadline, priority, status, task_id) {
    console.log(title, description, deadline, priority, status, task_id);
    $('#editTaskModal #ed_taskTitle').val(title);
    $('#editTaskModal #ed_taskDescription').html(description);
    $('#editTaskModal #ed_deadlineDate').val(deadline);
    $('#editTaskModal #ed_moodPerception').val(priority);
    $('#editTaskModal #ed_taskStatus').val(status);
    $('#editTaskModal #ed_task_id').val(task_id);
    $('#editTaskModal').modal('show');
}

$(document).ready(function () {
    // Make tasks draggable
    $('.task').draggable({
        helper: 'clone',
        cursor: 'move',
        zIndex: 1000,
        opacity: 0.7
    });

    // Make tasks div droppable
    $('.tasks').droppable({
        accept: '.task',
        drop: function (event, ui) {
            var droppedTask = ui.draggable;
            droppedTask.detach().css({ top: 0, left: 0 }).appendTo($(this));
            var taskId = droppedTask.attr('data-id');
            var status = $(this).attr('data-status');
            console.log('Task ID: ' + taskId + ', Status: ' + status);
            // Make AJAX call to updateTaskStatus.php
            $.ajax({
                url: 'updateTaskStatus.php',
                type: 'POST',
                data: { taskId: taskId, status: status },
                success: function (response) {
                    console.log(response);
                    // Add your success handling logic here
                    updateCounts();
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    // Add your error handling logic here
                }
            });
        }
    });
});

function updateCounts() {
    $('.tasks').each(function () {
        let tasksCount = $(this).find('.task').length;
        console.log(tasksCount);
        $(this).siblings('div').find('.task-counter').text(`(${tasksCount})`);
    })
}

updateCounts();

$(document).ready(function () {
    // Toggle sorting order
    var ascending = true;

    // Sort tasks based on data-priority attribute
    function sortTasks(elm) {
        var $tasksContainer = elm.closest('.task-info').siblings('.tasks');
        var $tasks = $tasksContainer.children('.task');
        $tasks.sort(function (a, b) {
            var priorityA = parseInt($(a).attr('data-priority'));
            var priorityB = parseInt($(b).attr('data-priority'));
            return ascending ? priorityA - priorityB : priorityB - priorityA;
        });
        $tasks.detach().appendTo($tasksContainer);
    }

    // Handle sort button click
    $('.sortButton').click(function () {
        let elm = $(this);
        ascending = !ascending; // Toggle sorting order
        sortTasks(elm);
    });
});

$(document).ready(function () {
    // Function to get the vertical position (Y-axis) of #new-messages
    function getNewMessagesPositionY() {
        var newMessages = document.getElementById('new-messages');
        if (newMessages) {
            var rect = newMessages.getBoundingClientRect();
            return rect.top + window.pageYOffset;
        }
        return null;
    }

    // Function to scroll #messages div to the bottom
    function scrollMessagesToBottom() {
        var messages = $('#messages');
        messages.scrollTop(messages[0].scrollHeight);
    }

    let prevMessagePositionY = 0;
    // Scroll #messages div until #new-messages comes into view
    function scrollMessagesUntilNewMessagesInView() {
        var newMessagePositionY = getNewMessagesPositionY();
        console.log(newMessagePositionY);
        if (newMessagePositionY !== null && newMessagePositionY < 70 || (newMessagePositionY == prevMessagePositionY)) {
            // #new-messages is within 68 pixels from the top, stop scrolling
            return;
        }

        // Scroll #messages div by a small increment
        $('#messages').scrollTop($('#messages').scrollTop() + 100);

        prevMessagePositionY = newMessagePositionY;

        // Continue scrolling recursively
        setTimeout(scrollMessagesUntilNewMessagesInView, 1); // Scroll every 100ms
    }

    let newMessages = $('#new-messages');
    let messages = $('#messages');
    if (newMessages.length > 0) {
        // console.log(1);
        scrollMessagesUntilNewMessagesInView();
    } else if (messages.length > 0) {
        scrollMessagesToBottom();
        // console.log(2);
    }
});


function showAlert(type, text) {
    $('.toast').removeClass('bg-danger');
    $('.toast').removeClass('bg-success');
    if (type == 'success') {
        $('.toast').addClass('bg-success');
        $('.toast-header strong').html('Success');
    } else {
        $('.toast').addClass('bg-danger');
        $('.toast-header strong').html('Error');
    }
    $('.toast-body>p').html(text);

    $('.toast').toast('show');
}


// Function to toggle timer body
function toggleTimer() {
    $('.timer-container').toggleClass('timer-open');
}
function startCountdown(startTime, duration) {
    // Parse the start time and duration
    const startTimeMs = new Date(startTime).getTime();
    const endTimeMs = startTimeMs + duration * 60 * 1000; // Calculate end time in milliseconds

    // Update the countdown timer display every second
    const timerInterval = setInterval(() => {
        // Calculate the remaining time
        const currentTimeMs = Date.now();
        const remainingTimeMs = endTimeMs - currentTimeMs;

        // Check if the timer has ended
        if (remainingTimeMs <= 0) {
            clearInterval(timerInterval); // Stop the timer interval
            document.getElementById('countdownTimer').innerText = '00:00:00'; // Display 00:00:00
            return;
        }

        // Format the remaining time as HH:MM:SS
        const hours = Math.floor(remainingTimeMs / (1000 * 60 * 60)).toString().padStart(2, '0');
        const minutes = Math.floor((remainingTimeMs % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
        const seconds = Math.floor((remainingTimeMs % (1000 * 60)) / 1000).toString().padStart(2, '0');
        const formattedTime = `${hours}:${minutes}:${seconds}`;

        // Update the countdown timer display
        document.getElementById('countdownTimer').innerText = formattedTime;
    }, 1000); // Update every second
}

$(document).ready(function () {
    $("#startTimerButton").on("click", function () {
        // Get duration in minutes from #durationInput
        var durationMinutes = Number($("#durationInput").val());
        console.log(durationMinutes);
        // Send duration to startTimer.php using AJAX
        $.ajax({
            url: "startTimer.php",
            method: "POST",
            data: { durationMinutes: durationMinutes },
            success: function (response) {
                response = JSON.parse(response);
                console.log(response.success);
                if (response.success !== false) {
                    let phpDateTime = response.success.timer.start_time;
                    let durationMinutes = response.success.timer.duration_minutes;
                    let startTime = phpDateTime;
                    startCountdown(startTime, durationMinutes);
                    showAlert('success', 'Timer started successfully!');
                } else {
                    showAlert('danger', 'An error occurred, maybe a timer is already started!');
                }
            },
            error: function (xhr, status, error) {
                // Handle error response here
                console.error("Error starting timer:", error);
            }
        });
    });
})

let countdownStarted = false; // Flag to track if countdown has started

let timerInterval;
function startCountdown(startTime, duration) {
    $('#countdownTimer').html('');
    $('.timer-button').removeClass('timer-ending');
    $('.timer-container').addClass('started');
    // Check if countdown has already started
    if (countdownStarted) {
        return; // Exit function if countdown has already started
    }
    clearInterval(timerInterval);
    $('.timer-container').addClass('started');
    countdownStarted = true; // Set flag to indicate countdown has started

    // Parse the start time and duration
    const startTimeMs = new Date(startTime).getTime();
    const durationMs = duration * 60 * 1000; // Convert duration to milliseconds

    // Update the countdown timer display every second
    timerInterval = setInterval(() => {
        // Calculate the remaining time
        const currentTimeMs = Date.now();
        const remainingTimeMs = startTimeMs + durationMs - currentTimeMs;

        // Check if the timer has ended
        if (remainingTimeMs <= 0) {
            clearInterval(timerInterval); // Stop the timer interval
            document.getElementById('countdownTimer').innerText = '00:00:00'; // Display 00:00:00
            endCountdown();
            return;
        }

        // Format the remaining time as HH:MM:SS
        const hours = Math.floor(remainingTimeMs / (1000 * 60 * 60)).toString().padStart(2, '0');
        const minutes = Math.floor((remainingTimeMs % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
        const seconds = Math.floor((remainingTimeMs % (1000 * 60)) / 1000).toString().padStart(2, '0');
        const formattedTime = `${hours}:${minutes}:${seconds}`;

        if(remainingTimeMs <= 60000){
            $('.timer-button').toggleClass('timer-ending');
        }
        console.log(remainingTimeMs);
        // Update the countdown timer display
        document.getElementById('countdownTimer').innerText = formattedTime;
    }, 1000); // Update every second
}


function endCountdown() {
    clearInterval(timerInterval);
    countdownStarted = false;
    $.ajax({
        url: 'endTimer.php',
        method: 'POST',
        success: function (response) {
            response = JSON.parse(response);
            console.log(response);
            if (response.success !== false) {
                showAlert('success', 'Timer ended successfully!');
            } else {
                showAlert('danger', 'An error occurred, maybe a timer is already ended!');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error ending timer:', error);
            // Handle error response
        }
    });
    $('#countdownTimer').html('');
    $('.timer-container').removeClass('started');
}

function openContentModal(content) {
    $('#noteContent').html(content);
    $('#contentModal').modal('show');
}