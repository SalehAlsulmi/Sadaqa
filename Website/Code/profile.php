<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch profile info
$stmt = $conn->prepare("SELECT u.email, p.total_donations_collected 
                        FROM users u 
                        JOIN profiles p ON u.id = p.user_id 
                        WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Calculate actual total donations from donations table
$don_stmt = $conn->prepare("SELECT SUM(amount) as total FROM donations WHERE user_id = ?");
$don_stmt->bind_param("i", $user_id);
$don_stmt->execute();
$don_result = $don_stmt->get_result()->fetch_assoc();
$actual_total = $don_result['total'] ?? 0;

// Update profile total if it differs (simple sync)
if ($actual_total != $profile['total_donations_collected']) {
    $conn->query("UPDATE profiles SET total_donations_collected = $actual_total WHERE user_id = $user_id");
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_email = $_POST['email'] ?? $profile['email'];
    $new_password = $_POST['password'] ?? '';

    if (!empty($new_email)) {
        if (!empty($new_password)) {
            $update_stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $new_email, $new_password, $user_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_email, $user_id);
        }

        if ($update_stmt->execute()) {
            $_SESSION['user_email'] = $new_email;
            echo "<script>alert('تم تحديث البيانات بنجاح'); window.location.href='profile.php';</script>";
            exit();
        } else {
            echo "<script>alert('حدث خطأ أثناء التحديث');</script>";
        }
    }
}

// Fetch categories for filter
$categories_res = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>الملف الشخصي - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css" /> <!-- styles -->
</head>
<body class="profile-page-body">

  <div class="page-wrapper">

    <header class="main-header">
    <div class="header-right">
      <a href="homepage.php">
      <img src="../images/Sadaqa logo.png" alt="Sadaqa Logo" class="nav-logo">
      </a>
      <nav class="nav-links">
        <a href="homepage.php" class="active">الصفحة الرئيسية</a>
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
          <?php while($cat = $categories_res->fetch_assoc()): ?>
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

    <main class="main-content profile-main-content">
      <section class="profile-card">
        <div class="profile-header">
          <div class="avatar-box">
            <img src="../images/AccountIcon.png" alt="صورة المستخدم" class="profile-avatar" />
          </div>
          <h1 class="user-name">مرحباً بك</h1>
          <div class="user-info-row" style="margin-bottom: 15px;">
            <p class="user-email" style="display: inline-block; margin: 0;"><?php echo $profile['email']; ?></p>
            <button type="button" class="edit-text-btn" onclick="toggleEdit('email')" style="background: none; border: none; color: var(--secondary); cursor: pointer; font-size: 14px; margin-right: 10px; text-decoration: underline;">تغيير الإيميل</button>
          </div>
          <div class="user-info-row" style="margin-bottom: 25px;">
            <p style="display: inline-block; margin: 0;">كلمة المرور: ********</p>
            <button type="button" class="edit-text-btn" onclick="toggleEdit('password')" style="background: none; border: none; color: var(--secondary); cursor: pointer; font-size: 14px; margin-right: 10px; text-decoration: underline;">تغيير الباسوورد</button>
          </div>
        </div>

        <div id="edit-form-container" style="display: none; background: #fcf9f7; padding: 25px; border-radius: 15px; margin-bottom: 30px; text-align: right; border: 1px dashed var(--secondary);">
          <h3 style="color: var(--primary); margin-bottom: 20px; font-size: 18px;">تحديث بيانات الحساب</h3>
          <form method="POST" id="updateProfileForm">
            <div id="email-edit-group" class="form-group" style="display: none;">
              <label>البريد الإلكتروني الجديد</label>
              <input type="email" name="email" value="<?php echo $profile['email']; ?>" class="form-input">
            </div>
            <div id="password-edit-group" class="form-group" style="display: none;">
              <label>كلمة المرور الجديدة</label>
              <input type="password" name="password" placeholder="أدخل كلمة المرور الجديدة" class="form-input">
            </div>
            <div style="display: flex; gap: 10px;">
              <button type="submit" name="update_profile" class="action-btn" style="flex: 1; cursor: pointer;">تأكيد التعديل</button>
              <button type="button" class="action-btn" onclick="cancelEdit()" style="background: #999; flex: 1; cursor: pointer;">إلغاء</button>
            </div>
          </form>
        </div>

        <div class="profile-stats">
          <div class="stat-item">
            <span class="stat-label">إجمالي التبرعات</span>
            <span class="stat-value">
              <?php echo number_format($actual_total); ?>
              <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon" alt="ريال">
            </span>
          </div>
        </div>

        <div class="profile-actions">
          <a href="../php/logout.php" class="action-btn logout-btn">تسجيل الخروج</a>
        </div>
      </section>
    </main>

    <footer class="main-footer">
      <div class="footer-container">
        <p class="copyright">جميع الحقوق محفوظة - صدقة 2026 &copy</p>

        <div class="footer-links">
          <a href="contact-us.php" class="footer-contact-btn">تواصل معنا</a>
        </div>
      </div>
    </footer>

  </div>

<script src="main.js"></script>
<script>
function toggleEdit(type) {
    document.getElementById('edit-form-container').style.display = 'block';
    if (type === 'email') {
        document.getElementById('email-edit-group').style.display = 'block';
        document.getElementById('password-edit-group').style.display = 'none';
    } else {
        document.getElementById('password-edit-group').style.display = 'block';
        document.getElementById('email-edit-group').style.display = 'none';
    }
}

function cancelEdit() {
    document.getElementById('edit-form-container').style.display = 'none';
}

document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
    const emailField = document.querySelector('input[name="email"]');
    const passwordField = document.querySelector('input[name="password"]');
    
    let message = 'هل أنت متأكد من تأكيد التغييرات؟';
    if (emailField.parentElement.style.display !== 'none' && passwordField.parentElement.style.display !== 'none') {
        message = 'هل أنت متأكد من تغيير الإيميل والباسوورد؟';
    } else if (emailField.parentElement.style.display !== 'none') {
        message = 'هل أنت متأكد من تغيير الإيميل؟';
    } else if (passwordField.parentElement.style.display !== 'none') {
        message = 'هل أنت متأكد من تغيير الباسوورد؟';
    }

    if (!confirm(message)) {
        e.preventDefault();
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>



