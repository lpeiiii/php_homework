<?php

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_email  = $_POST['to_email'] ?? '';
    $subject   = $_POST['subject'] ?? '';
    $body      = $_POST['body'] ?? '';
    
    //寄件者設定與密碼
    $smtp_user = 'lppei03056319@gmail.com';
    $smtp_pass = 'koeu ktlt izmj keyb';

    if (empty($to_email)) {
        echo json_encode(['ok' => false, 'msg' => '未接收到收件人信箱']);
        exit;
    }

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

        $mail->setFrom($smtp_user, 'Mailer');
        $mail->addAddress($to_email); 

        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = nl2br(htmlspecialchars($body)); 

        $mail->send();
        echo json_encode(['ok' => true, 'msg' => '成功']);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'msg' => $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['ok' => false, 'msg' => '錯誤的請求方式']);
}
?>