<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's campaign requests
$requests_res = $conn->query("SELECT cr.*, cat.name as category_name 
                              FROM campaign_requests cr 
                              JOIN categories cat ON cr.category_id = cat.id 
                              WHERE cr.user_id = $user_id 
                              ORDER BY cr.created_at DESC");

// Fetch donations from cookies
$donation_cookie_name = "user_donations_" . $user_id;
$cookie_donations = isset($_COOKIE[$donation_cookie_name]) ? json_decode($_COOKIE[$donation_cookie_name], true) : [];
// Reverse to show latest first
$cookie_donations = array_reverse($cookie_donations);

// Fetch categories for filter
$categories_res = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>Sadaqa - حملاتك</title>
  <link rel="stylesheet" href="styles-merged.css"> <!-- styles -->
</head>
<body>

  <header class="main-header">
    <div class="header-right">
      <a href="homepage.php">
      <img src="../images/Sadaqa logo.png" alt="Sadaqa Logo" class="nav-logo">
      </a>
      <nav class="nav-links">
        <a href="homepage.php">الصفحة الرئيسية</a>
        <a href="my-campaigns.php" class="active">حملاتك</a>
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

  <main class="main-content">
    <section class="my-campaigns-section">
      <h2 class="section-title">حملاتك وتبرعاتك</h2>

      <div class="campaign-status-buttons">
        <button class="status-btn active-btn">تبرعاتك</button>
        <button class="status-btn">طلباتك</button>
      </div>

      <!-- Your Donations -->
      <div class="campaign-content-box active-content">
        <div class="campaigns-grid">
          <?php if (!empty($cookie_donations)): ?>
            <?php foreach ($cookie_donations as $don): ?>
              <div class="campaign-card">
                <div class="card-image-box">
                  <img src="<?php echo $don['image_path']; ?>" alt="<?php echo $don['title']; ?>">
                </div>
                <div class="card-info">
                  <span class="campaign-category"><?php echo $don['category_name']; ?></span>
                  <h3 class="campaign-name"><?php echo $don['title']; ?></h3>
                  <p class="campaign-amount">تم التبرع بـ: <span><?php echo number_format($don['amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></span></p>
                  <p class="campaign-date" style="font-size: 0.8rem; color: gray;">بتاريخ: <?php echo $don['date']; ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>لم تقم بأي تبرعات بعد.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Your Requests -->
      <div class="campaign-content-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 class="campaign-content-title" style="margin-bottom: 0;">طلباتك المرسلة</h3>
          <a href="user-add-campaign.php" class="action-btn" style="padding: 8px 15px; font-size: 14px;">طلب إضافة حملة</a>
        </div>
        <div class="campaigns-grid">
          <?php if ($requests_res->num_rows > 0): ?>
            <?php while ($req = $requests_res->fetch_assoc()): ?>
              <div class="campaign-card">
                <div class="card-info">
                  <span class="campaign-category"><?php echo $req['category_name']; ?></span>
                  <h3 class="campaign-name"><?php echo $req['title']; ?></h3>
                  <p class="campaign-amount">المبلغ المطلوب: <span><?php echo number_format($req['goal_amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></span></p>
                  <p class="campaign-status" style="font-weight: bold; color: <?php echo ($req['status'] == 'approved' ? 'green' : ($req['status'] == 'rejected' ? 'red' : 'orange')); ?>;">
                    الحالة: <?php 
                      if($req['status'] == 'pending') echo 'قيد المراجعة';
                      elseif($req['status'] == 'approved') echo 'تمت الموافقة';
                      elseif($req['status'] == 'rejected') echo 'مرفوض';
                    ?>
                  </p>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p>لا توجد طلبات حملات حاليًا.</p>
          <?php endif; ?>
        </div>
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

<script src="main.js"></script>
</body>
</html>
<?php $conn->close(); ?>

