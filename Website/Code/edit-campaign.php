<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$type = $_GET['type']; // 'campaign' or 'request'
$table = ($type == 'request') ? 'campaign_requests' : 'campaigns';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $goal_amount = $_POST['goal_amount'];
    $description = $_POST['description'];
    
    // Handle Image Upload if provided
    $image_update = "";
    if (isset($_FILES['campaign_image']) && $_FILES['campaign_image']['error'] == 0) {
        $target_dir = "../images/";
        $file_extension = pathinfo($_FILES["campaign_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["campaign_image"]["tmp_name"], $target_file)) {
            $image_update = ", image_path = '$target_file'";
        }
    }

    $sql = "UPDATE $table SET title = ?, category_id = ?, goal_amount = ?, description = ? $image_update WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidsi", $title, $category_id, $goal_amount, $description, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('تم تحديث البيانات بنجاح'); window.location.href = 'admin.php';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء التحديث');</script>";
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$categories_res = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>تعديل الحملة - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css">
</head>
<body class="add-campaign-body">
  <header class="admin-header">
      <div class="small-logo-box">
        <img src="../images/Sadaqa logo.png" alt="شعار Sadaqa" class="small-logo-image" />
      </div>
      <div class="admin-header-text">
        <h1 class="admin-page-title">تعديل بيانات الحملة</h1>
      </div>
      <div class="admin-header-actions">
        <a href="admin.php" class="logout-button">العودة للوحة</a>
      </div>
  </header>

  <main class="main-content">
    <div class="add-campaign-container">
      <div class="add-campaign-card">
        <h2 class="section-title">بيانات الحملة</h2>
        <form class="add-campaign-form" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label>اسم الحملة *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" required>
          </div>
          <div class="form-group">
            <label>تصنيف الحملة *</label>
            <select name="category_id" required>
              <?php while($cat = $categories_res->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $data['category_id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>صورة الحملة (اختياري)</label>
            <input type="file" name="campaign_image" accept="image/*">
            <?php if ($data['image_path']): ?>
                <p style="font-size: 12px; color: gray; margin-top: 5px;">الصورة الحالية: <?php echo basename($data['image_path']); ?></p>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label>المبلغ المستهدف *</label>
            <input type="number" name="goal_amount" value="<?php echo $data['goal_amount']; ?>" required>
          </div>
          <div class="form-group">
            <label>وصف الحملة *</label>
            <textarea name="description" required><?php echo htmlspecialchars($data['description']); ?></textarea>
          </div>
          <button type="submit" class="submit-btn">حفظ التغييرات</button>
        </form>
      </div>
    </div>
  </main>
<script src="main.js"></script>
</body>
</html>
<?php $conn->close(); ?>


