<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch categories for filter
$categories_res = $conn->query("SELECT * FROM categories");

// Handle Checkout
if (isset($_POST['checkout'])) {
    $conn->begin_transaction();
    try {
        $cart_res = $conn->query("SELECT ci.*, c.title, c.image_path, cat.name as category_name 
                                  FROM cart_items ci 
                                  JOIN campaigns c ON ci.campaign_id = c.id 
                                  JOIN categories cat ON c.category_id = cat.id 
                                  WHERE ci.user_id = $user_id");
        
        $donation_cookie_name = "user_donations_" . $user_id;
        $existing_donations = isset($_COOKIE[$donation_cookie_name]) ? json_decode($_COOKIE[$donation_cookie_name], true) : [];

        while ($item = $cart_res->fetch_assoc()) {
            $campaign_id = $item['campaign_id'];
            $amount = $item['amount'];

            // Add donation
            $stmt1 = $conn->prepare("INSERT INTO donations (user_id, campaign_id, amount) VALUES (?, ?, ?)");
            $stmt1->bind_param("iid", $user_id, $campaign_id, $amount);
            $stmt1->execute();

            // Update campaign
            $stmt2 = $conn->prepare("UPDATE campaigns SET current_amount = current_amount + ? WHERE id = ?");
            $stmt2->bind_param("di", $amount, $campaign_id);
            $stmt2->execute();

            // Check if campaign is completed
            $check_stmt = $conn->prepare("SELECT goal_amount, current_amount FROM campaigns WHERE id = ?");
            $check_stmt->bind_param("i", $campaign_id);
            $check_stmt->execute();
            $camp_data = $check_stmt->get_result()->fetch_assoc();
            if ($camp_data['current_amount'] >= $camp_data['goal_amount']) {
                $conn->query("UPDATE campaigns SET status = 'inactive' WHERE id = $campaign_id");
            }

            // Save to cookies
            $existing_donations[] = [
                'campaign_id' => $campaign_id,
                'title' => $item['title'],
                'amount' => $amount,
                'category_name' => $item['category_name'],
                'image_path' => $item['image_path'],
                'date' => date('Y-m-d')
            ];
        }
        setcookie($donation_cookie_name, json_encode($existing_donations), time() + (86400 * 365), "/");

        // Clear cart
        $conn->query("DELETE FROM cart_items WHERE user_id = $user_id");
        $conn->commit();
        echo "<script>alert('تم إتمام التبرع بنجاح، شكرًا لك!'); window.location.href = 'homepage.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('حدث خطأ أثناء إتمام التبرع');</script>";
    }
}

// Handle Remove
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $conn->query("DELETE FROM cart_items WHERE id = $cart_id AND user_id = $user_id");
    header("Location: cart.php");
    exit();
}

// Handle Clear All
if (isset($_GET['clear_all'])) {
    $conn->query("DELETE FROM cart_items WHERE user_id = $user_id");
    header("Location: cart.php");
    exit();
}

$cart_res = $conn->query("SELECT ci.*, c.title, c.image_path, cat.name as category_name 
                          FROM cart_items ci 
                          JOIN campaigns c ON ci.campaign_id = c.id 
                          JOIN categories cat ON c.category_id = cat.id 
                          WHERE ci.user_id = $user_id");

$total_campaigns = $cart_res->num_rows;
$total_amount = 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>السلة - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css" /> <!-- styles1 -->
</head>
<body class="cart-page-body">

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

    <main class="main-content cart-main-content">
      <section class="cart-page-section">
        <div class="cart-header-row">
          <h1 class="section-title">سلة التبرعات</h1>
          <div style="display: flex; gap: 15px; align-items: center;">
            <span class="cart-items-count">عدد الحملات: <?php echo $total_campaigns; ?></span>
            <?php if ($total_campaigns > 0): ?>
              <a href="cart.php?clear_all=1" class="remove-btn" onclick="return confirm('هل أنت متأكد من حذف جميع العناصر؟')" style="text-decoration:none; color:white; background-color: #c0392b; padding: 5px 12px; border-radius: 8px; font-size: 13px;">حذف الكل</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="cart-layout">

          <div class="cart-items-list">
            <?php if ($total_campaigns > 0): ?>
              <?php while ($item = $cart_res->fetch_assoc()): 
                  $total_amount += $item['amount'];
              ?>
                <div class="cart-item-card">
                  <div class="cart-item-image">
                    <img src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['category_name']; ?>">
                  </div>

                  <div class="cart-item-details">
                    <span class="cart-item-category"><?php echo $item['category_name']; ?></span>
                    <h3 class="cart-item-name"><?php echo $item['title']; ?></h3>
                    <p class="cart-item-amount">
                      مبلغ التبرع:
                      <span><?php echo number_format($item['amount']); ?>
                        <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon" alt="ريال">
                      </span>
                    </p>
                  </div>

                  <div class="cart-item-actions" style="display: flex; gap: 10px;">
                    <a href="campaign-details.php?id=<?php echo $item['campaign_id']; ?>&edit_cart_id=<?php echo $item['id']; ?>&amount=<?php echo $item['amount']; ?>" class="edit-btn" style="text-decoration:none; color:white; background-color: #d35400; padding: 8px 15px; border-radius: 8px; font-size: 13px;">تعديل</a>
                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn" style="text-decoration:none; color:white; padding: 8px 15px; border-radius: 8px; font-size: 13px;">حذف</a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>سلة التبرعات فارغة.</p>
            <?php endif; ?>

          </div>

          <aside class="cart-summary-card">
            <h2 class="cart-summary-title">ملخص التبرع</h2>

            <div class="summary-row">
              <span>عدد الحملات</span>
              <span><?php echo $total_campaigns; ?></span>
            </div>

            <div class="summary-row">
              <span>إجمالي التبرعات</span>
              <span>
                <?php echo number_format($total_amount); ?>
                <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon" alt="ريال">
              </span>
            </div>

            <div class="summary-row total-row">
              <span>المجموع النهائي</span>
              <span>
                <?php echo number_format($total_amount); ?>
                <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon" alt="ريال">
              </span>
            </div>

            <form method="POST">
                <button class="checkout-btn" name="checkout" type="submit" <?php echo ($total_campaigns == 0) ? 'disabled' : ''; ?>>إتمام التبرع</button>
            </form>
          </aside>

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
</body>
</html>
<?php $conn->close(); ?>



