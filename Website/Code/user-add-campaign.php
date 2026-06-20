<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
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

    $stmt = $conn->prepare("INSERT INTO campaign_requests (user_id, title, description, category_id, goal_amount, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issids", $user_id, $title, $description, $category_id, $goal_amount, $image_path);
    
    if ($stmt->execute()) {
        echo "<script>alert('تم إرسال طلبك بنجاح، سيتم مراجعته قريباً'); window.location.href = 'my-campaigns.php';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء إرسال الطلب');</script>";
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
  <title>Sadaqa - طلب إضافة حملة</title>
  <link rel="stylesheet" href="styles-merged.css"> <!-- styles -->
</head>
<body class="add-campaign-body">

  <header class="main-header">
    <div class="header-right">
      <a href="homepage.php">
      <img src="../images/Sadaqa logo.png" alt="Sadaqa Logo" class="nav-logo">
      </a>
      <nav class="nav-links">
        <a href="homepage.php">الصفحة الرئيسية</a>
        <a href="my-campaigns.php">حملاتك</a>
        <a href="contact-us.php">تواصل معنا</a>
      </nav>
    </div>

    <div class="header-left">
      
      <div class="filter-wrapper">
        <button class="filter-btn" id="filterToggle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
          </svg>
        </button>
        
        <div class="filter-menu" id="filterMenu">
          <h4>تصفية حسب النوع:</h4>
          <?php 
          // Reset pointer for filter menu
          $categories_res->data_seek(0);
          while($cat = $categories_res->fetch_assoc()): ?>
            <label><input type="checkbox" class="category-filter" value="<?php echo $cat['id']; ?>" checked> <?php echo $cat['name']; ?></label>
          <?php endwhile; ?>
        </div>
      </div>

      <div class="search-container">
        <form action="homepage.php" method="GET" style="width: 100%;" id="searchForm">
          <input type="text" name="search" id="searchInput" placeholder="ابحث عن حملة..." class="search-input">
        </form>
      </div>

                <div class="cart-wrapper">
        <a href="cart.php" class="cart-trigger">
            <img src="../images/cart.png" alt="Cart" class="header-icon cart-icon">
        </a>
        </div>

        <a href="profile.php" class="account-link">
        <img src="../images/AccountIcon.png" alt="Account" class="header-icon account-trigger">
        </a>

    </div>
  </header>

  <main class="main-content">
    <div class="add-campaign-container">
      <div class="add-campaign-card">
        <h2 class="section-title">طلب إضافة حملة تبرع</h2>
        <p class="add-campaign-subtitle">املأ البيانات التالية لطلب إدراج حملتك</p>

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
              // Reset pointer again for dropdown
              $categories_res->data_seek(0);
              while($cat = $categories_res->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="campaign_image">صورة للحملة</label>
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

          <button type="submit" class="submit-btn">إرسال الطلب للمراجعة</button>
        </form>
      </div>
    </div>
  </main>

  <footer class="main-footer">
    <div class="footer-container">
      <p class="copyright">جميع الحقوق محفوظة - صدقة 2026 &copy</p>

      <div class="footer-links">
        <a href="contact-us.php" class="footer-contact-btn">تواصل معنا</a>
      </div>
    </div>
  </footer>

<script src="main.js"></script>
</body>
</html>
<?php $conn->close(); ?>



