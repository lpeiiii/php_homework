<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 請確認這些路徑指向你專案中的 PHPMailer 資料夾
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

function add_notification($conn, $store_id, $type, $content, $to_email) {
    // --- 1. 存入資料庫 ---
    $stmt = $conn->prepare("INSERT INTO seller_notifications (store_id, type, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $store_id, $type, $content);
    $stmt->execute();
    
    // --- 2. 寄送 Email 設定 ---
    $smtp_user = 'lppei03056319@gmail.com';
    $smtp_pass = 'koeu ktlt izmj keyb'; // 這是你的 Gmail App 密碼

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp_user, 'EcoBox 平台通知');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = '【EcoBox 系統通知】您有一則新訊息';
        $mail->Body    = "<h3>親愛的商家您好：</h3><p>" . nl2br(htmlspecialchars($content)) . "</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // 如果發信失敗，可以記錄錯誤日誌
        error_log("郵件發送失敗: " . $mail->ErrorInfo);
        return false;
    }
}
?>