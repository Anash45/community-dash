<?php
// Include database connection
include 'db_conn.php';

// Initialize $info variable
$info = "";

// Check if the login form is submitted
if (isset($_POST['login'])) {
    // Retrieve form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user data from database based on email
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            // Redirect to dashboard or any other page
            header("Location: index.php");
            exit();
        } else {
            $info = "<p class='alert alert-danger'>Incorrect password.</p>";
        }
    } else {
        $info = "<p class='alert alert-danger'>User with this email does not exist.</p>";
    }
}
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
        <div class="container">
            <div class="row">
                <main class="col-lg-4 col-md-6 col-sm-8 col-12 px-md-4 mx-auto py-5">
                    <div class="card">
                        <div class="card-header text-center">
                            <h5 class="card-title fw-bold">Login</h5>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate method="POST" action="">
                                <?php echo $info; ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control bg-transparent" id="email" name="email"
                                        required>
                                    <p class="invalid-feedback mb-0">Enter a valid Email!</p>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control bg-transparent" id="password"
                                        name="password" required>
                                    <p class="invalid-feedback mb-0">Password is required!</p>
                                </div>
                                <div class="mb-3">
                                    <p>Doesn't have an account? <a href="./signup.php"
                                            class="text-primary text-decoration-none">Signup Here</a></p>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary mx-auto" name="login">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="./assets/js/jquery-3.6.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/script.js"></script>
    </body>

</html>