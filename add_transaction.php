<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $category_id = $_POST['category_id'];
    $date = $_POST['date'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO transactions (user_id, amount, category_id, date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $amount, $category_id, $date);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Fetch categories
$categories = $conn->query("SELECT id, name FROM categories");
if ($categories->num_rows == 0) {
    die("No categories found. Please ensure the 'categories' table has data.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add Transaction</h1>
    <form method="POST" action="">
        <input type="number" step="0.01" name="amount" placeholder="Amount" required><br>
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
            <?php endwhile; ?>
        </select><br>
        <input type="date" name="date" required><br>
        <button type="submit">Add</button>
    </form>
</body>
</html>
