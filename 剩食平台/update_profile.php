<?php
session_start();
if (!isset($_SESSION['login']) || !isset($_SESSION['uName'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uName = $_SESSION['uName'];
    $newName = trim($_POST['update_name']);
    $newEmail = trim($_POST['update_email']);

    $link = @mysqli_connect('localhost', 'root', '', 'food_waste');
    if (!$link) die("資料庫連線失敗");
    mysqli_set_charset($link, "utf8mb4");

    $uName_escaped = mysqli_real_escape_string($link, $uName);
    $newName_escaped = mysqli_real_escape_string($link, $newName);
    $newEmail_escaped = mysqli_real_escape_string($link, $newEmail);

    // 更新會員資料
    $sql_update = "UPDATE users SET name = '$newName_escaped', email = '$newEmail_escaped' WHERE username = '$uName_escaped'";
    
    if (mysqli_query($link, $sql_update)) {
        // 更新成功後，如果你的姓名有存在 Session，記得同步更新
        $_SESSION['uName'] = $newName; 
        echo "<script>alert('個人資訊修改成功！'); window.location.href='notifications.php';</script>";
    } else {
        echo "<script>alert('修改失敗，請稍後再試。'); window.location.href='notifications.php';</script>";
    }
    mysqli_close($link);
}
?>