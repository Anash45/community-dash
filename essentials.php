<div class="position-fixed top-0 end-0 p-3 toast-container" style="z-index: 999999">
    <div class="toast hide bg-success" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-transparent">
            <strong class="me-auto text-white">Success</strong>
            <button type="button" data-bs-dismiss="toast" class="bg-transparent border-0 text-white"
                aria-label="Close"><i class="fa fa-times"></i></button>
        </div>
        <div class="toast-body bg-transparent py-2">
            <p class="me-auto mb-0 text-white">Timer started...</p>
        </div>
    </div>
</div>
<!-- Timer container -->
<div class="timer-container">
    <!-- Timer button -->
    <button class="timer-button" onclick="toggleTimer()">
        <i class="fas fa-clock timer-icon"></i>
    </button>
    <!-- Timer body -->
    <div class="timer-body">
        <!-- Start state -->
        <div id="startState" class="timer-states gap-1">
            <input type="number" class="timer-input" id="durationInput" max="999" min="1"
                title="Should be between 1-999" step="5" value="5" size="3" placeholder="Enter duration in minutes">
            <button class="start-btn border-0 py-2 px-2 btn-success rounded" id="startTimerButton">Start</button>
        </div>
        <!-- Stop state (hidden by default) -->
        <div id="stopState" class="timer-states gap-1">
            <div class="countdown" id="countdownTimer"></div>
            <button class="stop-btn border-0 py-2 px-2 btn-danger rounded" onclick="endCountdown()">Stop</button>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">Add Note</h5>
                <button type="button" class="bg-transparent border-0 p-0 text-white" data-bs-dismiss="modal"
                    aria-label="Close"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body">
                <!-- Note form -->
                <form id="addNoteForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="noteTitle" class="form-label">Title</label>
                        <input type="text" class="form-control bg-transparent border-secondary" id="title" name="title"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Content</label>
                        <textarea class="form-control bg-transparent border-secondary" id="content" name="content"
                            rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="addNote">Save Note</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$userTimer = getUserTimer();
if ($userTimer != null) {
    $dateTime = $userTimer['start_time'];
    $durationMins = $userTimer['duration_minutes'];
    $jsDateTime = convertToJsDateTime($dateTime);
    echo '<script>startCountdown("' . $jsDateTime . '",' . $durationMins . ');</script>';
}
?>