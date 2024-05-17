<?php
// Include database connection
include 'db_conn.php';

// Initialize $info variable
$info = "";

// Check if the signup form is submitted
if (isset($_POST['signup'])) {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $city = $_POST['city'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if password matches confirm password
    if ($password !== $confirm_password) {
        $info = "<p class='alert alert-danger'>
                    <span class='lang-en'>Error: Passwords do not match.</span>
                    <span class='lang-de'>Fehler: Passwörter stimmen nicht überein.</span>
                </p>";
    } else {
        // Check if user with the same email already exists
        $check_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $info = "<p class='alert alert-danger'>
                        <span class='lang-en'>Error: User with this email already exists.</span>
                        <span class='lang-de'>Fehler: Benutzer mit dieser E-Mail-Adresse existiert bereits.</span>
                    </p>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user data into database
            $insert_query = "INSERT INTO users (name, email, password, city) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $city);
            if ($stmt->execute()) {
                // User signed up successfully
                $info = "<p class='alert alert-success'>
                            <span class='lang-en'>Registered successfully!</span>
                            <span class='lang-de'>Erfolgreich registriert!</span>
                        </p>";
            } else {
                $info = "<p class='alert alert-danger'>
                            <span class='lang-en'>Error: </span>" . $conn->error . "
                            <span class='lang-de'>Fehler: </span>" . $conn->error . "
                        </p>";
            }
        }
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
        <title>MISSIONBERLIN2024</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="./assets/fontawesome/css/all.css">
        <link rel="stylesheet" href="./assets/css/style.css?v=2">
    </head>

    <body>
        <div class="container">
            <div class="row">
                <main class="col-lg-4 col-md-6 col-sm-8 col-12 px-md-4 mx-auto py-5">
                    <div class="card">
                        <div class="card-header text-center">
                            <h5 class="card-title fw-bold">
                                <span class="lang-en">Sign Up</span>
                                <span class="lang-de">Registrieren</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate method="POST" action="">
                                <?php echo $info; ?>
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <span class="lang-en">Name</span>
                                        <span class="lang-de">Name</span>
                                    </label>
                                    <input type="text" class="form-control bg-transparent" id="name" name="name"
                                        required>
                                    <p class="invalid-feedback mb-0">
                                        <span class="lang-en">Name is required!</span>
                                        <span class="lang-de">Name ist erforderlich!</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <span class="lang-en">Email</span>
                                        <span class="lang-de">E-Mail</span>
                                    </label>
                                    <input type="email" class="form-control bg-transparent" id="email" name="email"
                                        required>
                                    <p class="invalid-feedback mb-0">
                                        <span class="lang-en">Enter a valid Email!</span>
                                        <span class="lang-de">Geben Sie eine gültige E-Mail-Adresse ein!</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="text" class="form-label">
                                        <span class="lang-en">City</span>
                                        <span class="lang-de">Stadt</span>
                                    </label>
                                    <input type="text" class="form-control bg-transparent" id="text" name="city"
                                        required>
                                    <p class="invalid-feedback mb-0">
                                        <span class="lang-en">Enter a valid City!</span>
                                        <span class="lang-de">Geben Sie eine gültige Stadt ein!</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <span class="lang-en">Password</span>
                                        <span class="lang-de">Passwort</span>
                                    </label>
                                    <input type="password" minlength="4" class="form-control bg-transparent"
                                        id="password" name="password" required>
                                    <p class="invalid-feedback mb-0">
                                        <span class="lang-en">Password should be at least 4 characters!</span>
                                        <span class="lang-de">Das Passwort muss mindestens 4 Zeichen lang sein!</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <span class="lang-en">Confirm Password</span>
                                        <span class="lang-de">Passwort bestätigen</span>
                                    </label>
                                    <input type="password" minlength="4" class="form-control bg-transparent"
                                        id="confirm_password" name="confirm_password" required>
                                    <p class="invalid-feedback mb-0">
                                        <span class="lang-en">Password should be at least 4 characters!</span>
                                        <span class="lang-de">Das Passwort muss mindestens 4 Zeichen lang sein!</span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <p>
                                        <span class="lang-en">Already have an account?</span>
                                        <span class="lang-de">Haben Sie bereits ein Konto?</span>
                                        <a href="./login.php" class="text-primary text-decoration-none">
                                            <span class="lang-en">Login Here</span>
                                            <span class="lang-de">Hier einloggen</span>
                                        </a>
                                    </p>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary mx-auto" name="signup">
                                        <span class="lang-en">Sign Up</span>
                                        <span class="lang-de">Registrieren</span>
                                    </button>
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
        <script src="./assets/js/script.js?v=2"></script>
    </body>

</html>