<?php
session_start();
include('connection.php');

$connection = new Connection();
$pdo = $connection->OpenConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Handle login
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Fetch user data from the database
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Check if user exists and passwords match
        if ($user && password_verify($password, $user->password)) { 
            $_SESSION['username'] = $user->username;
            header("Location: index.php");
            exit;
        } else {
            $error = "SAYOP AMAW.";
        }
    } elseif (isset($_POST['register'])) {
        // Handle registration
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $birthdate = $_POST['birthdate'];
        $gender = $_POST['gender'];
        $username = $_POST['reg_username'];
        $password = $_POST['reg_password'];

        // Check if username already exists
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':username' => $username]);
        $existing_user = $stmt->fetch();

        if ($existing_user) {
            $error = "Username already exists.";
        } else {
            // Insert new user into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute([':username' => $username, ':password' => $hashed_password]);

            if ($result) {
                // Now insert into the register table
                $query = "INSERT INTO register (first_name, last_name, address, birthdate, gender, username, password, role, date_created) 
                          VALUES (:first_name, :last_name, :address, :birthdate, :gender, :username, :password, 'user', NOW())";
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':address' => $address,
                    ':birthdate' => $birthdate,
                    ':gender' => $gender,
                    ':username' => $username,
                    ':password' => $hashed_password // Store hashed password
                ]);

                if ($result) {
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Registration failed in the register table. Please try again.";
                }
            } else {
                $error = "Failed to create user in the users table.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>I P A S O L O D</h2>
    <?php if ($error) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form action="login.php" method="POST">
        <h4>Login</h4>
        <div class="mb-3">
            <label for="username" class="form-label">Ipasolod imong ... user</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Ipasolod imong ... pasword</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Login</button>
    </form>

    <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal" data-bs-target="#registrationModal">
        Register
    </button>

    <!-- Registration Modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">Rehistro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate</label>
                            <input type="date" name="birthdate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reg_username" class="form-label">Username</label>
                            <input type="text" name="reg_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_password" class="form-label">Password</label>
                            <input type="password" name="reg_password" class="form-control" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
