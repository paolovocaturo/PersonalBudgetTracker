<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

// Get the user's ID
$user_id = $_SESSION['user_id'];

// Fetch transactions with category names
$sql = "SELECT transactions.*, categories.name AS category_name 
        FROM transactions 
        JOIN categories ON transactions.category_id = categories.id 
        WHERE transactions.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the user's goal
$goal_query = "SELECT * FROM goals WHERE user_id = ?";
$stmt = $conn->prepare($goal_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goal_result = $stmt->get_result();
$goal = $goal_result->fetch_assoc();

// Calculate progress (if a goal exists)
$progress = 0;
if ($goal) {
    $spent_query = "SELECT SUM(amount) AS total_spent FROM transactions WHERE user_id = ?";
    $stmt = $conn->prepare($spent_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $spent_result = $stmt->get_result();
    $spent = $spent_result->fetch_assoc()['total_spent'] ?? 0;

    $progress = ($spent / $goal['target_amount']) * 100;
    $progress = min($progress, 100); // Cap progress at 100%
}

// Fetch data for the pie chart
$chart_data_query = "SELECT categories.name AS category, SUM(transactions.amount) AS total 
                     FROM transactions 
                     JOIN categories ON transactions.category_id = categories.id 
                     WHERE transactions.user_id = ? 
                     GROUP BY categories.id";
$stmt = $conn->prepare($chart_data_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$chart_result = $stmt->get_result();

$categories = [];
$totals = [];

while ($row = $chart_result->fetch_assoc()) {
    $categories[] = $row['category'];
    $totals[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="logout">
        <button class="button" onclick="window.location.href='logout.php';">Logout</button>
    </div>
    <h1>Dashboard</h1>
    <button class="button" onclick="window.location.href='add_transaction.php';">Add Transaction</button>
    
    <h2>Your Spending Limit</h2>
    <?php if ($goal): ?>
        <p>Maximum Spend: $<?php echo $goal['target_amount']; ?></p>
        <p>Deadline: <?php echo $goal['deadline']; ?></p>
        <p>Usage: <?php echo round($progress, 2); ?>%</p>
        <button class="button" onclick="window.location.href='set_goal.php';">Update Spending Limit</button>
    <?php else: ?>
        <p>No spending limit set.</p>
        <button class="button" onclick="window.location.href='set_goal.php';">Set a Spending Limit</button>
    <?php endif; ?>
    
    <h2>Your Transactions</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <?php echo $row['date'] . ': ' . $row['category_name'] . ' - $' . $row['amount']; ?>
                <button class="button" onclick="window.location.href='edit_transaction.php?id=<?php echo $row['id']; ?>';">Edit</button> 
                <button class="button" onclick="if(confirm('Are you sure you want to delete this transaction?')) window.location.href='delete_transaction.php?id=<?php echo $row['id']; ?>';">Delete</button>
            </li>
        <?php endwhile; ?>
    </ul>

    <h2>Expenses by Category</h2>
    <div style="text-align: center;">
        <canvas id="expenseChart" style="max-width: 300px; margin: 0 auto;"></canvas>
    </div>
    <script>
        const categories = <?php echo json_encode($categories); ?>;
        const totals = <?php echo json_encode($totals); ?>;

        const ctx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Expenses by Category',
                    data: totals,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': $' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                layout: {
                    padding: 10
                }
            }
        });
    </script>
</body>
</html>
