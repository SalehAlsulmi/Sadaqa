<?php
session_start();
include '../php/db_connect.php';

// Fetch categories for filter
$categories_res = $conn->query("SELECT * FROM categories");

// Fetch active campaigns with search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT c.*, cat.name as category_name FROM campaigns c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'active'";
if ($search) {
    $query .= " AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%' OR cat.name LIKE '%$search%')";
}
$campaigns_res = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>Sadaqa - الرئيسية</title>
  <link rel="stylesheet" href="styles-merged.css"> <!-- styles -->
</head>
<body>

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
          <input type="text" name="search" id="searchInput" placeholder="ابحث عن حملة..." class="search-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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
  <h2 class="section-title">التبرعات</h2>

  <div class="campaigns-grid">
    <?php if ($campaigns_res->num_rows > 0): ?>
      <?php while($campaign = $campaigns_res->fetch_assoc()): ?>
        <div class="campaign-card">
          <a href="campaign-details.php?id=<?php echo $campaign['id']; ?>" class="card-link"></a>
          <div class="card-image-box">
            <img src="<?php echo $campaign['image_path']; ?>" alt="<?php echo $campaign['title']; ?>">
          </div>
          <a href="campaign-details.php?id=<?php echo $campaign['id']; ?>" class="card-link">
          <div class="card-info">
            <span class="campaign-category"><?php echo $campaign['category_name']; ?></span>
            <h3 class="campaign-name"><?php echo $campaign['title']; ?></h3>
            <p class="campaign-amount">المبلغ المطلوب: <span><?php echo number_format($campaign['goal_amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></span></p>
          </div>
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>لا توجد حملات نشطة حاليًا.</p>
    <?php endif; ?>
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



