<?php
require_once './db_conn.php';
require './functions.php';

if (!isAdmin()) {
    header('location: index.php');
}
$info = '';
if(isset($_GET['delete_user'])){
    $user_id = $_GET['delete_user'];
    if(deleteUser($user_id)){
        $info = '<div class="alert alert-success" role="alert">User deleted successfully!</div>';
    }else{
        $info = '<div class="alert alert-danger" role="alert">Error deleting user!</div>';
    }
}
$users = getUsersExceptAdmin();
$page = 'users';
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
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h1 class="section-title pt-3 fw-bold text-center text-white">Users</h1>
                    </div>
                    <?php echo $info; ?>
                    <?php
                    // Check if the user is an admin (assuming isAdmin function checks user role)
                    
                    ?>
                    <div class="card">
                        <div class="card-body">
                            <table class="table text-white">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>User ID</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['name']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['user_id']; ?></td>
                                            <td><?php echo $user['created_at']; ?></td>
                                            <td>
                                                <!-- Delete button -->
                                                <a href="?delete_user=<?php echo $user['user_id']; ?>"
                                                    class="border-0 py-2 px-3 btn-danger text-decoration-none rounded" onclick="return confirm('Do you really want to delete this user?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>
        <?php
        include './essentials.php';
        ?>
    </body>

</html>