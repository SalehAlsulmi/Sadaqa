<?php
include 'db_connect.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$categories = isset($_GET['categories']) ? $_GET['categories'] : [];

$query = "SELECT c.*, cat.name as category_name FROM campaigns c JOIN categories cat ON c.category_id = cat.id WHERE c.status = 'active'";

if ($search) {
    $query .= " AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%' OR cat.name LIKE '%$search%')";
}

if (!empty($categories)) {
    $cat_ids = array_map('intval', $categories);
    $query .= " AND c.category_id IN (" . implode(',', $cat_ids) . ")";
}

$campaigns_res = $conn->query($query);

if ($campaigns_res->num_rows > 0) {
    while($campaign = $campaigns_res->fetch_assoc()) {
        ?>
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
        <?php
    }
} else {
    echo "<p>لا توجد حملات تطابق بحثك.</p>";
}
$conn->close();
?>


