<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$categories_res = $conn->query("SELECT * FROM categories");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['campaign_name'];
    $category_id = $_POST['category'];
    $goal_amount = $_POST['goal_amount'];
    $description = $_POST['description'];
    
    // Handle Image Upload
    $image_path = '../images/Logo.png'; // Default
    if (isset($_FILES['campaign_image']) && $_FILES['campaign_image']['error'] == 0) {
        $target_dir = "../images/";
        $file_extension = pathinfo($_FILES["campaign_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["campaign_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO campaigns (title, description, category_id, goal_amount, image_path, admin_id, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssidsi", $title, $description, $category_id, $goal_amount, $image_path, $admin_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('تمت إضافة الحملة بنجاح'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء إضافة الحملة');</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>Sadaqa - إضافة حملة</title>
  <link rel="stylesheet" href="styles-merged.css"> <!-- styles -->
</head>
<body class="add-campaign-body">

  <header class="admin-header">
      <div class="small-logo-box">
        <img src="../images/Sadaqa logo.png" alt="شعار Sadaqa" class="small-logo-image" />
      </div>

      <div class="admin-header-text">
        <h1 class="admin-page-title">إضافة حملة جديدة</h1>
      </div>

      <div class="admin-header-actions">
        <a href="admin.php" class="logout-button">العودة للوحة</a>
      </div>
  </header>

  <main class="main-content">
    <div class="add-campaign-container">
      <div class="add-campaign-card">
        <h2 class="section-title">بيانات الحملة الجديدة</h2>

        <form class="add-campaign-form" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="campaign-name">اسم الحملة</label>
            <input type="text" id="campaign-name" name="campaign_name" placeholder="أدخل اسم الحملة" required>
          </div>

          <div class="form-group">
            <label for="category">نوع الحملة</label>
            <select id="category" name="category" required>
              <option value="" disabled selected>اختر النوع</option>
              <?php 
              $categories_res->data_seek(0);
              while($cat = $categories_res->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="campaign_image">صورة الحملة</label>
            <input type="file" id="campaign_image" name="campaign_image" accept="image/*" required>
          </div>

          <div class="form-group">
            <label for="goal-amount">المبلغ المطلوب</label>
            <input type="number" id="goal-amount" name="goal_amount" placeholder="0.00" required>
          </div>

          <div class="form-group">
            <label for="description">وصف الحملة</label>
            <textarea id="description" name="description" placeholder="اكتب وصفاً تفصيلياً للحملة..." required></textarea>
          </div>

          <button type="submit" class="submit-btn">إضافة الحملة الآن</button>
        </form>
      </div>
    </div>
  </main>

  <footer class="main-footer">
    <div class="footer-container">
      <p class="copyright">جميع الحقوق محفوظة - صدقة 2026 &copy</p>
    </div>
  </footer>

<script src="main.js"></script>
</body>
</html>
<?php $conn->close(); ?>



