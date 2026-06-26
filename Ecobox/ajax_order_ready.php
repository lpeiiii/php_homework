<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$notif_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($notif_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => '識別編號無效']);
    exit;
}

$link = @mysqli_connect('localhost', 'root', '', 'food_waste');
if (!$link) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連接失敗']);
    exit;
}
mysqli_set_charset($link, "utf8mb4");

// 1. 用 seller_notifications 的 id 查出 order_id
$res_notif = mysqli_query($link, "SELECT id, order_id, is_read FROM seller_notifications WHERE id = $notif_id LIMIT 1");
$row_notif = mysqli_fetch_assoc($res_notif);

if (!$row_notif) {
    echo json_encode(['status' => 'error', 'message' => '找不到該通知']);
    mysqli_close($link);
    exit;
}

if ($row_notif['is_read'] == 1) {
    echo json_encode(['status' => 'success', 'message' => '此訂單先前已通知完成！']);
    mysqli_close($link);
    exit;
}

$order_no = (int)$row_notif['order_id'];

if ($order_no <= 0) {
    echo json_encode(['status' => 'error', 'message' => '此通知沒有關聯訂單']);
    mysqli_close($link);
    exit;
}

// 2. 用 order_id 查訂單與消費者 email
$res_order = mysqli_query($link, "
    SELECT o.No, o.store_name, o.user_name, c.email 
    FROM orders_history o
    LEFT JOIN consumer c ON o.user_name = c.email
    WHERE o.No = $order_no LIMIT 1
");
$row_order = mysqli_fetch_assoc($res_order);

if (!$row_order) {
    echo json_encode(['status' => 'error', 'message' => '找不到對應訂單']);
    mysqli_close($link);
    exit;
}

$user_name  = $row_order['user_name'];
$store_name = $row_order['store_name'];
$user_email = $row_order['email'];

// 3. 更新訂單狀態
mysqli_query($link, "UPDATE orders_history SET status = 2 WHERE No = $order_no");

// 4. 寫入消費者通知
$notify_content = mysqli_real_escape_string($link, "【取餐通知】您在「{$store_name}」訂購的餐點已經準備好囉！請盡速前往門市取餐。");
$user_escaped   = mysqli_real_escape_string($link, $user_name);
mysqli_query($link, "INSERT INTO notifications (user_name, type, content, created_at) 
                     VALUES ('$user_escaped', 'order_ready', '$notify_content', NOW())");

// 5. 標記通知已讀
mysqli_query($link, "UPDATE seller_notifications SET is_read = 1 WHERE id = $notif_id");

// 6. 寄 Email
if (!empty($user_email)) {
    require_once 'notify_helper.php';
    $mail_subject = "【EcoBox 訂單通知】您的餐點已準備完成！";
    $mail_body    = "<h3>親愛的顧客您好：</h3><p>您在「{$store_name}」的餐點已準備完成！請盡速前往取餐。</p><p>感謝您使用 EcoBox 剩食平台！</p>";
    send_email_via_phpmailer($user_email, $mail_subject, $mail_body);
}

echo json_encode(['status' => 'success', 'message' => '訂單已完成，通知已發送！']);
mysqli_close($link);
?>