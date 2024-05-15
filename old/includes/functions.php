<?php
require_once 'db_connect.php';

// Add a new user
function addUser($userData)
{
    global $conn;

    // Check if email is already present
    $existingEmailCheck = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($existingEmailCheck);
    $stmt->bind_param("s", $userData['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Email is already taken', 'data' => null];
    }

    $sql = "INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $userData['username'], $userData['email'], $userData['password'], $userData['role']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'User added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a user by ID
function deleteUser($userId)
{
    global $conn;
    $sql = "DELETE FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'User deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update user details
function updateUser($userId, $userData)
{
    global $conn;

    // Check if email is already taken by another user
    $existingEmailCheck = "SELECT * FROM Users WHERE email = ? AND user_id != ?";
    $stmt = $conn->prepare($existingEmailCheck);
    $stmt->bind_param("si", $userData['email'], $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Email is already taken by another user', 'data' => null];
    }

    $sql = "UPDATE Users SET username = ?, email = ?, password = ?, role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $userData['username'], $userData['email'], $userData['password'], $userData['role'], $userId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'User updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all users
function getAllUsers()
{
    global $conn;
    $sql = "SELECT * FROM Users";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return ['status' => 'success', 'message' => 'Users retrieved successfully', 'data' => $users];
    } else {
        return ['status' => 'error', 'message' => 'No users found', 'data' => null];
    }
}

// Get user by ID
function getUserById($userId)
{
    global $conn;
    $sql = "SELECT * FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'User retrieved successfully', 'data' => $user];
    } else {
        return ['status' => 'error', 'message' => 'User not found', 'data' => null];
    }
}

// Add a new project
function addProject($projectData)
{
    global $conn;

    $sql = "INSERT INTO Projects (project_name, project_description, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $projectData['project_name'], $projectData['project_description'], $projectData['created_by']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Project added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a project by ID
function deleteProject($projectId)
{
    global $conn;
    $sql = "DELETE FROM Projects WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Project deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update project details
function updateProject($projectId, $projectData)
{
    global $conn;

    $sql = "UPDATE Projects SET project_name = ?, project_description = ?, created_by = ? WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $projectData['project_name'], $projectData['project_description'], $projectData['created_by'], $projectId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Project updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all projects
function getAllProjects()
{
    global $conn;
    $sql = "SELECT * FROM Projects";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        return ['status' => 'success', 'message' => 'Projects retrieved successfully', 'data' => $projects];
    } else {
        return ['status' => 'error', 'message' => 'No projects found', 'data' => null];
    }
}

// Get project by ID
function getProjectById($projectId)
{
    global $conn;
    $sql = "SELECT * FROM Projects WHERE project_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $project = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Project retrieved successfully', 'data' => $project];
    } else {
        return ['status' => 'error', 'message' => 'Project not found', 'data' => null];
    }
}

// Add a new note
function addNote($noteData)
{
    global $conn;

    $sql = "INSERT INTO Notes (user_id, note_content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $noteData['user_id'], $noteData['note_content']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Note added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a note by ID
function deleteNote($noteId)
{
    global $conn;
    $sql = "DELETE FROM Notes WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $noteId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Note deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update note details
function updateNote($noteId, $noteData)
{
    global $conn;

    $sql = "UPDATE Notes SET note_content = ? WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $noteData['note_content'], $noteId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Note updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all notes for a user
function getAllNotes($userId)
{
    global $conn;
    $sql = "SELECT * FROM Notes WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $notes = [];
        while ($row = $result->fetch_assoc()) {
            $notes[] = $row;
        }
        return ['status' => 'success', 'message' => 'Notes retrieved successfully', 'data' => $notes];
    } else {
        return ['status' => 'error', 'message' => 'No notes found', 'data' => null];
    }
}

// Get note by ID
function getNoteById($noteId)
{
    global $conn;
    $sql = "SELECT * FROM Notes WHERE note_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $note = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Note retrieved successfully', 'data' => $note];
    } else {
        return ['status' => 'error', 'message' => 'Note not found', 'data' => null];
    }
}
// Add a new message to the group chat
function addMessageToGroupChat($userId, $messageContent)
{
    global $conn;

    $sql = "INSERT INTO GroupChat (user_id, message_content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $messageContent);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Message added to group chat successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a message from the group chat by ID
function deleteMessageFromGroupChat($messageId)
{
    global $conn;
    $sql = "DELETE FROM GroupChat WHERE message_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messageId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Message deleted from group chat successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all messages from the group chat
function getAllGroupChatMessages()
{
    global $conn;
    $sql = "SELECT * FROM GroupChat";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return ['status' => 'success', 'message' => 'Group chat messages retrieved successfully', 'data' => $messages];
    } else {
        return ['status' => 'error', 'message' => 'No messages found in group chat', 'data' => null];
    }
}

// Get message from the group chat by ID
function getGroupChatMessageById($messageId)
{
    global $conn;
    $sql = "SELECT * FROM GroupChat WHERE message_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $message = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Group chat message retrieved successfully', 'data' => $message];
    } else {
        return ['status' => 'error', 'message' => 'Message not found in group chat', 'data' => null];
    }
}

// Get messages from the group chat by user ID
function getChatMessagesByUser($userId)
{
    global $conn;
    $sql = "SELECT * FROM GroupChat WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return ['status' => 'success', 'message' => 'Group chat messages retrieved successfully for user', 'data' => $messages];
    } else {
        return ['status' => 'error', 'message' => 'No messages found in group chat for user', 'data' => null];
    }
}

// Add a new meeting
function addMeeting($meetingData)
{
    global $conn;

    $sql = "INSERT INTO Meetings (meeting_name, meeting_description, created_by, start_time, end_time, location, google_calendar_event_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissss", $meetingData['meeting_name'], $meetingData['meeting_description'], $meetingData['created_by'], $meetingData['start_time'], $meetingData['end_time'], $meetingData['location'], $meetingData['google_calendar_event_id']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Meeting added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a meeting by ID
function deleteMeeting($meetingId)
{
    global $conn;
    $sql = "DELETE FROM Meetings WHERE meeting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Meeting deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update meeting details
function updateMeeting($meetingId, $meetingData)
{
    global $conn;

    $sql = "UPDATE Meetings SET meeting_name = ?, meeting_description = ?, created_by = ?, start_time = ?, end_time = ?, location = ?, google_calendar_event_id = ? WHERE meeting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissssi", $meetingData['meeting_name'], $meetingData['meeting_description'], $meetingData['created_by'], $meetingData['start_time'], $meetingData['end_time'], $meetingData['location'], $meetingData['google_calendar_event_id'], $meetingId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Meeting updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all meetings
function getAllMeetings()
{
    global $conn;
    $sql = "SELECT * FROM Meetings";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $meetings = [];
        while ($row = $result->fetch_assoc()) {
            $meetings[] = $row;
        }
        return ['status' => 'success', 'message' => 'Meetings retrieved successfully', 'data' => $meetings];
    } else {
        return ['status' => 'error', 'message' => 'No meetings found', 'data' => null];
    }
}

// Get meeting by ID
function getMeetingById($meetingId)
{
    global $conn;
    $sql = "SELECT * FROM Meetings WHERE meeting_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $meeting = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Meeting retrieved successfully', 'data' => $meeting];
    } else {
        return ['status' => 'error', 'message' => 'Meeting not found', 'data' => null];
    }
}

// Add a new task
function addTask($taskData)
{
    global $conn;

    $sql = "INSERT INTO Tasks (task_name, task_description, project_id, assigned_to, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiis", $taskData['task_name'], $taskData['task_description'], $taskData['project_id'], $taskData['assigned_to'], $taskData['status']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Task added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a task by ID
function deleteTask($taskId)
{
    global $conn;
    $sql = "DELETE FROM Tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Task deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update task details
function updateTask($taskId, $taskData)
{
    global $conn;

    $sql = "UPDATE Tasks SET task_name = ?, task_description = ?, project_id = ?, assigned_to = ?, status = ? WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiisi", $taskData['task_name'], $taskData['task_description'], $taskData['project_id'], $taskData['assigned_to'], $taskData['status'], $taskId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Task updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all tasks
function getAllTasks()
{
    global $conn;
    $sql = "SELECT * FROM Tasks";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        return ['status' => 'success', 'message' => 'Tasks retrieved successfully', 'data' => $tasks];
    } else {
        return ['status' => 'error', 'message' => 'No tasks found', 'data' => null];
    }
}

// Get task by ID
function getTaskById($taskId)
{
    global $conn;
    $sql = "SELECT * FROM Tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $task = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Task retrieved successfully', 'data' => $task];
    } else {
        return ['status' => 'error', 'message' => 'Task not found', 'data' => null];
    }
}
// Add a new Pomodoro session
function addPomodoroSession($pomodoroData)
{
    global $conn;

    $sql = "INSERT INTO Pomodoro (user_id, start_time, end_time, completed) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $pomodoroData['user_id'], $pomodoroData['start_time'], $pomodoroData['end_time'], $pomodoroData['completed']);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Pomodoro session added successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Delete a Pomodoro session by ID
function deletePomodoroSession($pomodoroId)
{
    global $conn;
    $sql = "DELETE FROM Pomodoro WHERE pomodoro_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pomodoroId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Pomodoro session deleted successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Update Pomodoro session details
function updatePomodoroSession($pomodoroId, $pomodoroData)
{
    global $conn;

    $sql = "UPDATE Pomodoro SET start_time = ?, end_time = ?, completed = ? WHERE pomodoro_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $pomodoroData['start_time'], $pomodoroData['end_time'], $pomodoroData['completed'], $pomodoroId);
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Pomodoro session updated successfully', 'data' => null];
    } else {
        return ['status' => 'error', 'message' => $conn->error, 'data' => null];
    }
}

// Get all Pomodoro sessions for a user
function getAllPomodoroSessions($userId)
{
    global $conn;
    $sql = "SELECT * FROM Pomodoro WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        return ['status' => 'success', 'message' => 'Pomodoro sessions retrieved successfully', 'data' => $sessions];
    } else {
        return ['status' => 'error', 'message' => 'No Pomodoro sessions found', 'data' => null];
    }
}

// Get Pomodoro session by ID
function getPomodoroSessionById($pomodoroId)
{
    global $conn;
    $sql = "SELECT * FROM Pomodoro WHERE pomodoro_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pomodoroId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $session = $result->fetch_assoc();
        return ['status' => 'success', 'message' => 'Pomodoro session retrieved successfully', 'data' => $session];
    } else {
        return ['status' => 'error', 'message' => 'Pomodoro session not found', 'data' => null];
    }
}