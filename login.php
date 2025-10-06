<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // For demo only

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['unit'] = $user['unit']; // 👈 store unit
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ICT Helpdesk</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: url('SDObg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.60);
            border-radius: 12px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-container img {
            width: 90px;
            margin-bottom: 15px;
        }

        .login-container h1 {
            font-size: 20px;
            margin: 5px 0 15px;
            color: #333;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #444;
        }

        label {
            display: block;
            margin-top: 12px;
            text-align: left;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: 94%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #007BFF;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            margin: 10px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <img src="SDOlogo.png" alt="SDO Logo">
    <h1>Schools Division Office of San Pedro City</h1>
    <h2>Login</h2>
    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST" id="login-form">
        <label>Username</label>
        <input type="text" name="username" id="username" required>
        
        <label>Password</label>
        <input type="password" name="password" id="password" required>
        
        <button type="submit" id="submitlogin">Login</button>
    </form>
</div>
</body>
</html>
