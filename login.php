<?php 
session_start();
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"]; // Can be username or email
    $password = $_POST["password"];

    // ✅ Check if login is an email or username
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    }
    
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            header("Location: homepage.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
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
    <title>Login</title>
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
            margin-bottom: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">FinTrac</div>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">Don't have an account?  
            <a href="signup.php" class="btn btn-success btn-sm">Sign Up</a>
        </p>
    </div>
</body>
</html>