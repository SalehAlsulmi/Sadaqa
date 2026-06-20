<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if email already exists
    $checkEmail = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "<script>alert('البريد الإلكتروني مسجل مسبقًا'); window.history.back();</script>";
    } else {
        if ($password === $confirmPassword) {
            // Insert user
            $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                $user_id = $conn->insert_id;
                // Create profile
                $conn->query("INSERT INTO profiles (user_id) VALUES ($user_id)");
                echo "<script>alert('تم إنشاء الحساب بنجاح'); window.location.href = '../Code/login.php';</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "<script>alert('كلمات المرور غير متطابقة'); window.history.back();</script>";
        }
    }
}
$conn->close();
?>


