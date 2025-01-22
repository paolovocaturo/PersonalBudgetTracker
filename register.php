<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the username already exists
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Username already exists. Please choose a different one.";
    } else {
        // Insert the new user into the database
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        $success = "Registration successful! You can now <a href='index.php'>log in</a>.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Register</h1>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Choose a Username" required><br>
        <input type="password" name="password" placeholder="Choose a Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="index.php">Log in</a></p>
</body>
</html>
