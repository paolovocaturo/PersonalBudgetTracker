<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $sql = "DELETE FROM transactions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
}

header("Location: dashboard.php");
exit();
?>
