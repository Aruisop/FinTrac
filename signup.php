<?php
session_start();
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // ✅ Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email is already registered. Please use another email.";
    } else {
        // ✅ Insert only if email doesn't exist
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["username"] = $username;
            header("Location: login.php"); // Redirect to login page after signup
            exit();
        } else {
            $error = "Error signing up. Try again.";
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .header {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px; /* Ensures spacing between header and signup form */
        }
        .signup-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="header">FinTrac</div> <!-- Header is now properly centered above signup -->

<div class="signup-container">
    <h2 class="text-center">Sign Up</h2>

    <?php if (isset($error)) echo "<div class='alert alert-danger text-center'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Sign Up</button>
    </form>

    <p class="text-center mt-3">Already have an account?  
        <a href="login.php" class="btn btn-primary btn-sm">Login</a>
    </p>
</div>
</body>
</html>
