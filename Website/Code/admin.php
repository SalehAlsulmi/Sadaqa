<?php
session_start();
include '../php/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Search Logic
$search_query = isset($_GET['admin_search']) ? $conn->real_escape_string($_GET['admin_search']) : '';
$search_filter = "";
if ($search_query) {
    $search_filter = " AND (c.title LIKE '%$search_query%' OR cat.name LIKE '%$search_query%')";
    $req_search_filter = " AND (cr.title LIKE '%$search_query%' OR cat.name LIKE '%$search_query%')";
} else {
    $req_search_filter = "";
}

// Fetch active campaigns
$active_res = $conn->query("SELECT c.*, cat.name as category_name FROM campaigns c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'active' $search_filter");

// Fetch inactive campaigns
$inactive_res = $conn->query("SELECT c.*, cat.name as category_name FROM campaigns c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'inactive' $search_filter");

// Fetch pending requests
$pending_res = $conn->query("SELECT cr.*, cat.name as category_name FROM campaign_requests cr JOIN categories cat ON cr.category_id = cat.id WHERE cr.status = 'pending' $req_search_filter");

// Fetch contact messages
$messages_res = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

// Fetch Donation Statistics by Category
$stats_query = "SELECT cat.name, SUM(d.amount) as total_amount 
                FROM donations d 
                JOIN campaigns c ON d.campaign_id = c.id 
                JOIN categories cat ON c.category_id = cat.id 
                GROUP BY cat.id";
$stats_res = $conn->query($stats_query);

