<?php
include 'config.php'; // Include the database connection file

// Define the username and password you want to create
$username = 'testuser'; // Replace with your desired username
$password = password_hash('testpassword', PASSWORD_DEFAULT); // Replace with your desired password

// SQL query to insert the new user
$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql); // Prepare the query
$stmt->bind_param("ss", $username, $password); // Bind parameters

// Execute the query and check if it succeeded
if ($stmt->execute()) {
    echo "User created successfully!";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
