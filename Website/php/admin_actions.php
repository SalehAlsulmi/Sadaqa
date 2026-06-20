<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Code/login.php");
    exit();
}

$action = $_GET['action'];
$id = $_GET['id'];

if ($action == 'approve') {
    // Fetch request details
    $req = $conn->query("SELECT * FROM campaign_requests WHERE id = $id")->fetch_assoc();
    if ($req) {
        // Insert into campaigns
        $stmt = $conn->prepare("INSERT INTO campaigns (title, description, category_id, goal_amount, image_path, user_id, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssidsi", $req['title'], $req['description'], $req['category_id'], $req['goal_amount'], $req['image_path'], $req['user_id']);
        if ($stmt->execute()) {
            // Update request status
            $conn->query("UPDATE campaign_requests SET status = 'approved' WHERE id = $id");
        }
    }
} elseif ($action == 'reject') {
    $conn->query("UPDATE campaign_requests SET status = 'rejected' WHERE id = $id");
} elseif ($action == 'deactivate') {
    $conn->query("UPDATE campaigns SET status = 'inactive' WHERE id = $id");
} elseif ($action == 'activate') {
    $conn->query("UPDATE campaigns SET status = 'active' WHERE id = $id");
} elseif ($action == 'delete') {
    $conn->query("DELETE FROM campaigns WHERE id = $id");
}

header("Location: ../Code/admin.php");
exit();
?>