// Total Amount
$total_all_res = $conn->query("SELECT SUM(amount) as total FROM donations");
$total_all = $total_all_res->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>لوحة الإدارة - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css" /> <!-- styles1 -->
</head>
<body class="admin-page-body">

  <div class="page-wrapper">

    <header class="admin-header">
      <div class="small-logo-box">
        <img src="../images/Sadaqa logo.png" alt="شعار Sadaqa" class="small-logo-image" />
      </div>

      <div class="admin-header-text">
        <h1 class="admin-page-title">لوحة الإدارة</h1>
        <p class="admin-page-subtitle">إدارة الحملات والطلبات ورسائل التواصل</p>
      </div>

      <div class="admin-header-actions">
        <a href="../php/logout.php" class="logout-button">تسجيل خروج</a>
      </div>
    </header>

    <main class="admin-main-section">

      <section class="admin-section">
        <div class="section-header">
            <h2 class="section-title">الطلبات والحملات</h2>
            <a href="add-campaign.php" class="add-campaign-button inside">إضافة حملة</a>
        </div>
        
        <!-- Admin Search Bar -->
        <div class="admin-search-wrapper" style="margin-bottom: 25px;">
          <form action="admin.php" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <div style="position: relative; flex: 1; max-width: 300px;">
              <input type="text" name="admin_search" id="adminSearchInput" placeholder="ابحث في الحملات أو التصنيفات..." value="<?php echo htmlspecialchars($search_query); ?>" 
                     class="search-input" style="max-width: 100%; width: 100%;">
            </div>
            <button type="submit" class="action-btn" style="height: 42px; padding: 0 25px; background-color: var(--secondary); color: white; border-radius: 20px; border: none; cursor: pointer; font-weight: bold; font-size: 14px; font-family: inherit;">بحث</button>
            <?php if($search_query): ?>
              <a href="admin.php" class="action-btn" style="height: 42px; padding: 0 20px; background-color: #999; color: white; border-radius: 20px; text-decoration: none; display: flex; align-items: center; font-weight: bold; font-size: 14px;">إلغاء البحث</a>
            <?php endif; ?>
          </form>
        </div>

      <div class="orders-grid">

        <div class="status-card active-status">
          <h3 class="status-title">الحملات النشطة</h3>
          <?php while($row = $active_res->fetch_assoc()): ?>
            <div class="admin-campaign-item">
              <div class="admin-campaign-image"><img src="<?php echo $row['image_path']; ?>" alt=""></div>
              <div class="admin-campaign-details">
                <span class="admin-campaign-category"><?php echo $row['category_name']; ?></span>
                <h4 class="admin-campaign-name"><?php echo $row['title']; ?></h4>
                <p class="admin-campaign-amount">المبلغ: <span><?php echo number_format($row['goal_amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon"></span></p>
                <div class="admin-request-actions">
                  <a href="../php/admin_actions.php?action=deactivate&id=<?php echo $row['id']; ?>" class="stop-btn">إيقاف</a>
                  <a href="edit-campaign.php?id=<?php echo $row['id']; ?>&type=campaign" class="edit-btn">تعديل</a>
                  <a href="../php/admin_actions.php?action=delete&id=<?php echo $row['id']; ?>" class="admin-delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذه الحملة نهائياً؟');">حذف</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <div class="status-card pending-status">
          <h3 class="status-title">بانتظار الموافقة</h3>
          <?php while($row = $pending_res->fetch_assoc()): ?>
            <div class="admin-campaign-item">
              <div class="admin-campaign-image"><img src="<?php echo $row['image_path']; ?>" alt=""></div>
              <div class="admin-campaign-details">
                <span class="admin-campaign-category"><?php echo $row['category_name']; ?></span>
                <h4 class="admin-campaign-name"><?php echo $row['title']; ?></h4>
                <p class="admin-campaign-amount">المبلغ: <span><?php echo number_format($row['goal_amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon"></span></p>
                <div class="admin-request-actions">
                  <a href="../php/admin_actions.php?action=approve&id=<?php echo $row['id']; ?>" class="approve-btn">قبول</a>
                  <a href="edit-campaign.php?id=<?php echo $row['id']; ?>&type=request" class="edit-btn">تعديل</a>
                  <a href="../php/admin_actions.php?action=reject&id=<?php echo $row['id']; ?>" class="reject-btn">رفض</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <div class="status-card inactive-status">
          <h3 class="status-title">الحملات غير النشطة</h3>
          <?php while($row = $inactive_res->fetch_assoc()): ?>
            <div class="admin-campaign-item">
              <div class="admin-campaign-image"><img src="<?php echo $row['image_path']; ?>" alt=""></div>
              <div class="admin-campaign-details">
                <span class="admin-campaign-category"><?php echo $row['category_name']; ?></span>
                <h4 class="admin-campaign-name"><?php echo $row['title']; ?></h4>
                <p class="admin-campaign-amount">المبلغ: <span><?php echo number_format($row['goal_amount']); ?> <img src="../images/Saudi_Riyal_Symbol.png" class="riyal-icon"></span></p>
                <div class="admin-request-actions">
                  <a href="../php/admin_actions.php?action=activate&id=<?php echo $row['id']; ?>" class="activate-btn">تفعيل</a>
                  <a href="edit-campaign.php?id=<?php echo $row['id']; ?>&type=campaign" class="edit-btn">تعديل</a>
                  <a href="../php/admin_actions.php?action=delete&id=<?php echo $row['id']; ?>" class="admin-delete-btn" onclick="return confirm('هل أنت متأكد من حذف هذه الحملة نهائياً؟');">حذف</a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

      </div>
      </section>

      <section class="admin-section">
        <h2 class="section-title">رسائل التواصل</h2>
        <div class="messages-list">
          <?php while($msg = $messages_res->fetch_assoc()): ?>
            <div class="admin-campaign-item" style="display:block; padding:15px; margin-bottom:10px; background:#f9f9f9; border-radius:8px;">
              <p><strong>من:</strong> <?php echo $msg['full_name']; ?> (<?php echo $msg['email']; ?>) - <?php echo $msg['phone']; ?></p>
              <p><strong>الرسالة:</strong> <?php echo $msg['message']; ?></p>
              <p style="font-size:0.8rem; color:gray;"><?php echo $msg['created_at']; ?></p>
            </div>
          <?php endwhile; ?>
        </div>
      </section>

      <!-- Donation Statistics Section -->
      <section class="admin-section" style="margin-top: 35px;">
        <h2 class="section-title">إحصائيات التبرعات</h2>
        <div style="display: flex; gap: 20px; align-items: stretch;">
          <div style="flex: 3; background: #ffffff; border: 1px solid var(--border-light); border-radius: 15px; padding: 25px; display: flex; align-items: flex-end; gap: 15px; height: 350px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <p style="position: absolute; top: 15px; right: 20px; color: var(--text-soft); font-size: 14px; font-weight: bold;">المبالغ حسب الأنواع</p>
            
            <?php 
            // Fetch categories for chart labels (ensure we show all, even if 0)
            $all_cats_res = $conn->query("SELECT id, name FROM categories");
            $chart_data = [];
            $max_val = 0;
            
            while($c = $all_cats_res->fetch_assoc()) {
                $c_id = $c['id'];
                // Get total for this specific category
                $c_total_res = $conn->query("SELECT SUM(d.amount) as total FROM donations d JOIN campaigns cp ON d.campaign_id = cp.id WHERE cp.category_id = $c_id");
                $c_total = $c_total_res->fetch_assoc()['total'] ?? 0;
                $chart_data[] = ['name' => $c['name'], 'total' => $c_total];
                if ($c_total > $max_val) $max_val = $c_total;
            }
            
            $chart_ceiling = $max_val > 0 ? $max_val * 1.2 : 10000;
            
            foreach($chart_data as $item): 
                $h_perc = ($item['total'] / $chart_ceiling) * 100;
            ?>
              <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 10px; height: 100%; justify-content: flex-end;">
                <div style="width: 80%; background: var(--secondary); height: <?php echo $h_perc; ?>%; border-radius: 8px 8px 0 0; position: relative; transition: height 0.6s ease; min-height: 2px;">
                  <span style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); color: var(--primary); font-size: 12px; font-weight: bold; white-space: nowrap;"><?php echo number_format($item['total']); ?></span>
                </div>
                <span style="color: var(--primary); font-size: 11px; text-align: center; white-space: nowrap; font-weight: 600; width: 100%; overflow: hidden; text-overflow: ellipsis;" title="<?php echo $item['name']; ?>"><?php echo $item['name']; ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div style="flex: 1; background: #ffffff; border: 1px solid var(--border-light); border-radius: 15px; padding: 25px; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <p style="color: var(--text-soft); font-size: 16px; margin-bottom: 15px; font-weight: bold;">إجمالي المبالغ</p>
            <div style="display: flex; align-items: center; gap: 10px;">
              <img src="../images/Saudi_Riyal_Symbol.png" style="width: 25px;">
              <span style="font-size: 32px; font-weight: bold; color: var(--primary);"><?php echo number_format($total_all); ?></span>
            </div>
          </div>
        </div>
      </section>

    </main>

    <footer class="main-footer">
      <div class="footer-container">
        <p class="copyright">جميع الحقوق محفوظة - صدقة 2026 &copy</p>
      </div>
    </footer>

  </div>

<script src="main.js"></script>
</body>
</html>
<?php $conn->close(); ?>



