<?php
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_amount = $_POST['target_amount'];
    $deadline = $_POST['deadline'];

    // Insert or update the goal
    $sql = "REPLACE INTO goals (user_id, target_amount, deadline) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $user_id, $target_amount, $deadline);
    $stmt->execute();
    $stmt->close();

    $message = "Goal set successfully!";
}

// Fetch the current goal (if any)
$sql = "SELECT * FROM goals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goal_result = $stmt->get_result();
$current_goal = $goal_result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set Goal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Set Financial Goal</h1>
    <?php if (isset($message)) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="POST" action="">
        <label for="target_amount">Target Amount:</label><br>
        <input type="number" step="0.01" name="target_amount" value="<?php echo $current_goal['target_amount'] ?? ''; ?>" required><br>
        <label for="deadline">Deadline:</label><br>
        <input type="date" name="deadline" value="<?php echo $current_goal['deadline'] ?? ''; ?>" required><br>
        <button type="submit">Set Goal</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
