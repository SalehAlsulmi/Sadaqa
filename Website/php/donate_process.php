<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('يرجى تسجيل الدخول أولاً'); window.location.href = '../Code/login.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $campaign_id = $_POST['campaign_id'];
    $amount = $_POST['amount'];
    $action = $_POST['action'];

    if ($action === 'donate_now') {
        // Add to cart and redirect to cart page
        $stmt = $conn->prepare("INSERT INTO cart_items (user_id, campaign_id, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $user_id, $campaign_id, $amount);
        if ($stmt->execute()) {
            header("Location: ../Code/cart.php");
            exit();
        } else {
            echo "<script>alert('حدث خطأ أثناء الإضافة للسلة'); window.history.back();</script>";
        }
    } elseif ($action === 'add_to_cart') {
        $stmt = $conn->prepare("INSERT INTO cart_items (user_id, campaign_id, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $user_id, $campaign_id, $amount);
        if ($stmt->execute()) {
            echo "<script>alert('تمت الإضافة إلى السلة'); window.location.href = '../Code/campaign-details.php?id=$campaign_id';</script>";
        } else {
            echo "<script>alert('حدث خطأ أثناء الإضافة للسلة'); window.history.back();</script>";
        }
    } elseif ($action === 'update_cart') {
        $cart_item_id = $_POST['cart_item_id'];
        $stmt = $conn->prepare("UPDATE cart_items SET amount = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("dii", $amount, $cart_item_id, $user_id);
        if ($stmt->execute()) {
            header("Location: ../Code/cart.php");
            exit();
        } else {
            echo "<script>alert('حدث خطأ أثناء تحديث السلة'); window.history.back();</script>";
        }
    }
}
$conn->close();
?>


