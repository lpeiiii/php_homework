<?php
session_start();

// 1. 安全檢查
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentEmail = $_SESSION['user_id']; 
    $newName = trim($_POST['update_name']);
    $newEmail = trim($_POST['update_email']);

    $link = @mysqli_connect('localhost', 'root', '', 'food_waste');
    if (!$link) die("資料庫連線失敗");
    mysqli_set_charset($link, "utf8mb4");

    $currentEmail_escaped = mysqli_real_escape_string($link, $currentEmail);
    $newName_escaped = mysqli_real_escape_string($link, $newName);
    $newEmail_escaped = mysqli_real_escape_string($link, $newEmail);

    // 2. 更新資料庫
    $sql_update = "UPDATE consumer SET user_name = '$newName_escaped', email = '$newEmail_escaped' WHERE id = '$currentEmail_escaped'";
    
    if (mysqli_query($link, $sql_update)) {
        // 3. 同步 Session 資訊
        if (!empty($newEmail)) {
            $_SESSION['user_id'] = $newEmail;
        }
        $_SESSION['uName'] = $newName; 
        
        // 4. 使用 HTTP_REFERER 導回上一頁
        $back_url = $_SERVER['HTTP_REFERER'];
        echo "<script>alert('個人資訊修改成功！'); window.location.href='$back_url';</script>";
    } else {
        echo "<script>alert('修改失敗，請稍後再試。'); window.location.href='index.php';</script>";
    }
    mysqli_close($link);
}
?>