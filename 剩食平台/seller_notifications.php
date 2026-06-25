<?php
session_start();
include("db.php");
// 假設商家登入時會存入 store_id 到 session
$current_store_id = $_SESSION['store_name']; 

$sql = "SELECT * FROM seller_notifications WHERE store_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_store_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head><title>通知中心</title></head>
<body>
<?php include("seller_header.php"); ?>
<div style="max-width: 800px; margin: 100px auto;">
    <h2>🔔 通知中心</h2>
    <?php while($row = $notifications->fetch_assoc()): ?>
        <div style="padding: 15px; border-bottom: 1px solid #ccc;">
            <strong>[<?php echo $row['type']; ?>]</strong> 
            <?php echo $row['content']; ?>
            <div style="font-size: 0.8em; color: #666;"><?php echo $row['created_at']; ?></div>
        </div>
    <?php endwhile; ?>
</div>
</body>
</html>