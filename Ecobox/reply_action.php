<?php
session_start();
include("db.php");
require_once 'mail_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg_id = (int)$_POST['msg_id'];
    $reply  = trim(str_replace(["\r\n", "\r", "\n"], " ", $_POST['reply'])); // ← 加這行
    $reply  = mysqli_real_escape_string($conn, $reply);

    $res_select = mysqli_query($conn, "SELECT sender_id, sender_type, message_content FROM admin_messages WHERE message_id = $msg_id");
    $msg_row    = mysqli_fetch_assoc($res_select);

    if (!$msg_row) { die("找不到這筆問題資料。"); }

    $sender_id    = $msg_row['sender_id'];
    $sender_type  = $msg_row['sender_type'];
    $original_msg = $msg_row['message_content'];

    $sql_update = "UPDATE admin_messages 
                   SET admin_reply = '$reply', status = 'replied' 
                   WHERE message_id = $msg_id";

    if (mysqli_query($conn, $sql_update)) {

        if ($sender_type === 'store') {
            $sender_id_escaped = mysqli_real_escape_string($conn, $sender_id);
            $res_store = mysqli_query($conn, "SELECT No, email FROM store WHERE store_name = '$sender_id_escaped'");
            $store_row = mysqli_fetch_assoc($res_store);

            if ($store_row) {
                $actual_store_id  = (int)$store_row['No'];
                $reply_escaped    = mysqli_real_escape_string($conn, $reply);
                $original_escaped = mysqli_real_escape_string($conn, $original_msg);
                $notify_content   = mysqli_real_escape_string($conn, "管理員回覆了您的問題：「{$original_msg}」→ {$reply}");

                // 寫入商家評論通知（type = review）
                mysqli_query($conn, "INSERT INTO seller_notifications (store_id, type, content, is_read, created_at)
                                     VALUES ($actual_store_id, 'review', '$notify_content', 0, NOW())");

                // 寄信
                send_email_via_phpmailer($store_row['email'], '【EcoBox】您有一則管理員回覆',
                    "<h3>親愛的商家您好：</h3><p>您的問題：{$original_msg}</p><p>管理員回覆：{$reply}</p>");
            }

        } else {
            notify_consumer($conn, $sender_id, 'admin_reply', '客服回覆通知', $reply, $sender_id, $original_msg);
        }

        echo "<script>alert('回覆成功！'); window.location.href='admin_questions.php';</script>";
    } else {
        echo "錯誤: " . mysqli_error($conn);
    }
}
?>