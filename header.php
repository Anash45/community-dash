<?php
if (isset($_POST['addNote'])) {
    
    $title = $_POST['title'];
    $content = $_POST['content'];
    $checkNote = addNote($title, $content);
    if ($checkNote['success']) {
        header('location: ./notes.php');
    } else {
        $info = '<div class="alert alert-success">' . $checkNote['message'] . '</div>';
    }
}
?>
<header class="navbar sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fw-bold text-white" href="./index.php">TeamForceConnect</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse"
        data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav flex-row gap-1 align-items-center">
        <?php
        if ($page == 'tasks') {
            echo '<div class="nav-item text-nowrap">
            <button class="btn" data-bs-toggle="modal" data-bs-target="#editBoardModal">Edit Project</button>
        </div>
        <div class="nav-item text-nowrap">
            <button class="btn" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="fa fa-plus"></i>Add Task</button>
        </div>';
        }

        if ($page == 'projects') {
            echo '<div class="nav-item text-nowrap">
            <button class="btn" data-bs-toggle="modal" data-bs-target="#addBoardModal">Add Project</button>
        </div>';
        }
        ?>
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="./logout.php">Logout</a>
        </div>
    </div>
</header>