<?php
session_start();
include '../php/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: homepage.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT c.*, cat.name as category_name FROM campaigns c JOIN categories cat ON c.category_id = cat.id WHERE c.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: homepage.php");
    exit();
}

$campaign = $result->fetch_assoc();
$goal = $campaign['goal_amount'];
$current = $campaign['current_amount'];
$remaining = $goal - $current;
$progress = ($current / $goal) * 100;

// Check for edit mode from cart
$edit_cart_id = isset($_GET['edit_cart_id']) ? $_GET['edit_cart_id'] : null;
$edit_amount = isset($_GET['amount']) ? $_GET['amount'] : '';

// Fetch categories for filter
$categories_res = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <title>تفاصيل الحملة - <?php echo $campaign['title']; ?></title>
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
    <main class="details-container">
        <div class="campaign-detail-card">
            
            <div class="detail-header">
                <div class="detail-image">
                    <img src="<?php echo $campaign['image_path']; ?>" alt="<?php echo $campaign['title']; ?>">
                </div>
                <div class="detail-main-info">
                    <span class="campaign-category"><?php echo $campaign['category_name']; ?></span>
                    <h1 class="campaign-title"><?php echo $campaign['title']; ?></h1>
                    <p class="campaign-description"><?php echo $campaign['description']; ?></p>
                </div>
            </div>

            <div class="amounts-section">
                <div class="amount-item total">
                    <label>المبلغ الكلي</label>
                    <p><?php echo number_format($goal); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></p>
                </div>
                <div class="amount-item collected">
                    <label>تم تجميع</label>
                    <p><?php echo number_format($current); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></p>
                </div>
                <div class="amount-item remaining">
                    <label>المتبقي</label>
                    <p><?php echo number_format($remaining); ?> <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol"></p>
                </div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
            </div>

            <div class="donation-action-box">
    <form action="../php/donate_process.php" method="POST">
        <input type="hidden" name="campaign_id" value="<?php echo $id; ?>">
        <?php if ($edit_cart_id): ?>
            <input type="hidden" name="cart_item_id" value="<?php echo $edit_cart_id; ?>">
        <?php endif; ?>
        <label>أدخل مبلغ التبرع</label>
        
        <div class="input-with-symbol">
            <input type="number" name="amount" placeholder="0.00" class="donation-input" id="donationAmount" min="1" required value="<?php echo $edit_amount; ?>" oninput="validateAmount(this)">
            <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="inner-currency-symbol">
            <span class="tooltip-container donation-tooltip">
              <span class="help-icon">i</span>
              <span class="tooltip-text">الرجاء إدخال المبلغ بصيغة أرقام فقط (مثال: 150)</span>
            </span>
        </div>
        <p id="amountError" style="color: #c0392b; font-size: 13px; margin-top: 5px; display: none; font-weight: bold;"></p>
        
        <div class="quick-amounts">
            <button type="button" onclick="setAmount(10)">10 <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol-btn"></button>
            <button type="button" onclick="setAmount(50)">50 <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol-btn"></button>
            <button type="button" onclick="setAmount(100)">100 <img src="../images/Saudi_Riyal_Symbol.png" alt="ر.س" class="currency-symbol-btn"></button>
        </div>

        <div class="action-buttons">
            <?php if ($edit_cart_id): ?>
                <button type="submit" name="action" value="update_cart" class="donate-now-btn" style="width: 100%; background-color: #d35400; border-color: #d35400;">تعديل</button>
            <?php else: ?>
                <button type="submit" name="action" value="add_to_cart" class="add-to-cart-btn">إضافة للسلة</button>
                <button type="submit" name="action" value="donate_now" class="donate-now-btn">التبرع الآن</button>
            <?php endif; ?>
        </div>
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
<script>
const remainingAmount = <?php echo $remaining; ?>;
function validateAmount(input) {
    const errorMsg = document.getElementById('amountError');
    const submitBtns = document.querySelectorAll('button[type="submit"]');
    const val = parseFloat(input.value);

    if (val > remainingAmount) {
        errorMsg.textContent = `أعلى مبلغ يمكنك التبرع به هو ${remainingAmount.toLocaleString()}`;
        errorMsg.style.display = 'block';
        submitBtns.forEach(btn => btn.disabled = true);
    } else {
        errorMsg.style.display = 'none';
        submitBtns.forEach(btn => btn.disabled = false);
    }
}
</script>
</body>
</html>
<?php $conn->close(); ?>



