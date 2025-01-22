<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $sql = "SELECT * FROM transactions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['id'];
    $amount = $_POST['amount'];
    $category_id = $_POST['category_id'];
    $date = $_POST['date'];

    $sql = "UPDATE transactions SET amount = ?, category_id = ?, date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssi", $amount, $category_id, $date, $transaction_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

$categories = $conn->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Edit Transaction</h1>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
        <input type="number" step="0.01" name="amount" value="<?php echo $transaction['amount']; ?>" required><br>
        <select name="category_id" required>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $transaction['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $row['name']; ?>
                </option>
            <?php endwhile; ?>
        </select><br>
        <input type="date" name="date" value="<?php echo $transaction['date']; ?>" required><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
