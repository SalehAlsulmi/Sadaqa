<?php
session_start();
include '../php/db_connect.php';

$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $phone, $message);
    if ($stmt->execute()) {
        $message_sent = true;
    }
}

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
  <title>Sadaqa - تواصل معنا</title>
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
        <a href="my-campaigns.php">حملاتك</a>
        <a href="contact-us.php" class="active">تواصل معنا</a>
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
    <div class="contact-page-container">
      <div class="contact-info-box">
        <h2 class="section-title">تواصل معنا</h2>
        <p class="contact-subtitle">يسعدنا استقبال استفساراتكم واقتراحاتكم</p>
        
        <?php if ($message_sent): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                تم إرسال رسالتك بنجاح. شكرًا لتواصلك معنا!
            </div>
        <?php endif; ?>

        <form class="contact-form" method="POST">
          <div class="form-group">
            <label for="name">الاسم الكامل</label>
            <input type="text" id="name" name="full_name" placeholder="أدخل اسمك الكامل" required>
          </div>
          
          <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required>
          </div>
          
          <div class="form-group">
            <label for="phone">رقم الجوال</label>
            <input type="tel" id="phone" name="phone" placeholder="أدخل رقم جوالك" required>
          </div>
          
          <div class="form-group">
            <label for="message">الرسالة</label>
            <textarea id="message" name="message" placeholder="اكتب رسالتك هنا..." required></textarea>
          </div>
          
          <button type="submit" class="submit-btn">إرسال الرسالة</button>
        </form>
      </div>

      <div class="contact-map-box">
        <div class="map-frame-wrapper">
          <iframe class="map-frame" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3573.885311257478!2d50.19827632523201!3d26.394892682153046!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e49ef811304efab%3A0xe664343a49ebbf2b!2z2YPZhNmK2Kkg2LnZhNmI2YUg2KfZhNit2KfYs9ioINmI2KrZgtmG2YrYqSDYp9mE2YXYudmE2YjZhdin2Ko!5e0!3m2!1sar!2ssa!4v1779116610870!5m2!1sar!2ssa" width="100%" height="450" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
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



